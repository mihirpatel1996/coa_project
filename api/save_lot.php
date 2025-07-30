<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/templates_config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    // Changed from catalog_id to catalog_number
    $catalogNumber = isset($input['catalog_number']) ? trim($input['catalog_number']) : '';
    $lotNumber = isset($input['lot_number']) ? trim($input['lot_number']) : '';
    
    if (empty($catalogNumber)) {
        throw new Exception('Catalog number is required');
    }
    
    if (empty($lotNumber)) {
        throw new Exception('Lot number is required');
    }
    
    $conn = getDBConnection();
    
    // Get catalog templateCode
    $catalog_sql = "SELECT templateCode FROM catalogs WHERE catalogNumber = ?";
    $catalog_stmt = $conn->prepare($catalog_sql);
    $catalog_stmt->bind_param("s", $catalogNumber);
    $catalog_stmt->execute();
    $catalog_result = $catalog_stmt->get_result();
    
    if ($catalog_result->num_rows === 0) {
        throw new Exception('Catalog does not exist');
    }
    
    $catalog = $catalog_result->fetch_assoc();
    $templateCode = $catalog['templateCode'];
    $catalog_stmt->close();
    
    // Check if lot already exists
    $check_sql = "SELECT id FROM lots WHERE catalogNumber = ? AND lotNumber = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $catalogNumber, $lotNumber);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $existing = $check_result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'lot_id' => $existing['id'],
            'message' => 'Lot already exists',
            'action' => 'existing'
        ]);
    } else {
        // Create new lot
        $insert_sql = "INSERT INTO lots (catalogNumber, lotNumber, templateCode) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("sss", $catalogNumber, $lotNumber, $templateCode);
        
        if ($insert_stmt->execute()) {
            echo json_encode([
                'success' => true,
                'lot_id' => $conn->insert_id,
                'message' => 'Lot created successfully',
                'action' => 'created'
            ]);
        } else {
            throw new Exception('Failed to create lot');
        }
        $insert_stmt->close();
    }
    
    $check_stmt->close();
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>