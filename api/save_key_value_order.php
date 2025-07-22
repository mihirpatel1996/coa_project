<?php
// api/save_key_value_order.php
//header('Content<?php
// api/save_key_value_order.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../config/database.php';

try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate inputs
    $catalog_id = isset($input['catalog_id']) ? intval($input['catalog_id']) : 0;
    $section_id = isset($input['section_id']) ? intval($input['section_id']) : 0;
    $lot_number = isset($input['lot_number']) ? trim($input['lot_number']) : '';
    $key_value_orders = isset($input['key_value_orders']) ? $input['key_value_orders'] : [];
    
    if ($catalog_id <= 0) {
        throw new Exception('Valid catalog ID is required');
    }
    
    if ($section_id <= 0) {
        throw new Exception('Valid section ID is required');
    }
    
    if (empty($key_value_orders)) {
        throw new Exception('Key-value orders are required');
    }
    
    // Get database connection
    $conn = getDBConnection();
    
    // Get lot_id if lot_number is provided
    $lot_id = null;
    if (!empty($lot_number)) {
        $lot_sql = "SELECT id FROM lots WHERE catalog_id = ? AND lot_number = ?";
        $lot_stmt = $conn->prepare($lot_sql);
        $lot_stmt->bind_param("is", $catalog_id, $lot_number);
        $lot_stmt->execute();
        $lot_result = $lot_stmt->get_result();
        
        if ($lot_result->num_rows > 0) {
            $lot_row = $lot_result->fetch_assoc();
            $lot_id = $lot_row['id'];
        }
        $lot_stmt->close();
    }
    
    // Start transaction
    $conn->autocommit(false);
    
    $updated_count = 0;
    
    // Update catalog details orders
    foreach ($key_value_orders as $item) {
        if ($item['source'] === 'catalog') {
            $update_sql = "UPDATE catalog_details SET `order` = ? WHERE catalog_id = ? AND section_id = ? AND `key` = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("iiis", $item['order'], $catalog_id, $section_id, $item['key']);
            
            if ($stmt->execute()) {
                $updated_count++;
            }
            $stmt->close();
        }
    }
    
    // Update lot details orders if lot_id is available
    if ($lot_id !== null) {
        foreach ($key_value_orders as $item) {
            if ($item['source'] === 'lot') {
                $update_sql = "UPDATE lot_details SET `order` = ? WHERE lot_id = ? AND section_id = ? AND `key` = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("iiis", $item['order'], $lot_id, $section_id, $item['key']);
                
                if ($stmt->execute()) {
                    $updated_count++;
                }
                $stmt->close();
            }
        }
    }
    
    // Commit transaction
    $conn->commit();
    $conn->autocommit(true);
    
    echo json_encode([
        'success' => true,
        'message' => 'Order updated successfully',
        'updated_count' => $updated_count
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn)) {
        $conn->rollback();
        $conn->autocommit(true);
    }
    
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    // Close connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>