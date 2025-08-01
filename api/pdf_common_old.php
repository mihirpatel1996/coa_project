<?php
// api/generate_pdf.php
// Generate and download PDF - FIXED VERSION

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
    
    // Optional: Log PDF generation (disabled to avoid connection issues)
    // logPDFGeneration($catalog_number, $lot_number, $template_code);
    
    // Output PDF for download
    // 'D' = force download
    // 'I' = inline in browser
    $pdf->Output($filename, 'I');
    
} catch (Exception $e) {
    // Display error page
    displayError($e->getMessage());
}

/**
 * Log PDF generation to database (optional)
 * Currently disabled to avoid database connection conflicts
 */
function logPDFGeneration($catalog_number, $lot_number, $template_code) {
    try {
        // Would need to create a fresh database connection here
        // Disabled for now to keep things simple
        return;
    } catch (Exception $e) {
        // Silent fail
        error_log("PDF log failed: " . $e->getMessage());
    }
}
?>