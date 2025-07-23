<?php
// api/create_template.php (SIMPLIFIED VERSION)
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
    $template_name = isset($input['template_name']) ? trim($input['template_name']) : '';
    $description = isset($input['description']) ? trim($input['description']) : '';
    $is_default = isset($input['is_default']) ? (bool)$input['is_default'] : false;
    
    if (empty($template_name)) {
        throw new Exception('Template name is required');
    }
    
    // Get database connection
    $conn = getDBConnection();
    
    // Check if template name already exists
    $check_sql = "SELECT id FROM templates WHERE template_name = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $template_name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        throw new Exception('Template name already exists');
    }
    $check_stmt->close();
    
    // Start transaction
    $conn->autocommit(false);
    
    // If this is set as default, remove default from other templates
    if ($is_default) {
        $remove_default_sql = "UPDATE templates SET is_default = 0";
        $conn->query($remove_default_sql);
    }
    
    // Create new template (NO DEFAULT KEYS - starts empty)
    $insert_sql = "INSERT INTO templates (template_name, description, is_default) VALUES (?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ssi", $template_name, $description, $is_default);
    
    if ($insert_stmt->execute()) {
        $template_id = $conn->insert_id;
        
        // Commit transaction
        $conn->commit();
        $conn->autocommit(true);
        
        echo json_encode([
            'success' => true,
            'template_id' => $template_id,
            'message' => 'Template created successfully. Use Configure to add keys.'
        ]);
        
        $insert_stmt->close();
        
    } else {
        throw new Exception('Failed to create template');
    }
    
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