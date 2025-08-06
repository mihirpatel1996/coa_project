<?php
// api/preview_pdf.php
// Preview PDF in browser - No logging

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
    
    // Get data
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
    
    // Add PREVIEW watermark (optional)
    // Uncomment the following lines if you want a watermark for preview
    /*
    $pdf->SetAlpha(0.1);
    $pdf->SetTextColor(200, 200, 200);
    $pdf->StartTransform();
    $pdf->Rotate(45, 105, 200);
    $pdf->SetFont('helvetica', 'B', 60);
    $pdf->Text(50, 170, 'PREVIEW');
    $pdf->StopTransform();
    $pdf->SetAlpha(1);
    */
    
    // Output PDF for preview (inline in browser)
    $pdf->Output($filename, 'I');
    
} catch (Exception $e) {
    // Display error page
    displayError($e->getMessage());
}
?>