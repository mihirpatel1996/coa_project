<?php
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed');
}

$input = json_decode(file_get_contents('php://input'), true);
$filenames = $input['filenames'] ?? [];

if (empty($filenames)) {
    http_response_code(400);
    die('No files specified');
}

// Create zip file
$zip = new ZipArchive();
$zipName = 'pdfs_' . date('Y-m-d_His') . '.zip';
$zipPath = sys_get_temp_dir() . '/' . $zipName;

if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
    http_response_code(500);
    die('Cannot create zip file');
}

// Add PDFs to zip
$pdfDir = __DIR__ . '../generated_pdfs/';
foreach ($filenames as $filename) {
    $filePath = $pdfDir . $filename;
    if (file_exists($filePath)) {
        $zip->addFile($filePath, $filename);
    }
}

$zip->close();

// Send zip file
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipName . '"');
header('Content-Length: ' . filesize($zipPath));

readfile($zipPath);
unlink($zipPath); // Clean up temp file
?>