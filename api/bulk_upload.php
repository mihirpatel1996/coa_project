<?php
// api/bulk_upload.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

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
    
    // Validate file type - CHANGED TO EXCEL
    if (!preg_match('/\.(xlsx|xls)$/i', $fileName)) {
        throw new Exception('Invalid file type. Only Excel files are allowed');
    }
    
    // Read Excel file - REPLACED CSV READING
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
        'updateCount' => 0,  // ADDED
        'skippedCount' => 0,
        'skippedRecords' => [],
        'updatedRecords' => []  // ADDED
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
        
        // Generate updated report if needed - CHANGED
        $updatedReportPath = null;
        if ($results['updateCount'] > 0) {
            $updatedReportPath = generateUpdatedReport($results['updatedRecords'], $uploadType);
        }
        
        // Generate complete upload report (always generated)
        $completeReportPath = null;
        if (!empty($results['allRecords'])) {
            $completeReportPath = generateCompleteReport($results['allRecords'], $uploadType);
        }
        
        // Log upload - ensure autocommit is on
        $summary = [
            'uploadType' => $uploadType,
            'fileName' => $fileName,
            'totalRows' => $results['totalRows'],
            'successCount' => $results['successCount'],
            'updateCount' => $results['updateCount'],  // ADDED
            'skippedCount' => $results['skippedCount'],
            'errorCount' => 0,
            'updatedReportPath' => $updatedReportPath,  // CHANGED from skippedReportPath
            'completeReportPath' => $completeReportPath
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
            'updateCount' => 0,  // ADDED
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
        'updateCount' => 0,  // ADDED
        'skippedCount' => 0,
        'skippedRecords' => [],
        'updatedRecords' => [],  // ADDED
        'allRecords' => []  // Store all processed records
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
        
        
        // STEP 1: Validate basic required fields
        if (empty($data['templateCode']) || empty($data['catalogNumber']) || empty($data['catalogName'])) {
            throw new Exception("Row $i: Missing required field (templateCode, catalogNumber, or catalogName)");
        }
        
        // STEP 2: Validate template code exists
        if (!isset(TEMPLATES[$data['templateCode']])) {
            throw new Exception("Row $i: Invalid template code '{$data['templateCode']}'");
        }
        
        $templateCode = $data['templateCode'];
        
        // STEP 3: Check if catalog already exists (UPDATE if exists) - CHANGED
        $checkSql = "SELECT id, templateCode FROM catalogs WHERE catalogNumber = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $data['catalogNumber']);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        // Prepare record for complete report
        $recordData = $data;
        $recordData['row'] = $i;
        
        if ($checkResult->num_rows > 0) {
            // Catalog exists, UPDATE it - CHANGED
            $existingCatalog = $checkResult->fetch_assoc();
            $checkStmt->close();
            
            // Verify template code matches
            if ($existingCatalog['templateCode'] !== $templateCode) {
                throw new Exception("Row $i: Catalog '{$data['catalogNumber']}' already exists with different template code '{$existingCatalog['templateCode']}'");
            }
            
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
            
            // UPDATE catalog - NEW
            $updateSql = "UPDATE catalogs SET catalogName = ?";
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
                
                // Add to all records with status
                $recordData['status'] = 'Updated';
                $results['allRecords'][] = $recordData;
            } else {
                throw new Exception("Row $i: Failed to update catalog - " . $conn->error);
            }
            $updateStmt->close();
            
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
            
            // Add to all records with status
            $recordData['status'] = 'Inserted';
            $results['allRecords'][] = $recordData;
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
        'updateCount' => 0,  // ADDED
        'skippedCount' => 0,
        'skippedRecords' => [],
        'updatedRecords' => [],  // ADDED
        'allRecords' => []  // Store all processed records
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
        
        // Prepare record for complete report
        $recordData = $data;
        $recordData['row'] = $i;
        
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
            
            // Add to all records with status
            $recordData['status'] = 'Skipped';
            $results['allRecords'][] = $recordData;
            
            $catalogStmt->close();
            continue;
        }
        
        $catalog = $catalogResult->fetch_assoc();
        $catalogStmt->close();
        
        // STEP 4: Check template match (STOP if mismatch)
        if ($catalog['templateCode'] !== $templateCode) {
            throw new Exception("Row $i: Template code mismatch. Catalog has template '{$catalog['templateCode']}' but lot specifies '{$templateCode}'");
        }
        
        // STEP 5: Check if lot already exists (UPDATE if exists) - CHANGED
        $checkSql = "SELECT id FROM lots WHERE catalogNumber = ? AND lotNumber = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ss", $data['catalogNumber'], $data['lotNumber']);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            // Lot exists, UPDATE it - CHANGED
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
            
            // UPDATE lot - NEW
            $updateSql = "UPDATE lots SET templateCode = ?";
            $values = [$templateCode];
            $types = "s";
            
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
                
                // Add to all records with status
                $recordData['status'] = 'Updated';
                $results['allRecords'][] = $recordData;
            } else {
                throw new Exception("Row $i: Failed to update lot - " . $conn->error);
            }
            $updateStmt->close();
            
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
            
            // Add to all records with status
            $recordData['status'] = 'Inserted';
            $results['allRecords'][] = $recordData;
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
 * Generate updated records report - ADDED NEW FUNCTION
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
    
    // Style headers (optional)
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

// NO CHANGES TO generateSkippedReport - KEEP AS IS

/**
 * Generate skipped records report - Keep only latest file for each type
 */
function generateSkippedReport($skippedRecords, $uploadType) {
    // Create reports directory if it doesn't exist
    $reportsDir = '../reports';
    if (!file_exists($reportsDir)) {
        mkdir($reportsDir, 0755, true);
    }
    
    // Delete any existing skipped file for this type
    $existingFiles = glob($reportsDir . '/skipped_' . $uploadType . '_*.xlsx');
    foreach ($existingFiles as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }
    
    // Generate filename with timestamp
    $timestamp = date('Ymd_His');
    $filename = "skipped_{$uploadType}_{$timestamp}.xlsx";
    $filepath = $reportsDir . '/' . $filename;
    
    // Create new spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set headers
    $headers = ['row_number', 'catalog_number', 'lot_number', 'reason'];
    $sheet->fromArray($headers, null, 'A1');
    
    // Style headers (optional)
    $sheet->getStyle('A1:D1')->getFont()->setBold(true);
    
    // Add data
    $rowNum = 2;
    foreach ($skippedRecords as $record) {
        $sheet->fromArray([
            $record['row'],
            $record['catalogNumber'],
            $record['lotNumber'],
            $record['reason']
        ], null, "A{$rowNum}");
        $rowNum++;
    }
    
    // Auto-size columns
    foreach (range('A', 'D') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Save Excel file
    $writer = new Xlsx($spreadsheet);
    $writer->save($filepath);
    
    return $filename;
}

// NO CHANGES TO OTHER FUNCTIONS - ALL REMAIN THE SAME

/**
 * Generate complete upload report with all records and their status
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
    
    // Add status column to headers
    $headers[] = 'status';
    
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