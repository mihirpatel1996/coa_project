<?php
// api/get_section_data.php (WITH TEMPLATE_ID PARAMETER)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../config/database.php';

try {
    // Get parameters
    $catalog_id = isset($_GET['catalog_id']) ? intval($_GET['catalog_id']) : 0;
    $template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : 0;
    $lot_number = isset($_GET['lot_number']) ? trim($_GET['lot_number']) : '';
    
    if ($catalog_id <= 0) {
        throw new Exception('Valid catalog ID is required');
    }
    
    if ($template_id <= 0) {
        throw new Exception('Valid template ID is required');
    }
    
    // Get database connection
    $conn = getDBConnection();
    
    // Get catalog information
    $catalog_sql = "SELECT catalog_number FROM catalogs WHERE id = ?";
    $catalog_stmt = $conn->prepare($catalog_sql);
    $catalog_stmt->bind_param("i", $catalog_id);
    $catalog_stmt->execute();
    $catalog_result = $catalog_stmt->get_result();
    
    if ($catalog_result->num_rows === 0) {
        throw new Exception('Catalog not found');
    }
    
    $catalog_info = $catalog_result->fetch_assoc();
    $catalog_number = $catalog_info['catalog_number'];
    $catalog_stmt->close();
    
    // Validate template exists
    $template_check_sql = "SELECT template_name FROM templates WHERE id = ?";
    $template_stmt = $conn->prepare($template_check_sql);
    $template_stmt->bind_param("i", $template_id);
    $template_stmt->execute();
    $template_result = $template_stmt->get_result();
    
    if ($template_result->num_rows === 0) {
        throw new Exception('Template not found');
    }
    
    $template_info = $template_result->fetch_assoc();
    $template_name = $template_info['template_name'];
    $template_stmt->close();
    
    $sections_data = [];
    
    // Get all sections in order
    $sections_sql = "SELECT id, section_name FROM sections ORDER BY default_order";
    $sections_result = $conn->query($sections_sql);
    
    if ($sections_result && $sections_result->num_rows > 0) {
        while ($section = $sections_result->fetch_assoc()) {
            $section_id = $section['id'];
            $section_name = $section['section_name'];
            
            $key_values = [];
            
            // Get ALL template keys for this section (ordered by key_order)
            $template_keys_sql = "SELECT key_name, key_source, key_order 
                                 FROM template_keys 
                                 WHERE template_id = ? AND section_id = ? 
                                 ORDER BY key_order";
            $template_keys_stmt = $conn->prepare($template_keys_sql);
            $template_keys_stmt->bind_param("ii", $template_id, $section_id);
            $template_keys_stmt->execute();
            $template_keys_result = $template_keys_stmt->get_result();
            
            // Get existing catalog data for this section AND template
            $catalog_data = [];
            $catalog_data_sql = "SELECT `key`, `value` 
                                FROM catalog_details 
                                WHERE catalog_number = ? AND section_id = ? AND template_id = ?";
            $catalog_data_stmt = $conn->prepare($catalog_data_sql);
            $catalog_data_stmt->bind_param("sii", $catalog_number, $section_id, $template_id);
            $catalog_data_stmt->execute();
            $catalog_data_result = $catalog_data_stmt->get_result();
            
            while ($row = $catalog_data_result->fetch_assoc()) {
                $catalog_data[$row['key']] = $row['value'];
            }
            $catalog_data_stmt->close();
            
            // Get existing lot data for this section AND template (if lot_number provided)
            $lot_data = [];
            if (!empty($lot_number)) {
                $lot_data_sql = "SELECT `key`, `value` 
                                FROM lot_details 
                                WHERE lot_number = ? AND section_id = ? AND template_id = ?";
                $lot_data_stmt = $conn->prepare($lot_data_sql);
                $lot_data_stmt->bind_param("sii", $lot_number, $section_id, $template_id);
                $lot_data_stmt->execute();
                $lot_data_result = $lot_data_stmt->get_result();
                
                while ($row = $lot_data_result->fetch_assoc()) {
                    $lot_data[$row['key']] = $row['value'];
                }
                $lot_data_stmt->close();
            }
            
            // Process each template key and combine with existing data
            while ($template_key = $template_keys_result->fetch_assoc()) {
                $key_name = $template_key['key_name'];
                $key_source = $template_key['key_source'];
                $key_order = $template_key['key_order'];
                
                // Get the value based on the key source
                $value = '';
                if ($key_source === 'catalog' && isset($catalog_data[$key_name])) {
                    $value = $catalog_data[$key_name];
                } elseif ($key_source === 'lot' && isset($lot_data[$key_name])) {
                    $value = $lot_data[$key_name];
                }
                
                $key_values[] = [
                    'key' => $key_name,
                    'value' => $value,
                    'source' => $key_source,
                    'order' => $key_order
                ];
            }
            $template_keys_stmt->close();
            
            // Add section to response (even if no keys - will show "no keys defined" message)
            $sections_data[] = [
                'section_id' => $section_id,
                'section_name' => $section_name,
                'key_values' => $key_values
            ];
        }
    }
    
    // Return sections data as JSON with debug info
    echo json_encode([
        'sections_data' => $sections_data,
        'debug_info' => [
            'catalog_id' => $catalog_id,
            'catalog_number' => $catalog_number,
            'template_id' => $template_id,
            'template_name' => $template_name,
            'lot_number' => $lot_number,
            'total_sections' => count($sections_data),
            'total_keys' => array_sum(array_map(function($s) { return count($s['key_values']); }, $sections_data)),
            'keys_by_section' => array_map(function($s) { 
                return [
                    'section' => $s['section_name'], 
                    'count' => count($s['key_values']),
                    'keys' => array_map(function($kv) { return $kv['key'] . ' (' . $kv['source'] . ')'; }, $s['key_values'])
                ]; 
            }, $sections_data)
        ]
    ]);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Error fetching section data: ' . $e->getMessage(),
        'debug_info' => [
            'catalog_id' => isset($catalog_id) ? $catalog_id : 'not set',
            'template_id' => isset($template_id) ? $template_id : 'not set',
            'lot_number' => isset($lot_number) ? $lot_number : 'not set'
        ]
    ]);
} finally {
    // Close connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>