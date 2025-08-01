<?php
ini_set('log_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 0); // 0 for Off, 1 for On


// api/generate_pdf.php
// Save PDF to server and redirect to it

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
    
    // Get data
    $data = getCoAData($catalog_number, $lot_number);
    $catalog_data = $data['catalog'];
    $lot_data = $data['lot'];
    $template_code = $data['template_code'];
    
    var_dump($catalog_data, $lot_data, $template_code);
    
    // Validate all fields are filled
    $validation_errors = validateAllFields($catalog_data, $lot_data, $template_code);
    if (!empty($validation_errors)) {
        throw new Exception('Missing required fields: ' . implode(', ', $validation_errors));
    }
    
    echo "before generatePDF<br/>";
    // Generate PDF
    $pdf = generatePDF($catalog_data, $lot_data, $template_code);
    exit();
    // Generate filename
    $filename = generateFilename($catalog_number, $lot_number);
    
    // Calculate paths
    $current_script_dir = dirname(__FILE__); // This gives us /api
    echo "current_script_dir: $current_script_dir<br/>";
    exit();
    $project_root = dirname($current_script_dir); // This gives us project root
    $pdf_dir = $project_root . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR;
    $pdf_dir = '/var/www/html/COA_pdf_generator/pdf/'; // Use absolute path for server
    // Create pdf directory if it doesn't exist
    if (!file_exists($pdf_dir)) {
        if (!@mkdir($pdf_dir, 0755, true)) {
            throw new Exception('Failed to create PDF directory at: ' . $pdf_dir);
        }
    }
    
    // Full path for the PDF file
    $file_path = $pdf_dir . $filename;
    
    // Log to database FIRST (before any output)
    logPDFGeneration($catalog_number, $lot_number, $template_code);
    
    // Try to save PDF to file
    $save_success = false;
    
    // Method 1: Try direct output to file
    try {
        $pdf->Output($file_path, 'F');
        
        if (file_exists($file_path)) {
            $save_success = true;
            echo "file saved by method 1";
        }
    } catch (Exception $e) {
        // Method 2: Try with file_put_contents
        try {
            $pdf_content = $pdf->Output('', 'S');
            if (file_put_contents($file_path, $pdf_content) !== false) {
                $save_success = true;
                echo "file saved by method 2";
            }
        } catch (Exception $e2) {
            error_log("Failed to save PDF: " . $e2->getMessage());
        }
    }
    
    if ($save_success && file_exists($file_path)) {
        // Redirect to PDF viewer script instead of directly to PDF
        $viewer_url = 'view_pdf.php?file=' . urlencode($filename);
        header('Location: ' . $viewer_url);
        exit;
    } else {
        throw new Exception('Failed to save PDF file');
    }
    
} catch (Exception $e) {
    // Display error page
    displayError($e->getMessage());
}

/**
 * Log PDF generation to database
 */
function logPDFGeneration($catalog_number, $lot_number, $template_code) {
    try {
        $conn = getDBConnection();
        
        // Check if table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'pdf_generation_log'");
        
        if ($table_check && $table_check->num_rows > 0) {
            // Get table structure to check column names
            $columns_result = $conn->query("SHOW COLUMNS FROM pdf_generation_log");
            $columns = [];
            while ($col = $columns_result->fetch_assoc()) {
                $columns[] = $col['Field'];
            }
            
            // Check if using camelCase or snake_case
            if (in_array('catalogNumber', $columns)) {
                // CamelCase columns
                $log_sql = "INSERT INTO pdf_generation_log 
                           (catalogNumber, lotNumber, templateCode, generatedAt) 
                           VALUES (?, ?, ?, NOW())";
            } else {
                // Snake_case columns (fallback)
                $log_sql = "INSERT INTO pdf_generation_log 
                           (catalog_number, lot_number, template_code, generated_at) 
                           VALUES (?, ?, ?, NOW())";
            }
            
            $log_stmt = $conn->prepare($log_sql);
            if ($log_stmt) {
                $log_stmt->bind_param("sss", $catalog_number, $lot_number, $template_code);
                
                if ($log_stmt->execute()) {
                    error_log("PDF generation logged successfully to database");
                } else {
                    error_log("Failed to log PDF generation: " . $log_stmt->error);
                }
                
                $log_stmt->close();
            } else {
                error_log("Failed to prepare log statement: " . $conn->error);
            }
        } else {
            error_log("pdf_generation_log table does not exist");
        }
        
        $conn->close();
        
    } catch (Exception $e) {
        error_log("Exception in logPDFGeneration: " . $e->getMessage());
    }
}
?>