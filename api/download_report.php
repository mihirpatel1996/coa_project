<?php
// api/download_report.php
// Download upload reports (updated records and complete reports)

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
    $filepath = '../api/upload_reports/' . $filename;
    
    if (!file_exists($filepath)) {
        // Try legacy reports directory for backward compatibility
        $legacyPath = '../reports/' . $filename;
        if (file_exists($legacyPath)) {
            $filepath = $legacyPath;
        } else {
            throw new Exception('File not found');
        }
    }
    
    // Validate file is a valid report file (updated, complete, or legacy skipped)
    if (!preg_match('/^(updated_|complete_|skipped_|upload_report_).*\.(xlsx|csv)$/i', $filename)) {
        throw new Exception('Invalid file type');
    }
    
    // Determine content type based on file extension
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if ($extension === 'xlsx') {
        $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    } elseif ($extension === 'xls') {
        $contentType = 'application/vnd.ms-excel';
    } else {
        $contentType = 'text/csv';
    }
    
    // Set headers for download
    header('Content-Type: ' . $contentType);
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