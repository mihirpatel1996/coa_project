<?php
// this file is not needed anymore
// it was used for testing the generatePDFExternal function
require_once '../config/database.php';
include './generate_pdf_external.php';

$start_time = microtime(true);

$lot_numbers = [];
$result_set = [];

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
    $result = json_decode($result, true);
    $result_set[] = $result;
}

echo json_encode($result_set);
?>