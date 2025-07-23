<?php
// api/update_catalog_template.php
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
    $catalog_id = isset($input['catalog_id']) ? intval($input['catalog_id']) : 0;
    $template_id = isset($input['template_id']) ? intval($input['template_id']) : 0;
    
    if ($catalog_id <= 0) {
        throw new Exception('Valid catalog ID is required');
    }
    
    if ($template_id <= 0) {
        throw new Exception('Valid template ID is required');
    }
    
    // Get database connection
    $conn = getDBConnection();
    
    // Check if catalog exists
    $catalog_check_sql = "SELECT id FROM catalogs WHERE id = ?";
    $catalog_stmt = $conn->prepare($catalog_check_sql);
    $catalog_stmt->bind_param("i", $catalog_id);
    $catalog_stmt->execute();
    $catalog_result = $catalog_stmt->get_result();
    
    if ($catalog_result->num_rows === 0) {
        throw new Exception('Catalog not found');
    }
    $catalog_stmt->close();
    
    // Check if template exists
    $template_check_sql = "SELECT id FROM templates WHERE id = ?";
    $template_stmt = $conn->prepare($template_check_sql);
    $template_stmt->bind_param("i", $template_id);
    $template_stmt->execute();
    $template_result = $template_stmt->get_result();
    
    if ($template_result->num_rows === 0) {
        throw new Exception('Template not found');
    }
    $template_stmt->close();
    
    // Update catalog template
    $update_sql = "UPDATE catalogs SET template_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $template_id, $catalog_id);
    
    if ($update_stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Catalog template updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update catalog template');
    }
    
    $update_stmt->close();
    
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