<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/templates_config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    $catalog_name = isset($input['catalog_name']) ? trim($input['catalog_name']) : '';
    $catalog_number = isset($input['catalog_number']) ? trim($input['catalog_number']) : '';
    $template_code = isset($input['template_code']) ? trim($input['template_code']) : '';
    
    if (empty($catalog_name)) {
        throw new Exception('Catalog name is required');
    }
    
    if (empty($catalog_number)) {
        throw new Exception('Catalog number is required');
    }
    
    if (!isset(TEMPLATES[$template_code])) {
        throw new Exception('Invalid template code');
    }
    
    $conn = getDBConnection();
    
    // Check if catalog already exists
    $check_sql = "SELECT id FROM catalogs WHERE catalogNumber = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $catalog_number);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $existing = $check_result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'catalog_id' => $existing['id'],
            'message' => 'Catalog already exists',
            'action' => 'existing'
        ]);
    } else {
        // Create new catalog
        $insert_sql = "INSERT INTO catalogs (catalogName, catalogNumber, templateCode) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("sss", $catalog_name, $catalog_number, $template_code);
        
        if ($insert_stmt->execute()) {
            $catalog_id = $conn->insert_id;
            echo json_encode([
                'success' => true,
                'catalog_id' => $catalog_id,
                'message' => 'Catalog created successfully',
                'action' => 'created'
            ]);
        } else {
            throw new Exception('Failed to create catalog');
        }
        $insert_stmt->close();
    }
    
    $check_stmt->close();
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>