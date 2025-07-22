<?php
// api/get_lot_data.php
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
        throw new Exception('Invalid catalog ID');
    }
    
    // Get database connection
    $conn = getDBConnection();
    
    // Fetch all unique lot numbers for the given catalog
    $sql = "SELECT DISTINCT lot_number FROM lots WHERE catalog_id = ? ORDER BY lot_number";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $catalog_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $lots = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $lots[] = [
                'lot_number' => $row['lot_number']
            ];
        }
    }
    
    // Return lots as JSON
    echo json_encode($lots);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Error fetching lots: ' . $e->getMessage()
    ]);
} finally {
    // Close connection and statement
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>