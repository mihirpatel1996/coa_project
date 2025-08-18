<?php
/**
 * Bulk Upload API - Modified to UPDATE existing records instead of skipping
 * Handles bulk upload of catalogs and lots from Excel files
 */

require_once 'config.php';
require_once 'template_config.php';
require_once '../vendor/autoload.php';

// PHPSpreadsheet imports
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Constants
const MAX_ROWS = 5000;
const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB

// Define expected headers
const CATALOG_HEADERS = ['templateCode', 'catalogNumber', 'catalogName', 'activity', 'cas', 'detail', 'formulation', 'molFormula', 'observedMolMass', 'predictedMolMass', 'predictedNTerminal', 'reconstitution', 'shipping', 'source', 'stability'];
const LOT_HEADERS = ['templateCode', 'lotNumber', 'catalogNumber', 'activity', 'concentration', 'purity', 'formulation'];

// Debug flag
$debugMessages = [];

// Error handler
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Validate upload type
    if (!isset($_POST['uploadType']) || !in_array($_POST['uploadType'], ['catalog', 'lot'])) {
        throw new Exception('Invalid upload type');
    }
    
    $uploadType = $_POST['uploadType'];
    
    // Validate file upload
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload failed');
    }
    
    $uploadedFile = $_FILES['file'];
    $fileName = basename($uploadedFile['name']);
    
    // Validate file size
    if ($uploadedFile['size'] > MAX_FILE_SIZE) {
        throw new Exception('File size exceeds maximum allowed size (10MB)');
    }
    
    // Validate file extension
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if (!in_array($fileExtension, ['xlsx', 'xls'])) {
        throw new Exception('Invalid file format. Only Excel files (.xlsx, .xls) are allowed');
    }
    
    // Load the spreadsheet
    $spreadsheet = IOFactory::load($uploadedFile['tmp_name']);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();
    
    // Validate minimum rows
    if (count($rows) < 1) {
        throw new Exception('File is empty');
    }
    
    // Validate maximum rows
    if (count($rows) > MAX_ROWS) {
        throw new Exception('File contains too many rows (maximum ' . MAX_ROWS . ')');
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
    $expectedHeaders = $uploadType === 'catalog' ? CATALOG_HEADERS : LOT_HEADERS;
    if ($headers !== $expectedHeaders) {
        throw new Exception('Invalid Excel headers. Please use the provided template');
    }
    
    // Process data
    $conn = getDBConnection();
    $conn->autocommit(false);
    
    $results = [
        'totalRows' => count($rows) - 1,
        'successCount' => 0,
        'updateCount' => 0,  // NEW: Track updates
        'skippedCount' => 0,
        'skippedRecords' => [],
        'updatedRecords' => []  // NEW: Track updated records
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
        
        // Generate reports if needed
        $skippedReportPath = null;
        if ($results['skippedCount'] > 0) {
            $skippedReportPath = generateSkippedReport($results['skippedRecords'], $uploadType);
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
            'updateCount' => $results['updateCount'],  // NEW: Include update count
            'skippedCount' => $results['skippedCount'],
            'errorCount' => 0,
            'skippedReportPath' => $skippedReportPath,
            'completeReportPath' => $completeReportPath
        ];
        
        $status = ($results['skippedCount'] > 0) ? 'partial' : 'success';
        logUpload($conn, $status, $summary, null);
        
        // Return success response
        $response = [
            'success' => true,
            'status' => $status,
            'summary' => $summary
        ];
        
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
 * Process catalog upload with UPDATE support
 */
function processCatalogUpload($conn, $rows, $headers) {
    $results = [
        'totalRows' => count($rows) - 1,
        'successCount' => 0,
        'updateCount' => 0,  // NEW: Track updates
        'skippedCount' => 0,
        'skippedRecords' => [],
        'updatedRecords' => [],  // NEW: Track updated records
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
            throw new Exception("Row $i: Column count mismatch. Expected " . count($headers) . " columns, got " . count($row));
        }

        $data = array_combine($headers, $row);
        // Handle null values before trimming (PHP 8.1+ compatibility)
        $data = array_map(function($value) {
            return $value === null ? '' : trim($value);
        }, $data);
        
        // Prepare record data for tracking
        $recordData = array_merge(['row' => $i], $data);
        
        // STEP 1: Validate basic required fields
        if (empty($data['templateCode']) || empty($data['catalogNumber']) || empty($data['catalogName'])) {
            throw new Exception("Row $i: Missing required field (templateCode, catalogNumber, or catalogName)");
        }
        
        // STEP 2: Validate template code exists
        if (!isset(TEMPLATES[$data['templateCode']])) {
            throw new Exception("Row $i: Invalid template code '{$data['templateCode']}'");
        }
        
        $templateCode = $data['templateCode'];
        
        // STEP 3: Check required fields for template
        $requiredFields = $fieldMapping[$templateCode]['catalog'] ?? [];
        foreach ($requiredFields as $fieldName => $dbField) {
            if (!isset($data[$dbField]) || trim($data[$dbField]) === '') {
                throw new Exception("Row $i: Missing required field '$fieldName' for template '$templateCode'");
            }
        }
        
        // STEP 4: Validate no extra fields for template
        foreach ($allCatalogFields as $field) {
            if (!empty($data[$field]) && !in_array($field, array_values($requiredFields))) {
                $field = array_search($field, $headers) !== false ? $headers[array_search($field, $headers)] : $field;
                throw new Exception("Row $i: Extra field '$field' not allowed for template '$templateCode'. Only allowed fields are: " . implode(', ', array_keys($requiredFields)));
            }
        }
        
        // STEP 5: Check if catalog already exists
        $checkSql = "SELECT id, templateCode FROM catalogs WHERE catalogNumber = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $data['catalogNumber']);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            // Catalog exists - UPDATE it
            $existingCatalog = $checkResult->fetch_assoc();
            $checkStmt->close();
            
            // Verify template code matches
            if ($existingCatalog['templateCode'] !== $templateCode) {
                throw new Exception("Row $i: Catalog '{$data['catalogNumber']}' already exists with different template code '{$existingCatalog['templateCode']}'");
            }
            
            // Build UPDATE query
            $updateSql = "UPDATE catalogs SET catalogName = ?, updatedAt = NOW()";
            $values = [$data['catalogName']];
            $types = "s";
            
            // Add all catalog fields (both required and optional)
            foreach ($allCatalogFields as $field) {
                if (isset($data[$field])) {
                    $updateSql .= ", $field = ?";
                    $values[] = $data[$field];
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
                
                // Add to all records with status
                $recordData['status'] = 'Updated';
                $results['allRecords'][] = $recordData;
            } else {
                throw new Exception("Row $i: Failed to update catalog - " . $conn->error);
            }
            $updateStmt->close();
            
        } else {
            // Catalog doesn't exist - INSERT it
            $checkStmt->close();
            
            // Insert catalog
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
                
                // Add to all records with status
                $recordData['status'] = 'Inserted';
                $results['allRecords'][] = $recordData;
            } else {
                throw new Exception("Row $i: Failed to insert catalog - " . $conn->error);
            }
            $insertStmt->close();
        }
    }
    
    return $results;
}

/**
 * Process lot upload with UPDATE support
 */
function processLotUpload($conn, $rows, $headers) {
    $results = [
        'totalRows' => count($rows) - 1,
        'successCount' => 0,
        'updateCount' => 0,  // NEW: Track updates
        'skippedCount' => 0,
        'skippedRecords' => [],
        'updatedRecords' => [],  // NEW: Track updated records
        'allRecords' => []
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
        // Handle null values before trimming (PHP 8.1+ compatibility)
        $data = array_map(function($value) {
            return $value === null ? '' : trim($value);
        }, $data);
        
        // Prepare record data for tracking
        $recordData = array_merge(['row' => $i], $data);
        
        // STEP 1: Validate basic required fields
        if (empty($data['templateCode']) || empty($data['catalogNumber']) || empty($data['lotNumber'])) {
            throw new Exception("Row $i: Missing required field (templateCode, catalogNumber, or lotNumber)");
        }
        
        // STEP 2: Validate template code exists
        if (!isset(TEMPLATES[$data['templateCode']])) {
            throw new Exception("Row $i: Invalid template code '{$data['templateCode']}'");
        }
        
        $templateCode = $data['templateCode'];
        
        // STEP 3: Check if catalog exists
        $catalogSql = "SELECT id, templateCode FROM catalogs WHERE catalogNumber = ?";
        $catalogStmt = $conn->prepare($catalogSql);
        $catalogStmt->bind_param("s", $data['catalogNumber']);
        $catalogStmt->execute();
        $catalogResult = $catalogStmt->get_result();
        
        if ($catalogResult->num_rows === 0) {
            // Catalog doesn't exist, skip this lot
            $results['skippedCount']++;
            $results['skippedRecords'][] = [
                'row' => $i,
                'catalogNumber' => $data['catalogNumber'],
                'lotNumber' => $data['lotNumber'],
                'reason' => 'Catalog does not exist'
            ];
            $catalogStmt->close();
            
            // Add to all records with status
            $recordData['status'] = 'Skipped - No Catalog';
            $results['allRecords'][] = $recordData;
            continue;
        }
        
        $catalog = $catalogResult->fetch_assoc();
        $catalogStmt->close();
        
        // STEP 4: Verify template code matches
        if ($catalog['templateCode'] !== $templateCode) {
            throw new Exception("Row $i: Template code mismatch. Catalog '{$data['catalogNumber']}' has template '{$catalog['templateCode']}', but row specifies '{$templateCode}'");
        }
        
        // STEP 5: Get required fields for this template
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
        
        // STEP 6: Check if lot already exists
        $lotSql = "SELECT id FROM lots WHERE catalogNumber = ? AND lotNumber = ?";
        $lotStmt = $conn->prepare($lotSql);
        $lotStmt->bind_param("ss", $data['catalogNumber'], $data['lotNumber']);
        $lotStmt->execute();
        $lotResult = $lotStmt->get_result();
        
        if ($lotResult->num_rows > 0) {
            // Lot exists - UPDATE it
            $lotStmt->close();
            
            // Build UPDATE query
            $updateSql = "UPDATE lots SET templateCode = ?, updatedAt = NOW()";
            $values = [$templateCode];
            $types = "s";
            
            // Add all lot fields (both required and optional)
            foreach ($allLotFields as $field) {
                if (isset($data[$field])) {
                    $updateSql .= ", $field = ?";
                    $values[] = $data[$field];
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
                
                // Add to all records with status
                $recordData['status'] = 'Updated';
                $results['allRecords'][] = $recordData;
            } else {
                throw new Exception("Row $i: Failed to update lot - " . $conn->error);
            }
            $updateStmt->close();
            
        } else {
            // Lot doesn't exist - INSERT it
            $lotStmt->close();
            
            // Insert lot
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
                
                // Add to all records with status
                $recordData['status'] = 'Inserted';
                $results['allRecords'][] = $recordData;
            } else {
                throw new Exception("Row $i: Failed to insert lot - " . $conn->error);
            }
            $insertStmt->close();
        }
    }
    
    return $results;
}

/**
 * Generate skipped records report
 */
function generateSkippedReport($skippedRecords, $uploadType) {
    if (empty($skippedRecords)) {
        return null;
    }
    
    $timestamp = date('Ymd_His');
    $fileName = "skipped_{$uploadType}_{$timestamp}.csv";
    $filePath = __DIR__ . '/upload_reports/' . $fileName;
    
    // Ensure directory exists
    if (!is_dir(__DIR__ . '/upload_reports')) {
        mkdir(__DIR__ . '/upload_reports', 0755, true);
    }
    
    // Create CSV
    $fp = fopen($filePath, 'w');
    
    // Write header
    fputcsv($fp, ['Row', 'Catalog Number', 'Lot Number', 'Reason']);
    
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
    
    return $fileName;
}

/**
 * Generate complete upload report with status
 */
function generateCompleteReport($allRecords, $uploadType) {
    if (empty($allRecords)) {
        return null;
    }
    
    $timestamp = date('Ymd_His');
    $fileName = "complete_{$uploadType}_{$timestamp}.csv";
    $filePath = __DIR__ . '/upload_reports/' . $fileName;
    
    // Ensure directory exists
    if (!is_dir(__DIR__ . '/upload_reports')) {
        mkdir(__DIR__ . '/upload_reports', 0755, true);
    }
    
    // Create CSV
    $fp = fopen($filePath, 'w');
    
    // Get headers from first record
    $firstRecord = reset($allRecords);
    $headers = array_keys($firstRecord);
    
    // Write header
    fputcsv($fp, $headers);
    
    // Write data
    foreach ($allRecords as $record) {
        fputcsv($fp, array_values($record));
    }
    
    fclose($fp);
    
    return $fileName;
}

/**
 * Log upload to database
 */
function logUpload($conn, $status, $summary, $errorMessage = null) {
    $sql = "INSERT INTO uploadlogs (STATUS, summary, errorMessage) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    $summaryJson = json_encode($summary);
    
    $stmt->bind_param("sss", $status, $summaryJson, $errorMessage);
    $stmt->execute();
    $stmt->close();
}

/**
 * Get database connection
 */
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

/**
 * Get field mapping for templates from template_config.php
 */
function getFieldMappingForTemplates() {
    $fieldMapping = [];
    
    foreach (TEMPLATES as $code => $template) {
        $fieldMapping[$code] = [];
        
        // Get all fields for this template
        $allFields = array_merge(
            $template['required_fields'] ?? [],
            $template['optional_fields'] ?? []
        );
        
        // Map display names to database field names
        foreach ($allFields as $field) {
            // Convert display name to database field name
            $dbFieldName = lcfirst(str_replace(' ', '', $field));
            
            // Special cases
            if ($field === 'Observed mol mass') {
                $dbFieldName = 'observedMolMass';
            } elseif ($field === 'Predicted mol mass') {
                $dbFieldName = 'predictedMolMass';
            } elseif ($field === 'Mol formula') {
                $dbFieldName = 'molFormula';
            } elseif ($field === 'Predicted N terminal') {
                $dbFieldName = 'predictedNTerminal';
            } elseif ($field === 'CAS') {
                $dbFieldName = 'cas';
            }
            
            // Only map required fields
            if (in_array($field, $template['required_fields'] ?? [])) {
                $fieldMapping[$code][$field] = $dbFieldName;
            }
        }
    }
    
    return $fieldMapping;
}
?>