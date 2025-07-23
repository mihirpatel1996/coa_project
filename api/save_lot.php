<?php
// api/save_lot.php (UPDATED FOR TEMPLATE SUPPORT)
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
    $lot_number = isset($input['lot_number']) ? trim($input['lot_number']) : '';
    
    if ($catalog_id <= 0) {
        throw new Exception('Valid catalog ID is required');
    }
    
    if (empty($lot_number)) {
        throw new Exception('Lot number is required');
    }
    
    // Get database connection
    $conn = getDBConnection();
    
    // Get catalog information including template_id
    $catalog_check_sql = "SELECT id, template_id FROM catalogs WHERE id = ?";
    $catalog_stmt = $conn->prepare($catalog_check_sql);
    $catalog_stmt->bind_param("i", $catalog_id);
    $catalog_stmt->execute();
    $catalog_result = $catalog_stmt->get_result();
    
    if ($catalog_result->num_rows === 0) {
        throw new Exception('Catalog does not exist');
    }
    
    $catalog_info = $catalog_result->fetch_assoc();
    $template_id = $catalog_info['template_id'];
    $catalog_stmt->close();
    
    if (!$template_id) {
        throw new Exception('Catalog does not have a template assigned');
    }
    
    // Check if lot already exists
    $lot_check_sql = "SELECT id FROM lots WHERE catalog_id = ? AND lot_number = ?";
    $lot_stmt = $conn->prepare($lot_check_sql);
    $lot_stmt->bind_param("is", $catalog_id, $lot_number);
    $lot_stmt->execute();
    $lot_result = $lot_stmt->get_result();
    
    if ($lot_result->num_rows > 0) {
        // Lot already exists
        $existing_lot = $lot_result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'lot_id' => $existing_lot['id'],
            'message' => 'Lot already exists and is ready for use',
            'action' => 'existing'
        ]);
    } else {
        // Create new lot with template_id from catalog
        $insert_sql = "INSERT INTO lots (catalog_id, lot_number, template_id) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("isi", $catalog_id, $lot_number, $template_id);
        
        if ($insert_stmt->execute()) {
            $lot_id = $conn->insert_id;
            echo json_encode([
                'success' => true,
                'lot_id' => $lot_id,
                'message' => 'Lot created successfully',
                'action' => 'created'
            ]);
        } else {
            throw new Exception('Failed to create lot record: ' . $insert_stmt->error);
        }
        
        $insert_stmt->close();
    }
    $lot_stmt->close();
    
} catch (Exception $e) {
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug_info' => [
            'catalog_id' => isset($catalog_id) ? $catalog_id : 'not set',
            'lot_number' => isset($lot_number) ? $lot_number : 'not set',
            'template_id' => isset($template_id) ? $template_id : 'not retrieved'
        ]
    ]);
} finally {
    // Close connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>