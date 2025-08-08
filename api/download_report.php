<?php
// api/download_report.php
// Download skipped records report

// Prevent any output before headers
ob_clean();

require_once '../config/database.php';

try {
    // Get filename from query parameter
    $filename = isset($_GET['file']) ? $_GET['file'] : '';
    
    if (empty($filename)) {
        throw new Exception('No file specified');
    }
    
    // Sanitize filename to prevent directory traversal
    $filename = basename($filename);
    
    // Check if file exists
    $filepath = '../reports/' . $filename;
    
    if (!file_exists($filepath)) {
        throw new Exception('File not found');
    }
    
    // Validate file is an Excel file
    if (!preg_match('/^skipped_.*\.xlsx$/i', $filename)) {
        throw new Exception('Invalid file type');
    }
    
    // Set headers for Excel download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($filepath));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output file
    readfile($filepath);
    
} catch (Exception $e) {
    http_response_code(404);
    header('Content-Type: text/plain');
    echo 'Error: ' . $e->getMessage();
}
?>