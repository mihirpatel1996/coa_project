<?php
// api/get_template_keys.php (DEBUGGED VERSION)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../config/database.php';

try {
    // Get template_id from query parameter
    $template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : 0;
    
    if ($template_id <= 0) {
        throw new Exception('Valid template ID is required');
    }
    
    // Get database connection
    $conn = getDBConnection();
    
    // Get template info
    $template_sql = "SELECT template_name, description FROM templates WHERE id = ?";
    $template_stmt = $conn->prepare($template_sql);
    $template_stmt->bind_param("i", $template_id);
    $template_stmt->execute();
    $template_result = $template_stmt->get_result();
    
    if ($template_result->num_rows === 0) {
        throw new Exception('Template not found');
    }
    
    $template_info = $template_result->fetch_assoc();
    $template_stmt->close();
    
    // Get all sections with their keys for this template
    $sections_data = [];
    
    // Get sections (always 3 fixed sections)
    $sections_sql = "SELECT id, section_name FROM sections ORDER BY default_order";
    $sections_result = $conn->query($sections_sql);
    
    while ($section = $sections_result->fetch_assoc()) {
        $section_id = $section['id'];
        $section_name = $section['section_name'];
        
        // Get keys for this section and template
        $keys_sql = "SELECT id, key_name, key_source, key_order 
                     FROM template_keys 
                     WHERE template_id = ? AND section_id = ? 
                     ORDER BY key_order";
        $keys_stmt = $conn->prepare($keys_sql);
        $keys_stmt->bind_param("ii", $template_id, $section_id);
        $keys_stmt->execute();
        $keys_result = $keys_stmt->get_result();
        
        $keys = [];
        while ($key = $keys_result->fetch_assoc()) {
            $keys[] = [
                'id' => $key['id'],
                'key_name' => $key['key_name'],
                'key_source' => $key['key_source'],
                'key_order' => $key['key_order']
            ];
        }
        $keys_stmt->close();
        
        $sections_data[] = [
            'section_id' => $section_id,
            'section_name' => $section_name,
            'keys' => $keys
        ];
    }
    
    // Return template configuration with debug info
    echo json_encode([
        'success' => true,
        'template_info' => $template_info,
        'sections' => $sections_data,
        'debug' => [
            'template_id' => $template_id,
            'sections_count' => count($sections_data),
            'total_keys' => array_sum(array_map(function($s) { return count($s['keys']); }, $sections_data))
        ]
    ]);
    
} catch (Exception $e) {
    // Return error response with debug info
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'template_id' => isset($template_id) ? $template_id : 'not set',
            'error_line' => __LINE__
        ]
    ]);
} finally {
    // Close connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>