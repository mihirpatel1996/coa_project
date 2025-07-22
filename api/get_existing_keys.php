<?php
// api/get_existing_keys.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../config/database.php';

try {
    // Get parameters
    $catalog_id = isset($_GET['catalog_id']) ? intval($_GET['catalog_id']) : 0;
    $section_id = isset($_GET['section_id']) ? intval($_GET['section_id']) : 0;
    $source = isset($_GET['source']) ? trim($_GET['source']) : '';
    $lot_number = isset($_GET['lot_number']) ? trim($_GET['lot_number']) : '';
    
    if ($catalog_id <= 0) {
        throw new Exception('Valid catalog ID is required');
    }
    
    if ($section_id <= 0) {
        throw new Exception('Valid section ID is required');
    }
    
    if (!in_array($source, ['catalog', 'lot'])) {
        throw new Exception('Valid source is required (catalog or lot)');
    }
    
    // Get database connection
    $conn = getDBConnection();
    
    $keys = [];
    
    if ($source === 'catalog') {
        // Get unique keys from catalog_details for this section
        $sql = "SELECT DISTINCT `key` FROM catalog_details 
                WHERE section_id = ? 
                ORDER BY `key`";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $section_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $keys[] = $row['key'];
        }
        $stmt->close();
        
    } 
    if ($source === 'lot') {

        $sql = "SELECT DISTINCT `key` FROM lot_details 
        WHERE section_id = ? 
        ORDER BY `key`";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $section_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while($row = $result->fetch_assoc()){
            $keys[] = $row['key'];
        }
        /*
        // Check if lot_details table exists
        $lot_details_check = $conn->query("SHOW TABLES LIKE 'lot_details'");
        
        if ($lot_details_check->num_rows > 0) {
            // New structure - get keys from lot_details
            if (!empty($lot_number)) {
                // Get lot_id first
                $lot_id_sql = "SELECT id FROM lots WHERE catalog_id = ? AND lot_number = ?";
                $lot_id_stmt = $conn->prepare($lot_id_sql);
                $lot_id_stmt->bind_param("is", $catalog_id, $lot_number);
                $lot_id_stmt->execute();
                $lot_id_result = $lot_id_stmt->get_result();
                
                if ($lot_id_result->num_rows > 0) {
                    $lot_row = $lot_id_result->fetch_assoc();
                    $lot_id = $lot_row['id'];
                    
                    $sql = "SELECT DISTINCT `key` FROM lot_details 
                            WHERE lot_id = ? AND section_id = ? 
                            ORDER BY `key`";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ii", $lot_id, $section_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    while ($row = $result->fetch_assoc()) {
                        $keys[] = $row['key'];
                    }
                    $stmt->close();
                }
                $lot_id_stmt->close();
            }
        } 
        */  
    }

    
    // Return keys as JSON
    echo json_encode([
        'success' => true,
        'keys' => $keys,
        'debug_info' => [
            'catalog_id' => $catalog_id,
            'section_id' => $section_id,
            'source' => $source,
            'lot_number' => $lot_number,
            'keys_found' => count($keys)
        ]
    ]);
    
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