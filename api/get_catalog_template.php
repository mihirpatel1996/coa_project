<?php
// api/get_catalog_template.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../config/database.php';

try {
    // Get catalog_id from query parameter
    $catalog_id = isset($_GET['catalog_id']) ? intval($_GET['catalog_id']) : 0;
    
    if ($catalog_id <= 0) {
        throw new Exception('Valid catalog ID is required');
    }
    
    // Get database connection
    $conn = getDBConnection();
    
    // Get template_id for the catalog
    $sql = "SELECT template_id FROM catalogs WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $catalog_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'template_id' => $row['template_id']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Catalog not found'
        ]);
    }
    
    $stmt->close();
    
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