<?php
// api/get_templates.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../config/database.php';

try {
    // Get database connection
    $conn = getDBConnection();
    
    // Fetch all templates
    $sql = "SELECT id, template_name, description, is_default, created_at 
            FROM templates 
            ORDER BY is_default DESC, template_name";
    $result = $conn->query($sql);
    
    $templates = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $templates[] = [
                'id' => $row['id'],
                'template_name' => $row['template_name'],
                'description' => $row['description'],
                'is_default' => (bool)$row['is_default'],
                'created_at' => $row['created_at']
            ];
        }
    }
    
    // Return templates as JSON
    echo json_encode([
        'success' => true,
        'templates' => $templates
    ]);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching templates: ' . $e->getMessage()
    ]);
} finally {
    // Close connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>