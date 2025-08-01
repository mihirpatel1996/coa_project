<?php
// api/generate_pdf.php
// Generate and download PDF - CLEAN VERSION

header('Access-Control-Allow-Origin: *');

require_once 'pdf_common.php';

try {
    // Get parameters
    $catalog_number = isset($_GET['catalog_number']) ? trim($_GET['catalog_number']) : '';
    $lot_number = isset($_GET['lot_number']) ? trim($_GET['lot_number']) : '';
    
    // Validate required parameters
    if (empty($catalog_number) || empty($lot_number)) {
        throw new Exception('Catalog number and lot number are required');
    }
    
    // Get data from database
    $data = getCoAData($catalog_number, $lot_number);
    $catalog_data = $data['catalog'];
    $lot_data = $data['lot'];
    $template_code = $data['template_code'];
    
    // Validate all fields are filled
    $validation_errors = validateAllFields($catalog_data, $lot_data, $template_code);
    if (!empty($validation_errors)) {
        throw new Exception('Missing required fields: ' . implode(', ', $validation_errors));
    }
    
    // Generate PDF
    $pdf = generatePDF($catalog_data, $lot_data, $template_code);
    
    // Generate filename
    $filename = generateFilename($catalog_number, $lot_number);
    
    // Log PDF generation to database (optional - silent fail if error)
    try {
        logPDFGeneration($catalog_number, $lot_number, $template_code);
    } catch (Exception $e) {
        // Silent fail - don't break PDF generation for logging issues
        error_log("PDF log failed: " . $e->getMessage());
    }
    
    // Output PDF for download
    // 'D' = force download
    // 'I' = inline in browser
    $pdf->Output($filename, 'I');
    
} catch (Exception $e) {
    // Display error page
    displayError($e->getMessage());
}

/**
 * Log PDF generation to database
 */
function logPDFGeneration($catalog_number, $lot_number, $template_code) {
    $conn = getDBConnection();
    
    // Check if table exists first
    $table_check = $conn->query("SHOW TABLES LIKE 'pdf_generation_log'");
    
    if ($table_check && $table_check->num_rows > 0) {
        // Insert log entry
        $log_sql = "INSERT INTO pdf_generation_log 
                   (catalogNumber, lotNumber, templateCode, generatedAt) 
                   VALUES (?, ?, ?, NOW())";
        
        $log_stmt = $conn->prepare($log_sql);
        if ($log_stmt) {
            $log_stmt->bind_param("sss", $catalog_number, $lot_number, $template_code);
            $log_stmt->execute();
            $log_stmt->close();
        }
    }
    
    $conn->close();
}
?>