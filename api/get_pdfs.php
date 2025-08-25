<?php
header('Content-Type: application/json');
// Include database connection
require_once '../vendor/autoload.php';
require_once '../config/database.php';


// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

$fromDate = $input['fromDate'] ?? '';
$toDate = $input['toDate'] ?? '';
$searchQuery = $input['searchQuery'] ?? '';
$queryType = $input['searchType'] ?? '';

//get all PDFs from the database

// Get database connection
$conn = getDBConnection();

if($queryType == 'date'){
    $fromDateTimestamp = $fromDate . ' 00:00:00';
    $toDateTimestamp = $toDate . ' 23:59:59';

    try{
        $get_pdf_log_sql = "SELECT * from pdf_generation_log WHERE generatedAt BETWEEN ? AND ?";
        $pdf_log_stmt = $conn->prepare($get_pdf_log_sql);
        $pdf_log_stmt->bind_param("ss", $fromDateTimestamp, $toDateTimestamp);
        $pdf_log_stmt->execute();
        $result = $pdf_log_stmt->get_result();
        $all_pdfs = $result->fetch_all(MYSQLI_ASSOC);
        $pdf_log_stmt->close();
        $conn->close();

        http_response_code(200);
        echo json_encode(['success' => true, 'pdfs' => $all_pdfs]);

    }catch(Exception $e){
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        $conn->close();
        exit();
    }
}
if($queryType == 'query'){
    $searchTerms = array_map('trim', explode(',', $searchQuery));
    $searchTerms = array_filter($searchTerms); // Remove empty strings

    if (empty($searchTerms)) {
        echo json_encode(['success' => true, 'pdfs' => []]);
        exit();
    }

    $sql_parts = [];
    $params = [];
    $types = '';

    foreach ($searchTerms as $term) {
        $sql_parts[] = "(lotNumber LIKE ? OR catalogNumber LIKE ?)";
        $params[] = '%' . $term . '%';
        $params[] = '%' . $term . '%';
        $types .= 'ss';
    }

    $get_pdf_log_sql = "SELECT * from pdf_generation_log WHERE " . implode(' OR ', $sql_parts);

    try{
        $pdf_log_stmt = $conn->prepare($get_pdf_log_sql);
        $pdf_log_stmt->bind_param($types, ...$params);
        $pdf_log_stmt->execute();
        $result = $pdf_log_stmt->get_result();
        $all_pdfs = $result->fetch_all(MYSQLI_ASSOC);
        $pdf_log_stmt->close();
        $conn->close();
        
        http_response_code(200);
        echo json_encode(['success' => true, 'pdfs' => $all_pdfs]);
    }
    catch(Exception $e){
        http_response_code(500);
        echo json_encode(['success' => false, "message" => 'Database error: '.$e->getMessage()]);
        $conn->close();
        exit();
    } 
}    
?>