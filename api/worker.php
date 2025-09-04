<?php
// worker.php (CLI): php worker.php <jobId>
require_once '../config/database.php';
include './generate_pdf_external.php';

$jobId = $argv[1] ?? null;
if (!$jobId) { fwrite(STDERR, "Missing jobId\n"); exit(1); }

// $stateFile = sys_get_temp_dir() . "/pdf_job_{$jobId}.json";
$saveDir   = __DIR__ . "/pdf_out/{$jobId}";

// if (!is_dir($saveDir)) mkdir($saveDir, 0777, true);

$stateFile = $saveDir . "/pdf_job_{$jobId}.json";

function writeState($file, $state) {
  file_put_contents($file, json_encode($state), LOCK_EX);
}

// Get PDF lot numbers for generating PDFs
$conn = getDBConnection();
$get_lots_sql = "SELECT lotNumber FROM lots WHERE generatePDF = 1";
$lots_stmt = $conn->prepare($get_lots_sql);
$lots_stmt->execute();
//fetch all lot numbers into an array
    $lots_result = $lots_stmt->get_result();
    $lot_numbers = [];
    //fetch all lot numbers into an array
    while($lot_number = $lots_result->fetch_assoc()){
        $lot_numbers[] = $lot_number;
    }
$lots_stmt->close();
closeDBConnection($conn);

$state = json_decode(file_get_contents($stateFile), true);

$state['status'] = 'running';
$state['total'] = count($lot_numbers); // total count
$state['started_at'] = date("Y-m-d H:i:s");
$state['current'] = 0;
$state['percent'] = 0.0;
$state['errors'] = [];
$state['finished_at'] = null;
writeState($stateFile, $state);

// Start generating PDFs
foreach($lot_numbers as $lot_number){
    // echo "Generating PDF for lot number: {$lot_number['lotNumber']}\n";
    $result = generatePDFExternal($lot_number['lotNumber']);
    $result = json_decode($result, true);
    if($result['success']){
        $state['current']++;
        $state['percent'] = round($state['current'] / $state['total'] * 100, 2);
        writeState($stateFile, $state);
    }
    else{
        $state['errors'][] = ['lot_number' => $lot_number['lotNumber'], 'msg' => $result['message']];
        $state['current']++;
        $state['percent'] = round($state['current'] / $state['total'] * 100, 2);
        writeState($stateFile, $state);
    }
}

// The script will continue to run even if the client (browser) disconnects.
ignore_user_abort(true);
// The script will not time out.
set_time_limit(0);


// $total = $state['total'];
// for ($i = 1; $i <= $total; $i++) {
//     try {
//         // $pdf = createPdfFor($i);
//         // file_put_contents("$saveDir/doc_$i.pdf", $pdf);
//         usleep(40000);

//         $state['current'] = $i;
//         $state['percent'] = round($i / $total * 100, 2);
//         writeState($stateFile, $state);
//     } catch (Throwable $e) {
//         $state['errors'][] = ['index' => $i, 'msg' => $e->getMessage()];
//         $state['current']  = $i;
//         $state['percent']  = round($i / $total * 100, 2);
//         writeState($stateFile, $state);
//     }

//     break;
// }

$state['status']  = 'done';
$state['finished_at'] = date("Y-m-d H:i:s");
writeState($stateFile, $state);

//close cli script
exit(0);
