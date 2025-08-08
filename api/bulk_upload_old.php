<?php
// api/bulk_upload.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/templates_config.php';

// Maximum allowed rows
define('MAX_ROWS', 5000);

// Expected headers for each upload type
define('CATALOG_HEADERS', [
    'templateCode', 'catalogNumber', 'catalogName', 'cas', 
    'detail', 'formulation', 'observedMolMass', 
    'predictedMolMass', 'predictedNTerminal', 'reconstitution', 
    'shipping', 'source', 'stability'
]);

define('LOT_HEADERS', [
    'templateCode', 'lotNumber', 'catalogNumber', 'activity', 
    'concentration', 'purity', 'formulation'
]);

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }
    
    // Validate upload type
    $uploadType = isset($_POST['uploadType']) ? $_POST['uploadType'] : '';
    if (!in_array($uploadType, ['catalog', 'lot'])) {
        throw new Exception('Invalid upload type. Must be "catalog" or "lot"');
    }
    
    // Validate file upload
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error occurred');
    }
    
    $uploadedFile = $_FILES['file'];
    $fileName = $uploadedFile['name'];
    
    // Validate file type
    if (!preg_match('/\.csv$/i', $fileName)) {
        throw new Exception('Invalid file type. Only CSV files are allowed');
    }
    
    // Open CSV file
    $handle = fopen($uploadedFile['tmp_name'], 'r');
    if ($handle === false) {
        throw new Exception('Failed to open uploaded file');
    }
    
    // Read all rows into array first to count them
    $rows = [];
    while (($data = fgetcsv($handle)) !== false) {
        $rows[] = $data;
    }
    fclose($handle);
    
    if (count($rows) < 2) {
        throw new Exception('CSV file is empty or contains only headers');
    }
    
    // Check row limit
    if (count($rows) - 1 > MAX_ROWS) {
        throw new Exception('File exceeds maximum allowed rows (' . MAX_ROWS . ')');
    }
    
    // Get headers (first row)
    $headers = array_map('trim', $rows[0]);
    
    // Remove BOM from first header if present
    if (!empty($headers[0])) {
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
    }
    
    // Validate headers
    $expectedHeaders = $uploadType === 'catalog' ? CATALOG_HEADERS : LOT_HEADERS;
    if ($headers !== $expectedHeaders) {
        throw new Exception('Invalid CSV headers. Please use the provided template');
    }
    
    // Process data
    $conn = getDBConnection();
    $conn->autocommit(false);
    
    $results = [
        'totalRows' => count($rows) - 1,
        'successCount' => 0,
        'skippedCount' => 0,
        'skippedRecords' => []
    ];
    
    try {
        if ($uploadType === 'catalog') {
            $results = processCatalogUpload($conn, $rows, $headers);
        } else {
            $results = processLotUpload($conn, $rows, $headers);
        }
        
        // Commit transaction
        $conn->commit();
        $conn->autocommit(true);
        
        // Generate skipped report if needed
        $skippedReportPath = null;
        if ($results['skippedCount'] > 0) {
            $skippedReportPath = generateSkippedReport($results['skippedRecords'], $uploadType);
        }
        
        // Log upload - ensure autocommit is on
        $summary = [
            'uploadType' => $uploadType,
            'fileName' => $fileName,
            'totalRows' => $results['totalRows'],
            'successCount' => $results['successCount'],
            'skippedCount' => $results['skippedCount'],
            'errorCount' => 0,
            'skippedReportPath' => $skippedReportPath
        ];
        
        $status = $results['skippedCount'] > 0 ? 'partial' : 'success';
        logUpload($conn, $status, $summary, null);
        
        // Return success response
        $response = [
            'success' => true,
            'status' => $status,
            'summary' => $summary
        ];
        
        // Add debug info if available
        global $debugMessages;
        if (isset($debugMessages) && !empty($debugMessages)) {
            $response['debug'] = $debugMessages;
        }
        
        echo json_encode($response);
        
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    // Log failed upload
    if (isset($conn) && isset($uploadType) && isset($fileName)) {
        $summary = [
            'uploadType' => $uploadType,
            'fileName' => $fileName,
            'totalRows' => isset($results) ? $results['totalRows'] : 0,
            'successCount' => 0,
            'skippedCount' => 0,
            'errorCount' => isset($results) ? $results['totalRows'] : 0
        ];
        logUpload($conn, 'failed', $summary, $e->getMessage());
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'status' => 'failed',
        'errorMessage' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

/**
 * Process catalog upload
 */
function processCatalogUpload($conn, $rows, $headers) {
    $results = [
        'totalRows' => count($rows) - 1,
        'successCount' => 0,
        'skippedCount' => 0,
        'skippedRecords' => []
    ];
    
    // Get template fields mapping
    $fieldMapping = getFieldMappingForTemplates();
    
    // Define all possible catalog fields (excluding the basic 3)
    $allCatalogFields = ['activity', 'cas', 'detail', 'formulation', 'observedMolMass', 
                         'predictedMolMass', 'predictedNTerminal', 'reconstitution', 
                         'shipping', 'source', 'stability'];
    
    // Process each row
    for ($i = 1; $i < count($rows); $i++) {
        $row = $rows[$i];
        
        // Skip empty rows
        if (empty(array_filter($row))) {
            continue;
        }
        
        // Map row to associative array
        if (count($row) !== count($headers)) {
            throw new Exception("Row $i: Column count mismatch. Expected " . count($headers) . " columns, got " . count($row));
        }
        
        $data = array_combine($headers, $row);
        $data = array_map('trim', $data);
        
        // STEP 1: Validate basic required fields
        if (empty($data['templateCode']) || empty($data['catalogNumber']) || empty($data['catalogName'])) {
            throw new Exception("Row $i: Missing required field (templateCode, catalogNumber, or catalogName)");
        }
        
        // STEP 2: Validate template code exists
        if (!isset(TEMPLATES[$data['templateCode']])) {
            throw new Exception("Row $i: Invalid template code '{$data['templateCode']}'");
        }
        
        $templateCode = $data['templateCode'];
        
        // STEP 3: Check if catalog already exists (SKIP if exists)
        $checkSql = "SELECT id FROM catalogs WHERE catalogNumber = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $data['catalogNumber']);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            // Catalog exists, skip it
            $results['skippedCount']++;
            $results['skippedRecords'][] = [
                'row' => $i,
                'catalogNumber' => $data['catalogNumber'],
                'lotNumber' => '',
                'reason' => 'Catalog already exists'
            ];
            $checkStmt->close();
            continue;
        }
        $checkStmt->close();
        
        // STEP 4: Validate EXACTLY the required fields for this template
        $requiredFields = $fieldMapping[$templateCode]['catalog'] ?? [];
        $requiredDbFields = array_values($requiredFields);
        
        // Check for missing required fields
        foreach ($requiredFields as $fieldName => $dbField) {
            if (!isset($data[$dbField]) || trim($data[$dbField]) === '') {
                throw new Exception("Row $i: Missing required field '$fieldName' for template '$templateCode'");
            }
        }
        
        // Check for extra fields (fields that are filled but not required for this template)
        foreach ($allCatalogFields as $field) {
            if (!empty($data[$field]) && !in_array($field, $requiredDbFields)) {
                // Find the friendly name for this field
                $friendlyName = array_search($field, array_merge(...array_values($fieldMapping[$templateCode]))) ?: $field;
                throw new Exception("Row $i: Extra field '$field' not allowed for template '$templateCode'. Only allowed fields are: " . implode(', ', array_keys($requiredFields)));
            }
        }
        
        // STEP 5: Insert catalog
        $insertSql = "INSERT INTO catalogs (catalogNumber, catalogName, templateCode";
        $values = [$data['catalogNumber'], $data['catalogName'], $templateCode];
        $types = "sss";
        
        // Add template-specific fields
        foreach ($requiredFields as $fieldName => $dbField) {
            if (!empty($data[$dbField])) {
                $insertSql .= ", $dbField";
                $values[] = $data[$dbField];
                $types .= "s";
            }
        }
        
        $insertSql .= ") VALUES (" . str_repeat("?,", count($values) - 1) . "?)";
        
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param($types, ...$values);
        
        if ($insertStmt->execute()) {
            $results['successCount']++;
        } else {
            throw new Exception("Row $i: Failed to insert catalog - " . $conn->error);
        }
        $insertStmt->close();
    }
    
    return $results;
}

/**
 * Process lot upload
 */
function processLotUpload($conn, $rows, $headers) {
    $results = [
        'totalRows' => count($rows) - 1,
        'successCount' => 0,
        'skippedCount' => 0,
        'skippedRecords' => []
    ];
    
    // Get template fields mapping
    $fieldMapping = getFieldMappingForTemplates();
    
    // Define all possible lot fields (excluding the basic 3)
    $allLotFields = ['activity', 'concentration', 'purity', 'formulation'];
    
    // Process each row
    for ($i = 1; $i < count($rows); $i++) {
        $row = $rows[$i];
        
        // Skip empty rows
        if (empty(array_filter($row))) {
            continue;
        }
        
        // Map row to associative array
        if (count($row) !== count($headers)) {
            throw new Exception("Row $i: Column count mismatch. Expected " . count($headers) . " columns, got " . count($row));
        }
        
        $data = array_combine($headers, $row);
        $data = array_map('trim', $data);
        
        // STEP 1: Validate basic required fields
        if (empty($data['templateCode']) || empty($data['catalogNumber']) || empty($data['lotNumber'])) {
            throw new Exception("Row $i: Missing required field (templateCode, catalogNumber, or lotNumber)");
        }
        
        // STEP 2: Validate template code exists
        if (!isset(TEMPLATES[$data['templateCode']])) {
            throw new Exception("Row $i: Invalid template code '{$data['templateCode']}'");
        }
        
        $templateCode = $data['templateCode'];
        
        // STEP 3: Check if catalog exists (SKIP if not exists)
        $catalogSql = "SELECT id, templateCode FROM catalogs WHERE catalogNumber = ?";
        $catalogStmt = $conn->prepare($catalogSql);
        $catalogStmt->bind_param("s", $data['catalogNumber']);
        $catalogStmt->execute();
        $catalogResult = $catalogStmt->get_result();
        
        if ($catalogResult->num_rows === 0) {
            // Catalog doesn't exist, skip
            $results['skippedCount']++;
            $results['skippedRecords'][] = [
                'row' => $i,
                'catalogNumber' => $data['catalogNumber'],
                'lotNumber' => $data['lotNumber'],
                'reason' => 'Catalog does not exist'
            ];
            $catalogStmt->close();
            continue;
        }
        
        $catalog = $catalogResult->fetch_assoc();
        $catalogStmt->close();
        
        // STEP 4: Check template match (STOP if mismatch)
        if ($catalog['templateCode'] !== $templateCode) {
            throw new Exception("Row $i: Template code mismatch. Catalog has template '{$catalog['templateCode']}' but lot specifies '{$templateCode}'");
        }
        
        // STEP 5: Check if lot already exists (SKIP if exists)
        $checkSql = "SELECT id FROM lots WHERE catalogNumber = ? AND lotNumber = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ss", $data['catalogNumber'], $data['lotNumber']);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            // Lot exists, skip it
            $results['skippedCount']++;
            $results['skippedRecords'][] = [
                'row' => $i,
                'catalogNumber' => $data['catalogNumber'],
                'lotNumber' => $data['lotNumber'],
                'reason' => 'Lot already exists'
            ];
            $checkStmt->close();
            continue;
        }
        $checkStmt->close();
        
        // STEP 6: Validate EXACTLY the required fields for this template
        $requiredFields = $fieldMapping[$templateCode]['lot'] ?? [];
        $requiredDbFields = array_values($requiredFields);
        
        // Check for missing required fields
        foreach ($requiredFields as $fieldName => $dbField) {
            if (!isset($data[$dbField]) || trim($data[$dbField]) === '') {
                throw new Exception("Row $i: Missing required field '$fieldName' for template '$templateCode'");
            }
        }
        
        // Check for extra fields (fields that are filled but not required for this template)
        foreach ($allLotFields as $field) {
            if (!empty($data[$field]) && !in_array($field, $requiredDbFields)) {
                if (empty($requiredFields)) {
                    // Special message for templates with no lot fields like RGT
                    throw new Exception("Row $i: Template '$templateCode' does not allow any lot-specific fields. Only templateCode, catalogNumber, and lotNumber should be provided");
                } else {
                    throw new Exception("Row $i: Extra field '$field' not allowed for template '$templateCode'. Only allowed fields are: " . implode(', ', array_keys($requiredFields)));
                }
            }
        }
        
        // STEP 7: Insert lot
        $insertSql = "INSERT INTO lots (catalogNumber, lotNumber, templateCode";
        $values = [$data['catalogNumber'], $data['lotNumber'], $templateCode];
        $types = "sss";
        
        // Add template-specific fields
        foreach ($requiredFields as $fieldName => $dbField) {
            if (!empty($data[$dbField])) {
                $insertSql .= ", $dbField";
                $values[] = $data[$dbField];
                $types .= "s";
            }
        }
        
        $insertSql .= ") VALUES (" . str_repeat("?,", count($values) - 1) . "?)";
        
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param($types, ...$values);
        
        if ($insertStmt->execute()) {
            $results['successCount']++;
        } else {
            throw new Exception("Row $i: Failed to insert lot - " . $conn->error);
        }
        $insertStmt->close();
    }
    
    return $results;
}

/**
 * Get field mapping for templates
 */
function getFieldMappingForTemplates() {
    $mapping = [];
    
    foreach (TEMPLATE_FIELDS as $templateCode => $sections) {
        $mapping[$templateCode] = [
            'catalog' => [],
            'lot' => []
        ];
        
        foreach ($sections as $sectionId => $fields) {
            foreach ($fields as $field) {
                if ($field['field_source'] === 'catalog') {
                    $mapping[$templateCode]['catalog'][$field['field_name']] = $field['db_field'];
                } else {
                    $mapping[$templateCode]['lot'][$field['field_name']] = $field['db_field'];
                }
            }
        }
    }
    
    return $mapping;
}

/**
 * Generate skipped records report
 */
function generateSkippedReport($skippedRecords, $uploadType) {
    // Create reports directory if it doesn't exist
    $reportsDir = '../reports';
    if (!file_exists($reportsDir)) {
        mkdir($reportsDir, 0755, true);
    }
    
    // Generate filename
    $timestamp = date('Ymd_His');
    $filename = "skipped_{$uploadType}_{$timestamp}.csv";
    $filepath = $reportsDir . '/' . $filename;
    
    // Create CSV content
    $fp = fopen($filepath, 'w');
    
    // Write headers
    fputcsv($fp, ['row_number', 'catalog_number', 'lot_number', 'reason']);
    
    // Write data
    foreach ($skippedRecords as $record) {
        fputcsv($fp, [
            $record['row'],
            $record['catalogNumber'],
            $record['lotNumber'],
            $record['reason']
        ]);
    }
    
    fclose($fp);
    
    return $filename;
}

/**
 * Log upload to database
 */
function logUpload($conn, $status, $summary, $errorMessage) {
    try {
        // Ensure autocommit is on for logging
        $conn->autocommit(true);
        
        $sql = "INSERT INTO uploadLogs (status, summary, errorMessage, uploadedAt) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }
        
        $summaryJson = json_encode($summary);
        
        // For debugging, let's also try with NULL for errorMessage if it's empty
        if ($errorMessage === null || $errorMessage === '') {
            $errorMessage = null;
        }
        
        $stmt->bind_param("sss", $status, $summaryJson, $errorMessage);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute: " . $stmt->error);
        }
        
        $insertId = $conn->insert_id;
        $stmt->close();
        
        // Verify the insert worked
        $verify = $conn->query("SELECT * FROM uploadLogs WHERE id = $insertId");
        if (!$verify || $verify->num_rows === 0) {
            throw new Exception("Insert verification failed - record not found");
        }
        
        // Log successful (this will help us debug)
        error_log("Upload logged successfully with ID: $insertId, Status: $status");
        
    } catch (Exception $e) {
        // Log the error and also add it to the response for debugging
        $errorMsg = "Log Upload Error: " . $e->getMessage();
        error_log($errorMsg);
        
        // Don't throw the exception - we don't want logging failure to break the upload response
        // But add a debug flag to the response
        global $debugMessages;
        if (!isset($debugMessages)) $debugMessages = [];
        $debugMessages[] = $errorMsg;
    }
}
?>