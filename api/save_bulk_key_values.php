<?php
// api/save_key_value_pair.php (UPDATED WITHOUT section_id)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate inputs
    $catalog_id = isset($input['catalog_id']) ? intval($input['catalog_id']) : 0;
    $lot_number = isset($input['lot_number']) ? trim($input['lot_number']) : '';
    $key = isset($input['key']) ? trim($input['key']) : '';
    $value = isset($input['value']) ? trim($input['value']) : '';
    $source = isset($input['source']) ? trim($input['source']) : '';
    
    if ($catalog_id <= 0) {
        throw new Exception('Valid catalog ID is required');
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
        // Check if key already exists
        $check_sql = "SELECT id FROM catalog_details WHERE catalog_number = ? AND template_id = ? AND `key` = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("sis", $catalog_number, $template_id, $key);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update existing
            $update_sql = "UPDATE catalog_details SET `value` = ?, updated_at = CURRENT_TIMESTAMP 
                          WHERE catalog_number = ? AND template_id = ? AND `key` = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssis", $value, $catalog_number, $template_id, $key);
            
            if ($update_stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Catalog value updated successfully',
                    'action' => 'updated'
                ]);
            } else {
                throw new Exception('Failed to update catalog value');
            }
            $update_stmt->close();
        } else {
            // Insert new
            $insert_sql = "INSERT INTO catalog_details (catalog_number, template_id, `key`, `value`) 
                          VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("siss", $catalog_number, $template_id, $key, $value);
            
            if ($insert_stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'id' => $conn->insert_id,
                    'message' => 'Catalog value saved successfully',
                    'action' => 'created'
                ]);
            } else {
                throw new Exception('Failed to save catalog value');
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
            $create_lot_sql = "INSERT INTO lots (catalog_id, lot_number, template_id) VALUES (?, ?, ?)";
            $create_lot_stmt = $conn->prepare($create_lot_sql);
            $create_lot_stmt->bind_param("isi", $catalog_id, $lot_number, $template_id);
            $create_lot_stmt->execute();
            $create_lot_stmt->close();
        }
        $lot_check_stmt->close();
        
        // Check if key already exists
        $check_sql = "SELECT id FROM lot_details WHERE lot_number = ? AND template_id = ? AND `key` = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("sis", $lot_number, $template_id, $key);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update existing
            $update_sql = "UPDATE lot_details SET `value` = ?, updated_at = CURRENT_TIMESTAMP 
                          WHERE lot_number = ? AND template_id = ? AND `key` = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssis", $value, $lot_number, $template_id, $key);
            
            if ($update_stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Lot value updated successfully',
                    'action' => 'updated'
                ]);
            } else {
                throw new Exception('Failed to update lot value');
            }
            $update_stmt->close();
        } else {
            // Insert new
            $insert_sql = "INSERT INTO lot_details (lot_number, template_id, `key`, `value`) 
                          VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("siss", $lot_number, $template_id, $key, $value);
            
            if ($insert_stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'id' => $conn->insert_id,
                    'message' => 'Lot value saved successfully',
                    'action' => 'created'
                ]);
            } else {
                throw new Exception('Failed to save lot value');
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
    
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
