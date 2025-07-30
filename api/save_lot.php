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
    
    $catalog_id = isset($input['catalog_id']) ? intval($input['catalog_id']) : 0;
    $lot_number = isset($input['lot_number']) ? trim($input['lot_number']) : '';
    
    if ($catalog_id <= 0) {
        throw new Exception('Valid catalog ID is required');
    }
    
    if (empty($lot_number)) {
        throw new Exception('Lot number is required');
    }
    
    $conn = getDBConnection();
    
    // Get catalog template_code
    $catalog_sql = "SELECT template_code FROM catalogs WHERE id = ?";
    $catalog_stmt = $conn->prepare($catalog_sql);
    $catalog_stmt->bind_param("i", $catalog_id);
    $catalog_stmt->execute();
    $catalog_result = $catalog_stmt->get_result();
    
    if ($catalog_result->num_rows === 0) {
        throw new Exception('Catalog does not exist');
    }
    
    $catalog = $catalog_result->fetch_assoc();
    $template_code = $catalog['template_code'];
    $catalog_stmt->close();
    
    // Check if lot already exists
    $check_sql = "SELECT id FROM lots WHERE catalog_id = ? AND lot_number = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $catalog_id, $lot_number);
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
        $insert_sql = "INSERT INTO lots (catalog_id, lot_number, template_code) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iss", $catalog_id, $lot_number, $template_code);
        
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