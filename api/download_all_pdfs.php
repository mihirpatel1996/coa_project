<?php
// api/download_all_pdfs.php - Alternative using ZipArchive
header('Access-Control-Allow-Origin: *');
require_once '../vendor/autoload.php';

use ZipStream\ZipStream;
use ZipStream\Option\Archive;

// Prevent any output before headers
ob_clean();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Method not allowed']));
}

// Check if ZipArchive is available
if (!class_exists('ZipArchive')) {
    // If not, try to use ZipStream
    require_once '../vendor/autoload.php';
    
    if (!class_exists('ZipStream\ZipStream')) {
        http_response_code(500);
        die(json_encode(['error' => 'No zip library available']));
    }
    
    // Use ZipStream code from above
    useZipStream();
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$filenames = $input['filenames'] ?? [];

if (empty($filenames)) {
    http_response_code(400);
    die(json_encode(['error' => 'No files specified']));
}

// Create temp zip file
$zip = new ZipArchive();
$zipName = 'CoA_PDFs_' . date('Y-m-d_His') . '.zip';
$zipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $zipName;

if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    http_response_code(500);
    die(json_encode(['error' => 'Cannot create zip file']));
}

// Use the correct path
$pdfDir = dirname(__DIR__) . '/generated_pdfs/';
$filesAdded = 0;

foreach ($filenames as $filename) {
    $filename = basename($filename);
    $filePath = $pdfDir . $filename;
    
    if (file_exists($filePath) && is_readable($filePath)) {
        $zip->addFile($filePath, $filename);
        $filesAdded++;
    }
}

$zip->close();

if ($filesAdded === 0) {
    if (file_exists($zipPath)) {
        unlink($zipPath);
    }
    http_response_code(404);
    die(json_encode(['error' => 'No PDF files found']));
}

// Verify zip was created
if (!file_exists($zipPath) || filesize($zipPath) == 0) {
    if (file_exists($zipPath)) {
        unlink($zipPath);
    }
    http_response_code(500);
    die(json_encode(['error' => 'Failed to create zip file']));
}

// Clear output buffers
while (ob_get_level()) {
    ob_end_clean();
}

// Send headers
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipName . '"');
header('Content-Length: ' . filesize($zipPath));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Output file
readfile($zipPath);

// Clean up
unlink($zipPath);
exit;

// ZipStream fallback function
function useZipStream() {
    global $filenames;
    
    // use ZipStream\ZipStream;
    // use ZipStream\Option\Archive;
    
    $zipName = 'CoA_PDFs_' . date('Y-m-d_His') . '.zip';
    $options = new Archive();
    $options->setSendHttpHeaders(true);
    
    $zip = new ZipStream($zipName, $options);
    $pdfDir = dirname(__DIR__) . '/generated_pdfs/';
    
    foreach ($filenames as $filename) {
        $filename = basename($filename);
        $filePath = $pdfDir . $filename;
        
        if (file_exists($filePath) && is_readable($filePath)) {
            $zip->addFileFromPath($filename, $filePath);
        }
    }
    
    $zip->finish();
}
?>