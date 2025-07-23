<?php
// api/update_catalog_name.php
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
    $catalog_name = isset($input['catalog_name']) ? trim($input['catalog_name']) : '';
    
    if ($catalog_id <= 0) {
        throw new Exception('Valid catalog ID is required');
    }
    
    if (empty($catalog_name)) {
        throw new Exception('Catalog name is required');
    }
    
    // Get database connection
    $conn = getDBConnection();
    
    // Update catalog name
    $update_sql = "UPDATE catalogs SET catalog_name = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $catalog_name, $catalog_id);
    
    if ($update_stmt->execute()) {
        if ($update_stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Catalog name updated successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No catalog found or name unchanged'
            ]);
        }
    } else {
        throw new Exception('Failed to update catalog name');
    }
    
    $update_stmt->close();
    
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