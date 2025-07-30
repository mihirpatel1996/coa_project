<?php
// api/get_catalog_data.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../config/database.php';

try {
    // Get database connection
    $conn = getDBConnection();
    
    // Fetch all catalogs
    $sql = "SELECT id, catalogNumber, catalogName, createdAt FROM catalogs ORDER BY catalogNumber";
    $result = $conn->query($sql);
    
    $catalogs = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $catalogs[] = [
                'id' => $row['id'],
                'catalog_number' => $row['catalog_number'],
                'catalog_name' => $row['catalog_name'],
                'created_at' => $row['created_at']
            ];
        }
    }
    
    // Return catalogs as JSON
    echo json_encode($catalogs);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Error fetching catalogs: ' . $e->getMessage()
    ]);
} finally {
    // Close connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>