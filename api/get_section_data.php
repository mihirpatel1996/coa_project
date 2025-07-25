<?php
// api/get_section_data.php (UPDATED WITHOUT section_id)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

try {
    $catalog_id = isset($_GET['catalog_id']) ? intval($_GET['catalog_id']) : 0;
    $template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : 0;
    $lot_number = isset($_GET['lot_number']) ? trim($_GET['lot_number']) : '';
    
    if ($catalog_id <= 0) {
        throw new Exception('Valid catalog ID is required');
    }
    
    if ($template_id <= 0) {
        throw new Exception('Valid template ID is required');
    }
    
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
    
    $sections_data = [];
    
    // Get all sections
    $sections_sql = "SELECT id, section_name FROM sections ORDER BY default_order";
    $sections_result = $conn->query($sections_sql);
    
    while ($section = $sections_result->fetch_assoc()) {
        $section_id = $section['id'];
        $section_name = $section['section_name'];
        
        $key_values = [];
        
        // Get template keys for this section with their values
        $template_keys_sql = "
            SELECT 
                tk.key_name, 
                tk.key_source, 
                tk.key_order,
                CASE 
                    WHEN tk.key_source = 'catalog' THEN cd.value
                    WHEN tk.key_source = 'lot' THEN ld.value
                    ELSE ''
                END as value
            FROM template_keys tk
            LEFT JOIN catalog_details cd ON 
                cd.catalog_number = ? AND 
                cd.template_id = tk.template_id AND 
                cd.key = tk.key_name AND
                tk.key_source = 'catalog'
            LEFT JOIN lot_details ld ON 
                ld.lot_number = ? AND 
                ld.template_id = tk.template_id AND 
                ld.key = tk.key_name AND
                tk.key_source = 'lot'
            WHERE tk.template_id = ? AND tk.section_id = ?
            ORDER BY tk.key_order";
            
        $keys_stmt = $conn->prepare($template_keys_sql);
        $keys_stmt->bind_param("ssii", $catalog_number, $lot_number, $template_id, $section_id);
        $keys_stmt->execute();
        $keys_result = $keys_stmt->get_result();
        
        while ($key_data = $keys_result->fetch_assoc()) {
            $key_values[] = [
                'key' => $key_data['key_name'],
                'value' => $key_data['value'] ?? '',
                'source' => $key_data['key_source'],
                'order' => $key_data['key_order']
            ];
        }
        $keys_stmt->close();
        
        $sections_data[] = [
            'section_id' => $section_id,
            'section_name' => $section_name,
            'key_values' => $key_values
        ];
    }
    
    echo json_encode([
        'sections_data' => $sections_data,
        'debug_info' => [
            'catalog_id' => $catalog_id,
            'catalog_number' => $catalog_number,
            'template_id' => $template_id,
            'lot_number' => $lot_number,
            'total_sections' => count($sections_data)
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Error fetching section data: ' . $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}