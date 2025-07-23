<?php
// api/set_default_template.php
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
    $template_id = isset($input['template_id']) ? intval($input['template_id']) : 0;
    
    if ($template_id <= 0) {
        throw new Exception('Valid template ID is required');
    }
    
    // Get database connection
    $conn = getDBConnection();
    
    // Check if template exists
    $check_sql = "SELECT id FROM templates WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $template_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        throw new Exception('Template not found');
    }
    $check_stmt->close();
    
    // Start transaction
    $conn->autocommit(false);
    
    // Remove default from all templates
    $remove_default_sql = "UPDATE templates SET is_default = 0";
    $conn->query($remove_default_sql);
    
    // Set new default
    $set_default_sql = "UPDATE templates SET is_default = 1 WHERE id = ?";
    $set_default_stmt = $conn->prepare($set_default_sql);
    $set_default_stmt->bind_param("i", $template_id);
    
    if ($set_default_stmt->execute()) {
        // Commit transaction
        $conn->commit();
        $conn->autocommit(true);
        
        echo json_encode([
            'success' => true,
            'message' => 'Default template updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update default template');
    }
    
    $set_default_stmt->close();
    
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