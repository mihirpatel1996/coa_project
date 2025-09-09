<?php
// api/bulk_upload.php
require_once '../config/database.php';
require_once '../config/templates_config.php';
require_once '../vendor/autoload.php';

// PHPSpreadsheet imports
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
    'lotNumber', 'catalogNumber', 'activity', 
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
    if (!preg_match('/\.(xlsx|xls)$/i', $fileName)) {
        throw new Exception('Invalid file type. Only Excel files are allowed');
    }
    
    // Read Excel file
    try {
        $spreadsheet = IOFactory::load($uploadedFile['tmp_name']);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray(null, true, true, false);
    } catch (Exception $e) {
        throw new Exception('Failed to read Excel file: ' . $e->getMessage());
    }
    
    if (count($rows) < 2) {
        throw new Exception('Excel file is empty or contains only headers');
    }
    
    // Check row limit
    if (count($rows) - 1 > MAX_ROWS) {
        throw new Exception('File exceeds maximum allowed rows (' . MAX_ROWS . ')');
    }
    
    // Get headers (first row)
    $headers = array_map(function($value) {
        return $value === null ? '' : trim($value);
    }, $rows[0]);
    
    // Remove BOM from first header if present
    if (!empty($headers[0])) {
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
    }
    
    // Validate headers
    $expectedHeaders = '';
    if ($uploadType === 'catalog') {
        $expectedHeaders = CATALOG_HEADERS;
    } 
    if ($uploadType === 'lot') {
        $expectedHeaders = LOT_HEADERS;             
    }

    if ($headers !== $expectedHeaders) {
        throw new Exception('Invalid Excel headers. Please use the provided template');
    }
    
    // Process data
    $conn = getDBConnection();
    $conn->autocommit(false);
    
    $results = [
        'totalRows' => count($rows) - 1,
        'successCount' => 0,
        'updateCount' => 0,
        'skippedCount' => 0,
        'errorCount' => 0,
        'skippedRecords' => [],
        'updatedRecords' => [],
        'allRecords' => []
    ];
    
    try {
        if ($uploadType === 'catalog') {
            $results = processCatalogUpload($conn, $rows, $headers);
        } else {
            $results = processLotUpload($conn, $rows, $headers);
        }
        
        // Check if there are errors
        if ($results['errorCount'] > 0) {
            // Rollback transaction - don't save any data if there are errors
            $conn->rollback();
            $conn->autocommit(true);
            
            // Generate complete report showing all errors
            $completeReportPath = null;
            if (!empty($results['allRecords'])) {
                $completeReportPath = generateCompleteReport($results['allRecords'], $uploadType);
            }
            
            // Log as error status
            $summary = [
                'uploadType' => $uploadType,
                'fileName' => $fileName,
                'totalRows' => $results['totalRows'],
                'successCount' => 0,  // Set to 0 since transaction was rolled back
                'updateCount' => 0,   // Set to 0 since transaction was rolled back
                'skippedCount' => 0,  // Set to 0 for consistency
                'errorCount' => $results['errorCount'],
                'completeReportPath' => $completeReportPath
            ];
            
            logUpload($conn, 'error', $summary, 'Upload failed due to validation errors');
            
            http_response_code(400);
            // Return error response
            echo json_encode([
                'success' => false,
                'status' => 'error',
                'summary' => $summary,
                'errorMessage' => "Upload failed: Validation errors found. Download the Upload Report to see details and fix all errors before re-uploading."
            ]);
            exit;
        }
        
        // No errors, commit transaction
        $conn->commit();
        $conn->autocommit(true);
        
        // Generate updated report if needed
        $updatedReportPath = null;
        if ($results['updateCount'] > 0) {
            $updatedReportPath = generateUpdatedReport($results['updatedRecords'], $uploadType);
        }
        
        // Generate complete upload report (always generated)
        $completeReportPath = null;
        if (!empty($results['allRecords'])) {
            $completeReportPath = generateCompleteReport($results['allRecords'], $uploadType);
        }
        
        // Log upload
        $summary = [
            'uploadType' => $uploadType,
            'fileName' => $fileName,
            'totalRows' => $results['totalRows'],
            'successCount' => $results['successCount'],
            'updateCount' => $results['updateCount'],
            'skippedCount' => $results['skippedCount'],
            'errorCount' => 0,
            'updatedReportPath' => $updatedReportPath,
            'completeReportPath' => $completeReportPath
        ];
        
        $status = $results['skippedCount'] > 0 ? 'partial' : 'success';
        logUpload($conn, $status, $summary, null);
        
        http_response_code(200);
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
            'updateCount' => 0,
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
        'updateCount' => 0,
        'skippedCount' => 0,
        'errorCount' => 0,
        'skippedRecords' => [],
        'updatedRecords' => [],
        'allRecords' => []
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
            $results['errorCount']++;
            $recordData = ['row' => $i];
            $recordData['status'] = 'Error';
            $recordData['error_message'] = "Column count mismatch. Expected " . count($headers) . " columns, got " . count($row);
            $results['allRecords'][] = $recordData;
            continue;
        }

        $data = array_combine($headers, $row);
        // Handle null values before trimming (PHP 8.1+ compatibility)
        $data = array_map(function($value) {
            return $value === null ? '' : trim($value);
        }, $data);
        
        // Prepare record for complete report
        $recordData = $data;
        $recordData['row'] = $i;
        
        // STEP 1: Validate basic required fields
        if (empty($data['templateCode']) || empty($data['catalogNumber']) || empty($data['catalogName'])) {
            $results['errorCount']++;
            $recordData['status'] = 'Error';
            $recordData['error_message'] = 'Missing required field (templateCode, catalogNumber, or catalogName)';
            $results['allRecords'][] = $recordData;
            continue;
        }
        
        // STEP 2: Validate template code exists
        if (!isset(TEMPLATES[$data['templateCode']]) || $data['templateCode'] !== TEMPLATES[$data['templateCode']]['template_code']) {
            $results['errorCount']++;
            $recordData['status'] = 'Error';
            $recordData['error_message'] = "Invalid template code '{$data['templateCode']}'";
            $results['allRecords'][] = $recordData;
            continue;
        }
        
        $templateCode = $data['templateCode'];
        
        // STEP 3: Check if catalog already exists
        $checkSql = "SELECT id, templateCode FROM catalogs WHERE catalogNumber = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $data['catalogNumber']);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            // Catalog exists, UPDATE it
            $existingCatalog = $checkResult->fetch_assoc();
            $checkStmt->close();
            
            // Verify template code matches
            if ($existingCatalog['templateCode'] !== $templateCode) {
                $results['errorCount']++;
                $recordData['status'] = 'Error';
                $recordData['error_message'] = "Catalog '{$data['catalogNumber']}' already exists with different template code '{$existingCatalog['templateCode']}'";
                $results['allRecords'][] = $recordData;
                continue;
            }
            
            // STEP 4: Validate EXACTLY the required fields for this template
            $requiredFields = $fieldMapping[$templateCode]['catalog'] ?? [];
            $requiredDbFields = array_values($requiredFields);
            
            // Check for missing required fields
            $missingFields = [];
            foreach ($requiredFields as $fieldName => $dbField) {
                if (!isset($data[$dbField]) || trim($data[$dbField]) === '') {
                    $missingFields[] = $fieldName;
                }
            }
            
            if (!empty($missingFields)) {
                $results['errorCount']++;
                $recordData['status'] = 'Error';
                $recordData['error_message'] = "Missing required fields for template '$templateCode': " . implode(', ', $missingFields);
                $results['allRecords'][] = $recordData;
                continue;
            }
            
            // Check for extra fields
            $extraFields = [];
            foreach ($allCatalogFields as $field) {
                if (!empty($data[$field]) && !in_array($field, $requiredDbFields)) {
                    $extraFields[] = $field;
                }
            }
            
            if (!empty($extraFields)) {
                $results['errorCount']++;
                $recordData['status'] = 'Error';
                $recordData['error_message'] = "Extra fields not allowed for template '$templateCode': " . implode(', ', $extraFields);
                $results['allRecords'][] = $recordData;
                continue;
            }
            
            // UPDATE catalog
            $updateSql = "UPDATE catalogs SET catalogName = ?, updatedAt = NOW()";
            $values = [$data['catalogName']];
            $types = "s";
            
            // Add template-specific fields
            foreach ($requiredFields as $fieldName => $dbField) {
                if (!empty($data[$dbField])) {
                    $updateSql .= ", $dbField = ?";
                    $values[] = $data[$dbField];
                    $types .= "s";
                }
            }
            
            $updateSql .= " WHERE catalogNumber = ?";
            $values[] = $data['catalogNumber'];
            $types .= "s";
            
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param($types, ...$values);
            
            if ($updateStmt->execute()) {
                $results['updateCount']++;
                $results['updatedRecords'][] = [
                    'row' => $i,
                    'catalogNumber' => $data['catalogNumber'],
                    'catalogName' => $data['catalogName']
                ];
                
                $recordData['status'] = 'Would_Update'; // TEMPORARY STATUS
                $results['allRecords'][] = $recordData;
            } else {
                $results['errorCount']++;
                $recordData['status'] = 'Error';
                $recordData['error_message'] = "Failed to update catalog - Database error";
                $results['allRecords'][] = $recordData;
            }
            $updateStmt->close();
            continue;
        }
        $checkStmt->close();
        
        // STEP 4: Validate EXACTLY the required fields for this template (for new catalog)
        $requiredFields = $fieldMapping[$templateCode]['catalog'] ?? [];
        $requiredDbFields = array_values($requiredFields);
        
        // Check for missing required fields
        $missingFields = [];
        foreach ($requiredFields as $fieldName => $dbField) {
            if (!isset($data[$dbField]) || trim($data[$dbField]) === '') {
                $missingFields[] = $fieldName;
            }
        }
        
        if (!empty($missingFields)) {
            $results['errorCount']++;
            $recordData['status'] = 'Error';
            $recordData['error_message'] = "Missing required fields for template '$templateCode': " . implode(', ', $missingFields);
            $results['allRecords'][] = $recordData;
            continue;
        }
        
        // Check for extra fields
        $extraFields = [];
        foreach ($allCatalogFields as $field) {
            if (!empty($data[$field]) && !in_array($field, $requiredDbFields)) {
                $extraFields[] = $field;
            }
        }
        
        if (!empty($extraFields)) {
            $results['errorCount']++;
            $recordData['status'] = 'Error';
            $recordData['error_message'] = "Extra fields not allowed for template '$templateCode': " . implode(', ', $extraFields);
            $results['allRecords'][] = $recordData;
            continue;
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
            $recordData['status'] = 'Would_Insert'; // TEMPORARY STATUS
            $results['allRecords'][] = $recordData;
        } else {
            $results['errorCount']++;
            $recordData['status'] = 'Error';
            $recordData['error_message'] = "Failed to insert catalog - Database error";
            $results['allRecords'][] = $recordData;
        }
        $insertStmt->close();
    }
    
    // FINALIZE STATUSES BASED ON WHETHER THERE ARE ERRORS
    if ($results['errorCount'] > 0) {
        // Transaction will be rolled back, so nothing is actually saved
        // Convert Would_Insert and Would_Update to Valid
        foreach ($results['allRecords'] as &$record) {
            if ($record['status'] === 'Would_Insert' || $record['status'] === 'Would_Update') {
                $record['status'] = 'Valid'; // Passed validation but not saved
            }
        }
        // Reset counts since nothing was actually saved
        $results['successCount'] = 0;
        $results['updateCount'] = 0;
    } else {
        // Transaction will be committed, convert temporary statuses to final
        foreach ($results['allRecords'] as &$record) {
            if ($record['status'] === 'Would_Insert') {
                $record['status'] = 'Inserted';
            } elseif ($record['status'] === 'Would_Update') {
                $record['status'] = 'Updated';
            }
        }
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
        'updateCount' => 0,
        'skippedCount' => 0,
        'errorCount' => 0,
        'skippedRecords' => [],
        'updatedRecords' => [],
        'allRecords' => []
    ];
    
    // Get template fields mapping
    $fieldMapping = getFieldMappingForTemplates();
    
    // Define all possible lot fields
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
            $results['errorCount']++;
            $recordData = ['row' => $i];
            $recordData['status'] = 'Error';
            $recordData['error_message'] = "Column count mismatch. Expected " . count($headers) . " columns, got " . count($row);
            $results['allRecords'][] = $recordData;
            continue;
        }
        
        $data = array_combine($headers, $row);
        // Handle null values before trimming (PHP 8.1+ compatibility)
        $data = array_map(function($value) {
            return $value === null ? '' : trim($value);
        }, $data);
        
        // Prepare record for complete report
        $recordData = $data;
        $recordData['row'] = $i;
        
        // STEP 1: Validate basic required fields
        if (empty($data['catalogNumber']) || empty($data['lotNumber'])) {
            $results['errorCount']++;
            $recordData['status'] = 'Error';
            $recordData['error_message'] = 'Missing required field (catalogNumber or lotNumber)';
            $results['allRecords'][] = $recordData;
            continue;
        }
        
        // STEP 2: Check if catalog exists
        $catalogSql = "SELECT id, templateCode FROM catalogs WHERE catalogNumber = ?";
        $catalogStmt = $conn->prepare($catalogSql);
        $catalogStmt->bind_param("s", $data['catalogNumber']);
        $catalogStmt->execute();
        $catalogResult = $catalogStmt->get_result();
        
        if ($catalogResult->num_rows === 0) {
            $results['errorCount']++;
            $recordData['status'] = 'Error';
            $recordData['error_message'] = "Catalog doesn't exist '{$data['catalogNumber']}'";
            $results['allRecords'][] = $recordData;
            $catalogStmt->close();
            continue;
        }
        
        $catalog = $catalogResult->fetch_assoc();
        $catalogStmt->close();
        
        // STEP 3: Validate template
        if (!isset(TEMPLATES[$catalog['templateCode']]) || $catalog['templateCode'] !== TEMPLATES[$catalog['templateCode']]['template_code']) {
            $results['errorCount']++;
            $recordData['status'] = 'Error';
            $recordData['error_message'] = "Template code mismatch. Catalog has template '{$catalog['templateCode']}' but template configuration is not matching";
            $results['allRecords'][] = $recordData;
            continue;
        }

        $templateCode = TEMPLATES[$catalog['templateCode']]['template_code'];
        
        // STEP 4: Check if lot already exists
        $checkSql = "SELECT id FROM lots WHERE catalogNumber = ? AND lotNumber = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ss", $data['catalogNumber'], $data['lotNumber']);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            // Lot exists, UPDATE it
            $checkStmt->close();
            
            // Validate required fields for this template
            $requiredFields = $fieldMapping[$templateCode]['lot'] ?? [];
            $requiredDbFields = array_values($requiredFields);

            // Check for missing required fields
            $missingFields = [];
            foreach ($requiredFields as $fieldName => $dbField) {
                if (!isset($data[$dbField]) || trim($data[$dbField]) === '') {
                    $missingFields[] = $fieldName;
                }
            }
            
            if (!empty($missingFields)) {
                $results['errorCount']++;
                $recordData['status'] = 'Error';
                $recordData['error_message'] = "Missing required fields for template '$templateCode': " . implode(', ', $missingFields);
                $results['allRecords'][] = $recordData;
                continue;
            }
            
            // Check for extra fields
            $extraFields = [];
            foreach ($allLotFields as $field) {
                if (!empty($data[$field]) && !in_array($field, $requiredDbFields)) {
                    $extraFields[] = $field;
                }
            }
            
            if (!empty($extraFields)) {
                $results['errorCount']++;
                $recordData['status'] = 'Error';
                if (empty($requiredFields)) {
                    $recordData['error_message'] = "Template '$templateCode' does not allow any lot-specific fields. Only catalogNumber and lotNumber should be provided";
                } else {
                    $recordData['error_message'] = "Extra fields not allowed for template '$templateCode': " . implode(', ', $extraFields);
                }
                $results['allRecords'][] = $recordData;
                continue;
            }
            
            // UPDATE lot
            $updateSql = "UPDATE lots SET templateCode = ?, generatePDF = ?, updatedAt = NOW()";
            $values = [$templateCode, 1];
            $types = "si";
            
            // Add template-specific fields
            foreach ($requiredFields as $fieldName => $dbField) {
                if (!empty($data[$dbField])) {
                    $updateSql .= ", $dbField = ?";
                    $values[] = $data[$dbField];
                    $types .= "s";
                }
            }
            
            $updateSql .= " WHERE catalogNumber = ? AND lotNumber = ?";
            $values[] = $data['catalogNumber'];
            $values[] = $data['lotNumber'];
            $types .= "ss";
            
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param($types, ...$values);
            
            if ($updateStmt->execute()) {
                $results['updateCount']++;
                $results['updatedRecords'][] = [
                    'row' => $i,
                    'catalogNumber' => $data['catalogNumber'],
                    'lotNumber' => $data['lotNumber']
                ];
                
                $recordData['status'] = 'Would_Update'; // TEMPORARY STATUS
                $results['allRecords'][] = $recordData;
            } else {
                $results['errorCount']++;
                $recordData['status'] = 'Error';
                $recordData['error_message'] = "Failed to update lot - Database error";
                $results['allRecords'][] = $recordData;
            }
            $updateStmt->close();
            continue;
        }
        $checkStmt->close();
        
        // STEP 5: Validate required fields for this template (for new lot)
        $requiredFields = $fieldMapping[$templateCode]['lot'] ?? [];
        $requiredDbFields = array_values($requiredFields);
        
        // Check for missing required fields
        $missingFields = [];
        foreach ($requiredFields as $fieldName => $dbField) {
            if (!isset($data[$dbField]) || trim($data[$dbField]) === '') {
                $missingFields[] = $fieldName;
            }
        }
        
        if (!empty($missingFields)) {
            $results['errorCount']++;
            $recordData['status'] = 'Error';
            $recordData['error_message'] = "Missing required fields for template '$templateCode': " . implode(', ', $missingFields);
            $results['allRecords'][] = $recordData;
            continue;
        }
        
        // Check for extra fields
        $extraFields = [];
        foreach ($allLotFields as $field) {
            if (!empty($data[$field]) && !in_array($field, $requiredDbFields)) {
                $extraFields[] = $field;
            }
        }
        
        if (!empty($extraFields)) {
            $results['errorCount']++;
            $recordData['status'] = 'Error';
            if (empty($requiredFields)) {
                $recordData['error_message'] = "Template '$templateCode' does not allow any lot-specific fields. Only catalogNumber and lotNumber should be provided";
            } else {
                $recordData['error_message'] = "Extra fields not allowed for template '$templateCode': " . implode(', ', $extraFields);
            }
            $results['allRecords'][] = $recordData;
            continue;
        }
        
        // STEP 6: Insert lot
        $insertSql = "INSERT INTO lots (catalogNumber, lotNumber, templateCode, generatePDF";
        $values = [$data['catalogNumber'], $data['lotNumber'], $templateCode, 1];
        $types = "sssi";
        
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
            $recordData['status'] = 'Would_Insert'; // TEMPORARY STATUS
            $results['allRecords'][] = $recordData;
        } else {
            $results['errorCount']++;
            $recordData['status'] = 'Error';
            $recordData['error_message'] = "Failed to insert lot - Database error";
            $results['allRecords'][] = $recordData;
        }
        $insertStmt->close();
    }
    
    // FINALIZE STATUSES BASED ON WHETHER THERE ARE ERRORS
    if ($results['errorCount'] > 0) {
        // Transaction will be rolled back, so nothing is actually saved
        // Convert Would_Insert and Would_Update to Valid
        foreach ($results['allRecords'] as &$record) {
            if ($record['status'] === 'Would_Insert' || $record['status'] === 'Would_Update') {
                $record['status'] = 'Valid'; // Passed validation but not saved
            }
        }
        // Reset counts since nothing was actually saved
        $results['successCount'] = 0;
        $results['updateCount'] = 0;
    } else {
        // Transaction will be committed, convert temporary statuses to final
        foreach ($results['allRecords'] as &$record) {
            if ($record['status'] === 'Would_Insert') {
                $record['status'] = 'Inserted';
            } elseif ($record['status'] === 'Would_Update') {
                $record['status'] = 'Updated';
            }
        }
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
 * Generate updated records report
 */
function generateUpdatedReport($updatedRecords, $uploadType) {
    // Create reports directory if it doesn't exist
    $reportsDir = '../reports';
    if (!file_exists($reportsDir)) {
        mkdir($reportsDir, 0755, true);
    }
    
    // Delete any existing updated file for this type
    $existingFiles = glob($reportsDir . '/updated_' . $uploadType . '_*.xlsx');
    foreach ($existingFiles as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }
    
    // Generate filename with timestamp
    $timestamp = date('Ymd_His');
    $filename = "updated_{$uploadType}_{$timestamp}.xlsx";
    $filepath = $reportsDir . '/' . $filename;
    
    // Create new spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set headers
    if ($uploadType === 'catalog') {
        $headers = ['row_number', 'catalog_number', 'catalog_name'];
    } else {
        $headers = ['row_number', 'catalog_number', 'lot_number'];
    }
    $sheet->fromArray($headers, null, 'A1');
    
    // Style headers
    $sheet->getStyle('A1:C1')->getFont()->setBold(true);
    
    // Add data
    $rowNum = 2;
    foreach ($updatedRecords as $record) {
        if ($uploadType === 'catalog') {
            $sheet->fromArray([
                $record['row'],
                $record['catalogNumber'],
                $record['catalogName']
            ], null, "A{$rowNum}");
        } else {
            $sheet->fromArray([
                $record['row'],
                $record['catalogNumber'],
                $record['lotNumber']
            ], null, "A{$rowNum}");
        }
        $rowNum++;
    }
    
    // Auto-size columns
    foreach (range('A', 'C') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Save Excel file
    $writer = new Xlsx($spreadsheet);
    $writer->save($filepath);
    
    return $filename;
}

/**
 * Generate complete upload report with all records and their status
 * Status values:
 * - Error: Row has validation errors
 * - Valid: Row passed validation but was not saved due to errors in other rows
 * - Inserted: Row was successfully inserted (only when no errors in entire file)
 * - Updated: Row was successfully updated (only when no errors in entire file)
 */
function generateCompleteReport($allRecords, $uploadType) {
    // Create reports directory if it doesn't exist
    $reportsDir = '../reports';
    if (!file_exists($reportsDir)) {
        mkdir($reportsDir, 0755, true);
    }
    
    // Delete any existing complete report file for this type
    $existingFiles = glob($reportsDir . '/upload_report_' . $uploadType . '_*.xlsx');
    foreach ($existingFiles as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }
    
    // Generate filename with timestamp
    $timestamp = date('Ymd_His');
    $filename = "upload_report_{$uploadType}_{$timestamp}.xlsx";
    $filepath = $reportsDir . '/' . $filename;
    
    // Create new spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Determine headers based on upload type
    if ($uploadType === 'catalog') {
        $headers = CATALOG_HEADERS;
    } else {
        $headers = LOT_HEADERS;
    }
    
    // Add status and error_message columns
    $headers[] = 'status';
    $headers[] = 'error_message';
    
    // Set headers
    $sheet->fromArray($headers, null, 'A1');
    
    // Style headers
    $lastCol = chr(64 + count($headers)); // Convert number to letter (A, B, C, etc.)
    $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
    
    // Add data
    $rowNum = 2;
    foreach ($allRecords as $record) {
        $rowData = [];
        // Add all original fields in order
        foreach (($uploadType === 'catalog' ? CATALOG_HEADERS : LOT_HEADERS) as $header) {
            $rowData[] = isset($record[$header]) ? $record[$header] : '';
        }
        // Add status
        $rowData[] = $record['status'];
        // Add error message (empty if no error)
        $rowData[] = isset($record['error_message']) ? $record['error_message'] : '';
        
        $sheet->fromArray($rowData, null, "A{$rowNum}");
        
        $rowNum++;
    }
    
    // Auto-size columns
    foreach (range('A', $lastCol) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Save Excel file
    $writer = new Xlsx($spreadsheet);
    $writer->save($filepath);
    
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