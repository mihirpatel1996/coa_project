<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';
require_once '../config/templates_config.php';

try {
    // Changed from catalog_id to catalog_number
    $catalogNumber = isset($_GET['catalog_number']) ? trim($_GET['catalog_number']) : '';
    $template_code = isset($_GET['template_code']) ? trim($_GET['template_code']) : '';
    $lot_number = isset($_GET['lot_number']) ? trim($_GET['lot_number']) : '';
    
    // Changed validation
    if (empty($catalogNumber)) {
        throw new Exception('Catalog number is required');
    }
    
    if (!isset(TEMPLATES[$template_code])) {
        throw new Exception('Valid template code is required');
    }
    
    $conn = getDBConnection();
    
    // Get catalog data - first check WITHOUT templateCode to see if catalog exists
    $catalog_sql = "SELECT * FROM catalogs WHERE catalogNumber = ?";
    $catalog_stmt = $conn->prepare($catalog_sql);
    $catalog_stmt->bind_param("s", $catalogNumber);
    $catalog_stmt->execute();
    $catalog_result = $catalog_stmt->get_result();
    
    if ($catalog_result->num_rows === 0) {
        throw new Exception('Catalog not found');
    }
    
    $catalog_data = $catalog_result->fetch_assoc();
    $catalog_stmt->close();
    
    // Check if template matches
    if ($catalog_data['templateCode'] !== $template_code) {
        // Template mismatch - return empty structure
        $sections_data = [];
        foreach (SECTIONS as $section_id => $section) {
            $key_values = [];
            
            if (isset(TEMPLATE_FIELDS[$template_code][$section_id])) {
                foreach (TEMPLATE_FIELDS[$template_code][$section_id] as $field_config) {
                    $key_values[] = [
                        'key' => $field_config['field_name'],
                        'value' => '', // Empty value for template change
                        'source' => $field_config['field_source'],
                        'order' => $field_config['field_order']
                    ];
                }
            }
            
            $sections_data[] = [
                'section_id' => $section_id,
                'section_name' => $section['section_name'],
                'key_values' => $key_values
            ];
        }
        
        echo json_encode([
            'sections_data' => $sections_data,
            'template_mismatch' => true,
            'debug_info' => [
                'catalog_number' => $catalogNumber,
                'requested_template' => $template_code,
                'catalog_template' => $catalog_data['templateCode'],
                'lot_number' => $lot_number
            ]
        ]);
        exit;
    }
    
    // Templates match - continue with normal flow
    // Get lot data if lot_number provided
    $lot_data = null;
    if ($lot_number) {
        $lot_sql = "SELECT * FROM lots WHERE catalogNumber = ? AND lotNumber = ? AND templateCode = ?";
        $lot_stmt = $conn->prepare($lot_sql);
        $lot_stmt->bind_param("sss", $catalogNumber, $lot_number, $template_code);
        $lot_stmt->execute();
        $lot_result = $lot_stmt->get_result();
        
        if ($lot_result->num_rows > 0) {
            $lot_data = $lot_result->fetch_assoc();
        }
        $lot_stmt->close();
    }
    
    // Build response using static template structure
    $sections_data = [];
    foreach (SECTIONS as $section_id => $section) {
        $key_values = [];
        
        if (isset(TEMPLATE_FIELDS[$template_code][$section_id])) {
            foreach (TEMPLATE_FIELDS[$template_code][$section_id] as $field_config) {
                $value = '';
                
                // Get value from appropriate table based on field_source
                if ($field_config['field_source'] === 'catalog' && $catalog_data) {
                    $db_field = $field_config['db_field'];
                    $value = $catalog_data[$db_field] ?? '';
                } elseif ($field_config['field_source'] === 'lot' && $lot_data) {
                    $db_field = $field_config['db_field'];
                    $value = $lot_data[$db_field] ?? '';
                }
                
                $key_values[] = [
                    'key' => $field_config['field_name'],
                    'value' => $value,
                    'source' => $field_config['field_source'],
                    'order' => $field_config['field_order']
                ];
            }
        }
        
        $sections_data[] = [
            'section_id' => $section_id,
            'section_name' => $section['section_name'],
            'key_values' => $key_values
        ];
    }
    
    echo json_encode([
        'sections_data' => $sections_data,
        'debug_info' => [
            'catalog_number' => $catalogNumber,
            'template_code' => $template_code,
            'lot_number' => $lot_number,
            'catalog_number_db' => $catalog_data['catalogNumber']
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>