<?php
// api/create_template_with_keys.php
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
    $sections = isset($input['sections']) ? $input['sections'] : [];
    
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
    
    try {
        // If this is set as default, remove default from other templates
        if ($is_default) {
            $remove_default_sql = "UPDATE templates SET is_default = 0";
            $conn->query($remove_default_sql);
        }
        
        // Create new template
        $insert_template_sql = "INSERT INTO templates (template_name, description, is_default) VALUES (?, ?, ?)";
        $insert_template_stmt = $conn->prepare($insert_template_sql);
        $insert_template_stmt->bind_param("ssi", $template_name, $description, $is_default);
        
        if (!$insert_template_stmt->execute()) {
            throw new Exception('Failed to create template');
        }
        
        $template_id = $conn->insert_id;
        $insert_template_stmt->close();
        
        $total_keys = 0;
        
        // Add keys for each section
        if (!empty($sections)) {
            $insert_key_sql = "INSERT INTO template_keys (template_id, section_id, key_name, key_order, key_source) VALUES (?, ?, ?, ?, ?)";
            $insert_key_stmt = $conn->prepare($insert_key_sql);
            
            foreach ($sections as $section) {
                $section_id = intval($section['section_id']);
                
                if (!in_array($section_id, [1, 2, 3])) {
                    throw new Exception("Invalid section ID: $section_id");
                }
                
                if (!empty($section['keys'])) {
                    foreach ($section['keys'] as $key) {
                        $key_name = trim($key['key_name']);
                        $key_source = trim($key['key_source']);
                        $key_order = intval($key['key_order']);
                        
                        if (empty($key_name)) {
                            throw new Exception('Key name cannot be empty');
                        }
                        
                        if (!in_array($key_source, ['catalog', 'lot'])) {
                            throw new Exception("Invalid key source: $key_source");
                        }
                        
                        $insert_key_stmt->bind_param("iisis", $template_id, $section_id, $key_name, $key_order, $key_source);
                        
                        if (!$insert_key_stmt->execute()) {
                            throw new Exception("Failed to add key: $key_name");
                        }
                        
                        $total_keys++;
                    }
                }
            }
            
            $insert_key_stmt->close();
        }
        
        // Commit transaction
        $conn->commit();
        $conn->autocommit(true);
        
        echo json_encode([
            'success' => true,
            'template_id' => $template_id,
            'total_keys' => $total_keys,
            'message' => "Template '$template_name' created successfully with $total_keys keys"
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        $conn->autocommit(true);
        throw $e;
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