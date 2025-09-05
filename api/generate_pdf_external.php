<?php
// api/generate_pdf_external.php
// require_once '../config/database.php';
// require_once './pdf_common.php';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/pdf_common.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

function generatePDFExternal($lot_number) {
    try {
        if (empty($lot_number)) {
            throw new Exception('Lot number is required');
        }

        // Get catalog number from the lots table
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT catalogNumber FROM lots WHERE lotNumber = ? LIMIT 1");
        $stmt->bind_param("s", $lot_number);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        // $conn->close();

        if (!$row || empty($row['catalogNumber'])) {
            throw new Exception("Could not find a catalog number for the provided lot number.");
        }
        $catalog_number = $row['catalogNumber'];

        // Get data for PDF generation
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
        
        // Log PDF generation
        try {
            // $conn = getDBConnection();

            // Check for and delete existing PDFs
            $check_sql = "SELECT filename FROM pdf_generation_log WHERE catalogNumber = ? AND lotNumber = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ss", $catalog_number, $lot_number);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $existing_pdfs = $result->fetch_all(MYSQLI_ASSOC);
            $check_stmt->close();

            if (!empty($existing_pdfs)) {
                foreach ($existing_pdfs as $pdf) {
                    $old_filepath = __DIR__ . '/../generated_pdfs/' . $pdf['filename'];
                    if (file_exists($old_filepath)) {
                        unlink($old_filepath);
                    }

                    // Update existing log entry with new filename and timestamp
                    $update_sql = "UPDATE pdf_generation_log SET filename = ?, generatedAt = NOW() WHERE catalogNumber = ? AND lotNumber = ? AND templateCode = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ssss", $filename, $catalog_number, $lot_number, $data['template_code']);
                    $update_stmt->execute();
                    $update_stmt->close();
                }
            }
            else {
                // Insert new log entry
                $log_sql = "INSERT INTO pdf_generation_log (catalogNumber, lotNumber, templateCode, filename, generatedAt) 
                            VALUES (?, ?, ?, ?, NOW())";
                $log_stmt = $conn->prepare($log_sql);
                $log_stmt->bind_param("ssss", $catalog_number, $lot_number, $data['template_code'], $filename);
                $log_stmt->execute();
                $log_stmt->close();
                // $conn->close();
            }

        } catch (Exception $logException) {
            error_log("PDF generation log failed: " . $logException->getMessage());
        }

        $filepath = __DIR__. '/../generated_pdfs/' . $filename;

        // Save PDF to file
        $mpdf->Output($filepath, 'F');

        // Update the lots table to indicate PDF has been generated
        try {
            $update_lot_sql = "UPDATE lots SET generatePDF = 0 WHERE lotNumber = ?";
            $update_lot_stmt = $conn->prepare($update_lot_sql);
            $update_lot_stmt->bind_param("s", $lot_number);
            $update_lot_stmt->execute();
            $update_lot_stmt->close();

        } catch (Exception $lotException) {
            error_log("Failed to update lot after PDF generation: " . $lotException->getMessage());
        }

        // Return success response as JSON
        return json_encode([
            'success' => true,
            'message' => 'PDF generated successfully.',
            'file' => $filename,
            'filepath' => realpath($filepath)
        ]);
        
    } catch (Exception $e) {
        // Return error response as JSON
        return json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    finally{
        if (isset($conn)) {
           closeDBConnection($conn);
        }
    }
}
?>