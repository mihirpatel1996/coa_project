<?php
// api/generate_pdf.php
// Generate PDF - Save to server AND provide for download with logging

header('Access-Control-Allow-Origin: *');

require_once 'pdf_common.php';

try {
    // Get parameters
    $catalog_number = isset($_GET['catalog_number']) ? trim($_GET['catalog_number']) : '';
    $lot_number = isset($_GET['lot_number']) ? trim($_GET['lot_number']) : '';
    
    // Validate required parameters
    if (empty($catalog_number)) {
        throw new Exception('Catalog number is required');
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
    
    // Create directory for PDFs if it doesn't exist
    // $pdf_dir =  dirname(__FILE__) .'/generated_pdfs';
        $pdf_dir =  dirname(__FILE__) .'\\..\\generated_pdfs\\';
    if (!file_exists($pdf_dir)) {
        mkdir($pdf_dir, 0755, true);
    }
    
    // Full path for saving
    // $filepath = $pdf_dir . '/' . $filename;
    $filepath = $pdf_dir . $filename;
    // echo "saving to $filepath\n";
    // exit();
    // return;
    
    // Save PDF to server AND send to browser
    // 'FI' = save to File and send Inline to browser
    $pdf->Output($filepath, 'FI');
    
    // Log PDF generation to database
    try {
        $conn = getDBConnection();
        $log_sql = "INSERT INTO pdf_generation_log (catalogNumber, lotNumber, templateCode, generatedAt) 
                    VALUES (?, ?, ?, NOW())";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("sss", $catalog_number, $lot_number, $template_code);
        $log_stmt->execute();
        $log_stmt->close();
        $conn->close();
        
        // error_log("PDF generated and logged: $filename");
    } catch (Exception $e) {
        // Log error but don't fail the PDF generation
        error_log("Failed to log PDF generation: " . $e->getMessage());
    }
    
} catch (Exception $e) {
    // Display error page
    displayError($e->getMessage());
}
?>