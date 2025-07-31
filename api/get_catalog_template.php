<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

try {
    $catalogNumber = isset($_GET['catalog_number']) ? trim($_GET['catalog_number']) : '';
    
    if (empty($catalogNumber)) {
        throw new Exception('Catalog number is required');
    }
    
    $conn = getDBConnection();
    
    // Check if catalog exists and has data
    $sql = "SELECT c.templateCode, 
            (SELECT COUNT(*) FROM lots WHERE catalogNumber = c.catalogNumber) as lot_count
            FROM catalogs c 
            WHERE c.catalogNumber = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $catalogNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Check if catalog has any non-null field data
        $data_check_sql = "SELECT 
            (source IS NOT NULL OR predictedMolMass IS NOT NULL OR 
             activity IS NOT NULL OR formulation IS NOT NULL OR 
             shipping IS NOT NULL OR stability IS NOT NULL) as has_data
            FROM catalogs WHERE catalogNumber = ?";
        $check_stmt = $conn->prepare($data_check_sql);
        $check_stmt->bind_param("s", $catalogNumber);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $has_data = false;
        
        if ($check_result->num_rows > 0) {
            $check_row = $check_result->fetch_assoc();
            $has_data = (bool)$check_row['has_data'];
        }
        $check_stmt->close();
        
        echo json_encode([
            'success' => true,
            'template_code' => $row['templateCode'],
            'has_data' => $has_data,
            'lot_count' => (int)$row['lot_count']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Catalog not found'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>