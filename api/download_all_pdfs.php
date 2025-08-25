<?php
// api/download_all_pdfs.php
require_once '../vendor/autoload.php';

use ZipStream\ZipStream;
use ZipStream\Option\Archive;

header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Method not allowed']));
}

$input = json_decode(file_get_contents('php://input'), true);
$filenames = $input['filenames'] ?? [];

if (empty($filenames)) {
    http_response_code(400);
    die(json_encode(['error' => 'No files specified']));
}

// Clear any output buffers
while (ob_get_level()) {
    ob_end_clean();
}

// Create zip filename
$zipName = 'CoA_PDFs_' . date('Y-m-d_His') . '.zip';

// Create ZipStream instance
$options = new Archive();
$options->setSendHttpHeaders(true);

$zip = new ZipStream($zipName, $options);

$pdfDir = dirname(__DIR__) . '/../generated_pdfs/';
$filesAdded = 0;

foreach ($filenames as $filename) {
    $filename = basename($filename);
    $filePath = $pdfDir . $filename;
    
    if (file_exists($filePath) && is_readable($filePath)) {
        $zip->addFileFromPath($filename, $filePath);
        $filesAdded++;
    }
}

if ($filesAdded === 0) {
    http_response_code(404);
    die(json_encode(['error' => 'No PDF files found']));
}

// Finish the zip stream
$zip->finish();
?>