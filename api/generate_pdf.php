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

        // Check for and delete existing PDFs for this catalog and lot number
        $check_sql = "SELECT filename FROM pdf_generation_log WHERE catalogNumber = ? AND lotNumber = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $catalog_number, $lot_number);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $existing_pdfs = $result->fetch_all(MYSQLI_ASSOC);
        $check_stmt->close();

        if (!empty($existing_pdfs)) {
            // Delete old PDF files
            foreach ($existing_pdfs as $pdf) {
                $old_filepath = __DIR__ . '/../generated_pdfs/' . $pdf['filename'];
                if (file_exists($old_filepath)) {
                    unlink($old_filepath);
                }
            }

            // Delete old log entries
            $delete_sql = "DELETE FROM pdf_generation_log WHERE catalogNumber = ? AND lotNumber = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("ss", $catalog_number, $lot_number);
            $delete_stmt->execute();
            $delete_stmt->close();
        }

        // Insert new log entry
        $log_sql = "INSERT INTO pdf_generation_log (catalogNumber, lotNumber, templateCode, filename, generatedAt) 
                    VALUES (?, ?, ?, ?, NOW())";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("ssss", $catalog_number, $lot_number, $data['template_code'], $filename);
        $log_stmt->execute();
        $log_stmt->close();
        $conn->close();
    } catch (Exception $logException) {
        // Silently fail logging - don't interrupt PDF generation
        error_log("PDF generation log failed: " . $logException->getMessage());
    }

	// if (PHP_OS_FAMILY === 'Windows') {
	// 	// $filepath = __DIR__ . '//..//generated_pdfs//' . $filename;
    //     $filepath = __DIR__ . '/../generated_pdfs/' . $filename;
	// } 
	// if (PHP_OS_FAMILY === 'Linux') {
	// 	// Define the path for the generated PDFs directory, one level above the current 'api' directory.
    //     $pdf_dir = dirname(__DIR__) . '/generated_pdfs';
    //     $filepath = $pdf_dir . '/' . $filename;
	// }
    $filepath = '../generated_pdfs/' . $filename;

    // Output PDF
    // 'D' = force download
    // 'I' = inline (display in browser)
    // 'F' = save to file
    // 'S' = return as string
    $mpdf->Output($filepath, 'F');
    // Output to browser
    $mpdf->Output($filename, 'D');

    // echo "file generated successfully";

    // Respond with JSON
    // echo json_encode([
    //     'success' => true,
    //     'message' => 'PDF generated successfully',
    //     'file' => 'generated_pdfs/' . $filename // or provide a download link if needed
    // ]);
    exit;
    
} catch (Exception $e) {
    // Display error page
    displayError($e->getMessage());
}