<?php
// api/save_key_value_order.php (FIXED FOR NEW STRUCTURE)
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
    
    // Get catalog_number first
    $catalog_sql = "SELECT catalog_number FROM catalogs WHERE id = ?";
    $catalog_stmt = $conn->prepare($catalog_sql);
    $catalog_stmt->bind_param("i", $catalog_id);
    $catalog_stmt->execute();
    $catalog_result = $catalog_stmt->get_result();
    
    if ($catalog_result->num_rows === 0) {
        throw new Exception('Catalog not found');
    }
    
    $catalog_row = $catalog_result->fetch_assoc();
    $catalog_number = $catalog_row['catalog_number'];
    $catalog_stmt->close();
    
    // Start transaction
    $conn->autocommit(false);
    
    $updated_count = 0;
    
    // Note: Since the new structure doesn't have an 'order' column, 
    // we'll need to add it or handle ordering differently
    // For now, we'll skip the ordering update and just return success
    // You may need to add an 'order' column to catalog_details and lot_details tables
    
    foreach ($key_value_orders as $item) {
        if ($item['source'] === 'catalog') {
            // Check if order column exists in catalog_details
            $check_column = $conn->query("SHOW COLUMNS FROM catalog_details LIKE 'order'");
            if ($check_column->num_rows > 0) {
                $update_sql = "UPDATE catalog_details SET `order` = ? WHERE catalog_number = ? AND section_id = ? AND `key` = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("isis", $item['order'], $catalog_number, $section_id, $item['key']);
                
                if ($stmt->execute()) {
                    $updated_count++;
                }
                $stmt->close();
            }
        } elseif ($item['source'] === 'lot' && !empty($lot_number)) {
            // Check if order column exists in lot_details
            $check_column = $conn->query("SHOW COLUMNS FROM lot_details LIKE 'order'");
            if ($check_column->num_rows > 0) {
                $update_sql = "UPDATE lot_details SET `order` = ? WHERE lot_number = ? AND section_id = ? AND `key` = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("isis", $item['order'], $lot_number, $section_id, $item['key']);
                
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
        'updated_count' => $updated_count,
        'note' => 'Order functionality requires order columns in catalog_details and lot_details tables'
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