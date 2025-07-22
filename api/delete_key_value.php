<?php
// api/delete_key_value.php
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
    $section_id = isset($input['section_id']) ? intval($input['section_id']) : 0;
    $lot_number = isset($input['lot_number']) ? trim($input['lot_number']) : '';
    $key = isset($input['key']) ? trim($input['key']) : '';
    $source = isset($input['source']) ? trim($input['source']) : '';
    
    if ($catalog_id <= 0) {
        throw new Exception('Valid catalog ID is required');
    }
    
    if ($section_id <= 0) {
        throw new Exception('Valid section ID is required');
    }
    
    if (empty($key)) {
        throw new Exception('Key is required');
    }
    
    if (!in_array($source, ['catalog', 'lot'])) {
        throw new Exception('Valid source is required (catalog or lot)');
    }
    
    // Get database connection
    $conn = getDBConnection();
    
    $deleted_count = 0;
    
    if ($source === 'catalog') {
        // Delete from catalog_details
        $delete_sql = "DELETE FROM catalog_details WHERE catalog_id = ? AND section_id = ? AND `key` = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("iis", $catalog_id, $section_id, $key);
        
        if ($delete_stmt->execute()) {
            $deleted_count = $delete_stmt->affected_rows;
        }
        $delete_stmt->close();
        
    } elseif ($source === 'lot') {
        // Check if lot_details table exists
        $lot_details_check = $conn->query("SHOW TABLES LIKE 'lot_details'");
        
        if ($lot_details_check->num_rows > 0) {
            // New structure - delete from lot_details
            if (empty($lot_number)) {
                throw new Exception('Lot number is required for lot data deletion');
            }
            
            // Get lot_id first
            $lot_id_sql = "SELECT id FROM lots WHERE catalog_id = ? AND lot_number = ?";
            $lot_id_stmt = $conn->prepare($lot_id_sql);
            $lot_id_stmt->bind_param("is", $catalog_id, $lot_number);
            $lot_id_stmt->execute();
            $lot_id_result = $lot_id_stmt->get_result();
            
            if ($lot_id_result->num_rows === 0) {
                throw new Exception('Lot not found');
            }
            
            $lot_row = $lot_id_result->fetch_assoc();
            $lot_id = $lot_row['id'];
            $lot_id_stmt->close();
            
            $delete_sql = "DELETE FROM lot_details WHERE lot_id = ? AND section_id = ? AND `key` = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("iis", $lot_id, $section_id, $key);
            
            if ($delete_stmt->execute()) {
                $deleted_count = $delete_stmt->affected_rows;
            }
            $delete_stmt->close();
            
        } else {
            // Check if lots table has old structure
            $lots_structure = $conn->query("SHOW COLUMNS FROM lots");
            $lots_columns = [];
            while ($row = $lots_structure->fetch_assoc()) {
                $lots_columns[] = $row['Field'];
            }
            
            if (in_array('key', $lots_columns) && in_array('section_id', $lots_columns)) {
                // Old mixed structure - delete from lots table
                if (empty($lot_number)) {
                    throw new Exception('Lot number is required for lot data deletion');
                }
                
                $delete_sql = "DELETE FROM lots WHERE catalog_id = ? AND section_id = ? AND lot_number = ? AND `key` = ?";
                $delete_stmt = $conn->prepare($delete_sql);
                $delete_stmt->bind_param("iiss", $catalog_id, $section_id, $lot_number, $key);
                
                if ($delete_stmt->execute()) {
                    $deleted_count = $delete_stmt->affected_rows;
                }
                $delete_stmt->close();
            } else {
                throw new Exception('Cannot delete lot data - no appropriate table structure found');
            }
        }
    }
    
    if ($deleted_count > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Key-value deleted successfully',
            'deleted_count' => $deleted_count
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No matching key-value found to delete'
        ]);
    }
    
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