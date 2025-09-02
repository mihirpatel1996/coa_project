<?php
require_once '../config/database.php';
include './generate_pdf_external.php';

$start_time = microtime(true);

// $lot_numbers = ['SCF19MA0631', 'L2111-7'];
$lot_numbers = [];
$result_set = [];

//fetch lots from lot table where generate_lot flag is set to 1
// $conn = getDBConnection();
// $stmt = $conn->prepare("SELECT lotNumber FROM lots WHERE generate_lot = 1");
// $stmt->execute();

// $result = $stmt->get_result();
// while ($row = $result->fetch_assoc()) {
//     $lot_numbers[] = $row['lotNumber'];
// }
// $stmt->close();
// $conn->close();

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

foreach($lot_numbers as $lot_number){
    $result = generatePDFExternal($lot_number);
    $result_set[] = $result;
    echo $result;
    echo "<br><br>";
}


$end_time = microtime(true);
$time_difference = $end_time - $start_time;
// echo "Time difference: ".$time_difference;

return json_encode($result_set);

?>