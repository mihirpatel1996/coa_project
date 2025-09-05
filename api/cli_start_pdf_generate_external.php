<?php
// this file is not needed anymore
// it was used for testing the generatePDFExternal function
// require_once '/../config/database.php';
// include './generate_pdf_external.php';
require_once __DIR__ . '/../config/database.php';
include __DIR__ . '/generate_pdf_external.php';


$lot_numbers = [];

if (PHP_SAPI !== 'cli') {
    die('This script can only be run from command line');
}

// The script will continue to run even if the client (browser) disconnects.
ignore_user_abort(true);
// The script will not time out.
set_time_limit(0);

$conn = getDBConnection();
$get_lots_sql = "SELECT * FROM lots WHERE generatePDF = 1";
$lots_stmt = $conn->prepare($get_lots_sql);
$lots_stmt->execute();

$lots_result = $lots_stmt->get_result();
while($row = $lots_result->fetch_assoc()){
    $lot_numbers[] = $row['lotNumber'];
}

$lots_stmt->close();
closeDBConnection($conn);

echo "Starting PDF generation for ".count($lot_numbers)." lots...\n";

foreach($lot_numbers as $lot_number){
    require_once __DIR__ . '/../config/database.php';
    echo "Generating PDF for lot number: {$lot_number}\n";
    $result = generatePDFExternal($lot_number);
    $result = json_decode($result, true);
    if($result['success']){
        echo "PDF generated successfully for lot number: {$lot_number}\n";
    }
    else{
        echo "Error generating PDF for lot number: {$lot_number}. Error: {$result['message']}\n";

    }
    sleep(5);
}

//close cli script
exit(0);

// Close the opened command line window
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    // Windows-specific command to close the command prompt window
    pclose(popen("start /B exit", "r"));
} else {
    // Unix-like systems command to close the terminal window
    exec("exit");
}


?>