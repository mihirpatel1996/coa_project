<?php
// api/save_key_value_pair.php (UPDATED FOR CURRENT STRUCTURE)
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
    $value = isset($input['value']) ? trim($input['value']) : '';
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
    
    if (empty($value)) {
        throw new Exception('Value is required');
    }
    
    if (!in_array($source, ['catalog', 'lot'])) {
        throw new Exception('Valid source is required (catalog or lot)');
    }
    
    // Get database connection
    $conn = getDBConnection();
    
    // Get catalog information
    $catalog_sql = "SELECT catalog_number, template_id FROM catalogs WHERE id = ?";
    $catalog_stmt = $conn->prepare($catalog_sql);
    $catalog_stmt->bind_param("i", $catalog_id);
    $catalog_stmt->execute();
    $catalog_result = $catalog_stmt->get_result();
    
    if ($catalog_result->num_rows === 0) {
        throw new Exception('Catalog not found');
    }
    
    $catalog_info = $catalog_result->fetch_assoc();
    $catalog_number = $catalog_info['catalog_number'];
    $template_id = $catalog_info['template_id'];
    $catalog_stmt->close();
    
    if ($source === 'catalog') {
        // Check if key already exists for this catalog/section
        $check_sql = "SELECT id FROM catalog_details WHERE catalog_number = ? AND section_id = ? AND `key` = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("sis", $catalog_number, $section_id, $key);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update existing
            $update_sql = "UPDATE catalog_details SET `value` = ?, updated_at = CURRENT_TIMESTAMP WHERE catalog_number = ? AND section_id = ? AND `key` = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssis", $value, $catalog_number, $section_id, $key);
            
            if ($update_stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Catalog key-value pair updated successfully',
                    'action' => 'updated'
                ]);
            } else {
                throw new Exception('Failed to update catalog key-value pair');
            }
            $update_stmt->close();
        } else {
            // Insert new
            $insert_sql = "INSERT INTO catalog_details (catalog_number, template_id, section_id, `key`, `value`) VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("siiss", $catalog_number, $template_id, $section_id, $key, $value);
            
            if ($insert_stmt->execute()) {
                $insert_id = $conn->insert_id;
                echo json_encode([
                    'success' => true,
                    'id' => $insert_id,
                    'message' => 'Catalog key-value pair saved successfully',
                    'action' => 'created'
                ]);
            } else {
                throw new Exception('Failed to save catalog key-value pair');
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
        
    } elseif ($source === 'lot') {
        if (empty($lot_number)) {
            throw new Exception('Lot number is required for lot data');
        }
        
        // Check if lot exists
        $lot_check_sql = "SELECT id FROM lots WHERE catalog_id = ? AND lot_number = ?";
        $lot_check_stmt = $conn->prepare($lot_check_sql);
        $lot_check_stmt->bind_param("is", $catalog_id, $lot_number);
        $lot_check_stmt->execute();
        $lot_check_result = $lot_check_stmt->get_result();
        
        if ($lot_check_result->num_rows === 0) {
            // Create lot if it doesn't exist
            $create_lot_sql = "INSERT INTO lots (catalog_id, lot_number) VALUES (?, ?)";
            $create_lot_stmt = $conn->prepare($create_lot_sql);
            $create_lot_stmt->bind_param("is", $catalog_id, $lot_number);
            $create_lot_stmt->execute();
            $create_lot_stmt->close();
        }
        $lot_check_stmt->close();
        
        // Check if key already exists for this lot/section
        $check_sql = "SELECT id FROM lot_details WHERE lot_number = ? AND section_id = ? AND `key` = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("sis", $lot_number, $section_id, $key);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update existing
            $update_sql = "UPDATE lot_details SET `value` = ?, updated_at = CURRENT_TIMESTAMP WHERE lot_number = ? AND section_id = ? AND `key` = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssis", $value, $lot_number, $section_id, $key);
            
            if ($update_stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Lot key-value pair updated successfully',
                    'action' => 'updated'
                ]);
            } else {
                throw new Exception('Failed to update lot key-value pair');
            }
            $update_stmt->close();
        } else {
            // Insert new
            $insert_sql = "INSERT INTO lot_details (lot_number, template_id, section_id, `key`, `value`) VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("siiss", $lot_number, $template_id, $section_id, $key, $value);
            
            if ($insert_stmt->execute()) {
                $insert_id = $conn->insert_id;
                echo json_encode([
                    'success' => true,
                    'id' => $insert_id,
                    'message' => 'Lot key-value pair saved successfully',
                    'action' => 'created'
                ]);
            } else {
                throw new Exception('Failed to save lot key-value pair');
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
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