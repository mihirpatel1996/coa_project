<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/templates_config.php';

$template_code = isset($_GET['template_code']) ? trim($_GET['template_code']) : '';

if (!isset(TEMPLATES[$template_code])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Template not found'
    ]);
    exit;
}

// Build response with fields
$sections = [];
foreach (SECTIONS as $section_id => $section) {
    $fields = isset(TEMPLATE_FIELDS[$template_code][$section_id]) 
              ? TEMPLATE_FIELDS[$template_code][$section_id] : [];
    
    // Format fields to match frontend expectations
    $formatted_fields = [];
    foreach ($fields as $field) {
        $formatted_fields[] = [
            'id' => count($formatted_fields) + 1, // Fake ID for compatibility
            'key_name' => $field['field_name'],
            'key_source' => $field['field_source'],
            'key_order' => $field['field_order']
        ];
    }
    
    $sections[] = [
        'section_id' => $section_id,
        'section_name' => $section['section_name'],
        'keys' => $formatted_fields
    ];
}

echo json_encode([
    'success' => true,
    'template_info' => TEMPLATES[$template_code],
    'sections' => $sections
]);
?>