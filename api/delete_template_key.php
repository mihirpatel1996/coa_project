<?php
// api/delete_template_key.php
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
    $key_id = isset($input['key_id']) ? intval($input['key_id']) : 0;
    
    if ($key_id <= 0) {
        throw new Exception('Valid key ID is required');
    }
    
    // Get database connection
    $conn = getDBConnection();
    
    // Check if template key exists
    $check_sql = "SELECT template_id, section_id, key_order FROM template_keys WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $key_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        throw new Exception('Template key not found');
    }
    
    $key_info = $check_result->fetch_assoc();
    $template_id = $key_info['template_id'];
    $section_id = $key_info['section_id'];
    $deleted_order = $key_info['key_order'];
    $check_stmt->close();
    
    // Start transaction
    $conn->autocommit(false);
    
    // Delete the template key
    $delete_sql = "DELETE FROM template_keys WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $key_id);
    
    if ($delete_stmt->execute()) {
        // Reorder remaining keys in the same template/section
        $reorder_sql = "UPDATE template_keys 
                        SET key_order = key_order - 1 
                        WHERE template_id = ? AND section_id = ? AND key_order > ?";
        $reorder_stmt = $conn->prepare($reorder_sql);
        $reorder_stmt->bind_param("iii", $template_id, $section_id, $deleted_order);
        $reorder_stmt->execute();
        $reorder_stmt->close();
        
        // Commit transaction
        $conn->commit();
        $conn->autocommit(true);
        
        echo json_encode([
            'success' => true,
            'message' => 'Template key deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete template key');
    }
    
    $delete_stmt->close();
    
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