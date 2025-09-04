<?php
// progress.php?jobId=xxxx
$jobId = $_GET['jobId'] ?? '';
$stateFile = __DIR__ . "/pdf_out/{$jobId}/pdf_job_{$jobId}.json";

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');

@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', 0);
while (ob_get_level() > 0) { ob_end_flush(); }
ob_implicit_flush(1);

session_write_close();
ignore_user_abort(true);
set_time_limit(0);

function sse($event, $data) {
  echo "event: $event\n";
  echo "data: " . json_encode($data) . "\n\n";
  @flush();
}

if (!is_file($stateFile)) {
  sse('error', ['message' => 'Job not found']); exit;
}

$lastSent = null;
while (true) {
  clearstatcache(true, $stateFile);
  if (is_file($stateFile)) {
    $json = file_get_contents($stateFile);
    if ($json !== $lastSent) {
      $state = json_decode($json, true);
      sse('progress', $state);
      $lastSent = $json;
      if (($state['status'] ?? '') === 'done') break;
    }
  } else {
    sse('error', ['message' => 'Job disappeared']);
    break;
  }
  usleep(500000); // 0.5s
}
