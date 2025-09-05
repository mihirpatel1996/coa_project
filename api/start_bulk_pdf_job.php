<?php 
// start-job.php
session_write_close();

$jobId = bin2hex(random_bytes(8));

$saveDir   = __DIR__ . "/pdf_out/{$jobId}";
if (!is_dir($saveDir)) mkdir($saveDir, 0777, true);

//$stateFile = sys_get_temp_dir() . "/pdf_job_{$jobId}.json";
$stateFile = $saveDir . "/pdf_job_{$jobId}.json";

file_put_contents($stateFile, json_encode([
  'jobId' => $jobId, 'total' => 1000, 'current' => 0, 'errors' => [], 'status' => 'queued'
], JSON_PRETTY_PRINT), LOCK_EX);

try {
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $workerScript = escapeshellarg(__DIR__ . '/worker.php');
      $jobIdArg = escapeshellarg($jobId);
      $phpExecutable = escapeshellarg("c:/wamp64/bin/php/php8.1.31/php.exe");
      $logFile = $saveDir . "/worker_{$jobId}.log";
      // On Windows, use 'start /B' to run in the background.
      // We use pclose(popen(...)) as a reliable way to start a background process without waiting for it.
      $cmd = "start /B \"\" {$phpExecutable} {$workerScript} {$jobIdArg}";
      pclose(popen($cmd, 'r'));

  }
  else {
	  $phpExecutable = escapeshellarg('/usr/bin/php8.2');
      $workerScript = escapeshellarg(__DIR__ . '/worker.php');
	  $jobIdArg = escapeshellarg($jobId);
      $logFile = $saveDir . "/worker_{$jobId}.log";

      // Simple background execution for Unix-like systems
      // Using 'nohup' to ignore hangup signals and '&' to run in background
	  $cmd = "nohup {$phpExecutable} {$workerScript} {$jobIdArg} > {$logFile} 2>&1 &";
	  exec($cmd);
  }
}
catch(Exception $e) {
  $error = "Error running worker: ".$e->getMessage();
  http_response_code(500);
  echo json_encode(['jobId' => $jobId, 'success' =>false, 'status' => 'error', 'error' => $error]);
  exit();
}

header('Content-Type: application/json');
http_response_code(200);
echo json_encode(['jobId' => $jobId, 'status' => 'running', 'success'=> true, 'error' => null]);
exit();
?>