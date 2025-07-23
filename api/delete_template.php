<?php
// api/delete_template.php
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
    
    // Check if template exists and if it's being used
    $check_sql = "SELECT t.*, COUNT(c.id) as catalog_count 
                  FROM templates t 
                  LEFT JOIN catalogs c ON t.id = c.template_id 
                  WHERE t.id = ? 
                  GROUP BY t.id";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $template_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        throw new Exception('Template not found');
    }
    
    $template_info = $check_result->fetch_assoc();
    $check_stmt->close();
    
    // Check if template is being used by catalogs
    if ($template_info['catalog_count'] > 0) {
        throw new Exception('Cannot delete template: it is being used by ' . $template_info['catalog_count'] . ' catalog(s)');
    }
    
    // Check if it's the only default template
    if ($template_info['is_default']) {
        $default_count_sql = "SELECT COUNT(*) as count FROM templates WHERE is_default = 1";
        $default_count_result = $conn->query($default_count_sql);
        $default_count = $default_count_result->fetch_assoc()['count'];
        
        if ($default_count <= 1) {
            throw new Exception('Cannot delete the only default template');
        }
    }
    
    // Start transaction
    $conn->autocommit(false);
    
    // Delete template keys first (foreign key constraint)
    $delete_keys_sql = "DELETE FROM template_keys WHERE template_id = ?";
    $delete_keys_stmt = $conn->prepare($delete_keys_sql);
    $delete_keys_stmt->bind_param("i", $template_id);
    $delete_keys_stmt->execute();
    $delete_keys_stmt->close();
    
    // Delete template
    $delete_template_sql = "DELETE FROM templates WHERE id = ?";
    $delete_template_stmt = $conn->prepare($delete_template_sql);
    $delete_template_stmt->bind_param("i", $template_id);
    
    if ($delete_template_stmt->execute()) {
        // Commit transaction
        $conn->commit();
        $conn->autocommit(true);
        
        echo json_encode([
            'success' => true,
            'message' => 'Template deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete template');
    }
    
    $delete_template_stmt->close();
    
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