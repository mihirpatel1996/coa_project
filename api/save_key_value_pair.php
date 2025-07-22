<?php
// api/save_key_value.php
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
    $key = isset($input['key']) ? trim($input['key']) : '';
    $value = isset($input['value']) ? trim($input['value']) : '';
    $source = isset($input['source']) ? trim($input['source']) : '';
    
    if ($catalog_id <= 0) {
        throw new Exception('Valid catalog ID is required');
    }
    
    if ($section_id <= 0) {
        throw new Exception('Valid section ID is required');
    }
    
    if (empty($key)) {
        throw new Exception('Key is required');
    }
    
    if (empty($value)) {
        throw new Exception('Value is required');
    }
    
    if (!in_array($source, ['catalog', 'lot', 'custom'])) {
        throw new Exception('Valid source is required (catalog, lot, or custom)');
    }
    
    // Get database connection
    $conn = getDBConnection();
    
    if ($source === 'catalog' || $source === 'custom') {
        // Get next order for catalog details
        $order_sql = "SELECT COALESCE(MAX(`order`), 0) + 1 as next_order FROM catalog_details WHERE catalog_id = ? AND section_id = ?";
        $order_stmt = $conn->prepare($order_sql);
        $order_stmt->bind_param("ii", $catalog_id, $section_id);
        $order_stmt->execute();
        $order_result = $order_stmt->get_result();
        $next_order = $order_result->fetch_assoc()['next_order'];
        $order_stmt->close();
        
        // Insert into catalog_details
        $insert_sql = "INSERT INTO catalog_details (catalog_id, section_id, `key`, `value`, `order`) VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iissi", $catalog_id, $section_id, $key, $value, $next_order);
        
        if ($insert_stmt->execute()) {
            $insert_id = $conn->insert_id;
            echo json_encode([
                'success' => true,
                'id' => $insert_id,
                'message' => 'Catalog key-value pair saved successfully',
                'order' => $next_order
            ]);
        } else {
            throw new Exception('Failed to save catalog key-value pair');
        }
        
        $insert_stmt->close();
        
    } elseif ($source === 'lot') {
        if (empty($lot_number)) {
            throw new Exception('Lot number is required for lot data');
        }
        
        // Get lot_id
        $lot_sql = "SELECT id FROM lots WHERE catalog_id = ? AND lot_number = ?";
        $lot_stmt = $conn->prepare($lot_sql);
        $lot_stmt->bind_param("is", $catalog_id, $lot_number);
        $lot_stmt->execute();
        $lot_result = $lot_stmt->get_result();
        
        if ($lot_result->num_rows === 0) {
            throw new Exception('Lot not found');
        }
        
        $lot_row = $lot_result->fetch_assoc();
        $lot_id = $lot_row['id'];
        $lot_stmt->close();
        
        // Get next order for lot details
        $order_sql = "SELECT COALESCE(MAX(`order`), 0) + 1 as next_order FROM lot_details WHERE lot_id = ? AND section_id = ?";
        $order_stmt = $conn->prepare($order_sql);
        $order_stmt->bind_param("ii", $lot_id, $section_id);
        $order_stmt->execute();
        $order_result = $order_stmt->get_result();
        $next_order = $order_result->fetch_assoc()['next_order'];
        $order_stmt->close();
        
        // Insert into lot_details
        $insert_sql = "INSERT INTO lot_details (lot_id, section_id, `key`, `value`, `order`) VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iissi", $lot_id, $section_id, $key, $value, $next_order);
        
        if ($insert_stmt->execute()) {
            $insert_id = $conn->insert_id;
            echo json_encode([
                'success' => true,
                'id' => $insert_id,
                'message' => 'Lot key-value pair saved successfully',
                'order' => $next_order
            ]);
        } else {
            throw new Exception('Failed to save lot key-value pair');
        }
        
        $insert_stmt->close();
    }
    
} catch (Exception $e) {
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