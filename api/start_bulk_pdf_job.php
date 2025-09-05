<?php 
// start_bulk_pdf_job.php
require_once '../config/database.php';
session_write_close();

try {
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      // $workerScript = escapeshellarg(__DIR__ . '/worker.php');
      $workerScript = escapeshellarg(__DIR__ . '/cli_start_pdf_generate_external.php');
      $phpExecutable = escapeshellarg("c:/wamp64/bin/php/php8.1.31/php.exe");

      // On Windows, use 'start /B' to run in the background.
      // We use pclose(popen(...)) as a reliable way to start a background process without waiting for it.
      $cmd = "start /B \"\" {$phpExecutable} {$workerScript}";
      pclose(popen($cmd, 'r'));

  }
  else {
	  $phpExecutable = escapeshellarg('/usr/bin/php8.2');
      // $workerScript = escapeshellarg(__DIR__ . '/worker.php');
      $workerScript = escapeshellarg(__DIR__ . '/cli_start_pdf_generate_external.php');
      $logFile = '/dev/null';

      // Simple background execution for Unix-like systems
      // Using 'nohup' to ignore hangup signals and '&' to run in background
      $cmd = "nohup {$phpExecutable} {$workerScript} > {$logFile} 2>&1 &";
	  exec($cmd);
  }
}
catch(Exception $e) {
  $error = "Error running worker: ".$e->getMessage();
  http_response_code(500);
  echo json_encode([ 'success' =>false, 'status' => 'error', 'error' => $error]);
  exit();
}

// Estimate how long the job will take (based on 30 seconds per PDF)
$conn = getDBConnection();
$get_lots_sql = "SELECT count(lotNumber) AS lotCount FROM lots WHERE generatePDF = 1";
$lots_stmt = $conn->prepare($get_lots_sql);
$lots_stmt->execute();

$lots_result = $lots_stmt->get_result();
$row = $lots_result->fetch_assoc();
$lotCount = $row['lotCount'] ?? 0;
$lots_stmt->close();
closeDBConnection($conn);

$estimatedSeconds = $lotCount * 20; // 20 seconds per PDF
$estimatedMinutes = ceil($estimatedSeconds / 60);
$estimatedTime = "{$estimatedMinutes} minutes";


header('Content-Type: application/json');
http_response_code(200);
echo json_encode([ 'status' => 'running', 'success'=> true, 'error' => null, 'estimated_time' => $estimatedTime,]);
exit();
?>