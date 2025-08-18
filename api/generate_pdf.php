<?php
// api/generate_pdf_mpdf.php - mPDF implementation example
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Use the mPDF version of pdf_common
require_once 'pdf_common.php';

try {
    // Get parameters
    $catalog_number = $_GET['catalog_number'] ?? '';
    $lot_number = $_GET['lot_number'] ?? '';
    
    // Validate required parameters
    if (empty($catalog_number)) {
        throw new Exception('Catalog number is required');
    }
    
    if (empty($lot_number)) {
        throw new Exception('Lot number is required');
    }
    
    // Get data from database
    $data = getCoAData($catalog_number, $lot_number);
    
    // Validate all required fields
    $errors = validateAllFields($data['catalog'], $data['lot'], $data['template_code']);
    
    if (!empty($errors)) {
        throw new Exception('Missing required fields: ' . implode(', ', $errors));
    }
    
    // Generate PDF using mPDF
    $mpdf = generatePDF($data['catalog'], $data['lot'], $data['template_code']);
    
    // Generate filename
    $filename = generateFilename($catalog_number, $lot_number);
    
    // Log PDF generation (optional)
    try {
        $conn = getDBConnection();
        $log_sql = "INSERT INTO pdf_generation_log (catalogNumber, lotNumber, templateCode, generatedAt) 
                    VALUES (?, ?, ?, NOW())";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("sss", $catalog_number, $lot_number, $data['template_code']);
        $log_stmt->execute();
        $log_stmt->close();
        $conn->close();
    } catch (Exception $logException) {
        // Silently fail logging - don't interrupt PDF generation
        error_log("PDF generation log failed: " . $logException->getMessage());
    }
    
    //windows path
        //file path to save the PDF
        if (!is_dir(__DIR__ . '/generated_pdfs')) {
            mkdir(__DIR__ . '/generated_pdfs', 0755, true);
        }
        // $filepath = __DIR__ . '//..//generated_pdfs//' . $filename;
        $filepath = __DIR__ . '/../generated_pdfs/' . $filename;

    //linux path
        // Define the path for the generated PDFs directory, one level above the current 'api' directory.
        $pdf_dir = dirname(__DIR__) . '/generated_pdfs';

    // Output PDF
    // 'D' = force download
    // 'I' = inline (display in browser)
    // 'F' = save to file
    // 'S' = return as string
    $mpdf->Output($filepath, 'F');
    // Output to browser
    //$mpdf->Output($filepath, 'I');

    // echo "file generated successfully";

    // Respond with JSON
    echo json_encode([
        'success' => true,
        'message' => 'PDF generated successfully',
        'file' => 'generated_pdfs/' . $filename // or provide a download link if needed
    ]);
    exit;
    
} catch (Exception $e) {
    // Display error page
    displayError($e->getMessage());
}