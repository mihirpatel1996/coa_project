<?php
// api/save_all_data.php - Bulk save endpoint for all key-value pairs
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
    
    // Validate required fields
    $catalog_id = isset($input['catalog_id']) ? intval($input['catalog_id']) : 0;
    $catalog_number = isset($input['catalog_number']) ? trim($input['catalog_number']) : '';
    $catalog_name = isset($input['catalog_name']) ? trim($input['catalog_name']) : '';
    $lot_number = isset($input['lot_number']) ? trim($input['lot_number']) : '';
    $template_id = isset($input['template_id']) ? intval($input['template_id']) : 0;
    $key_values = isset($input['key_values']) ? $input['key_values'] : [];
    
    // Validation
    if ($catalog_id <= 0) {
        throw new Exception('Valid catalog ID is required');
    }
    
    if (empty($catalog_number)) {
        throw new Exception('Catalog number is required');
    }
    
    if (empty($catalog_name)) {
        throw new Exception('Catalog name is required');
    }
    
    if (empty($lot_number)) {
        throw new Exception('Lot number is required');
    }
    
    if ($template_id <= 0) {
        throw new Exception('Valid template ID is required');
    }
    
    if (empty($key_values)) {
        throw new Exception('Key-value pairs are required');
    }
    
    // Validate all key-values have values
    foreach ($key_values as $kv) {
        if (empty($kv['key'])) {
            throw new Exception('All keys must be specified');
        }
        if (!isset($kv['value']) || trim($kv['value']) === '') {
            throw new Exception("Value for key '{$kv['key']}' cannot be empty");
        }
        if (!in_array($kv['source'], ['catalog', 'lot'])) {
            throw new Exception("Invalid source for key '{$kv['key']}'");
        }
    }
    
    $conn = getDBConnection();
    
    // Start transaction
    $conn->autocommit(false);
    
    try {
        // Update catalog name if changed
        $update_catalog_sql = "UPDATE catalogs SET catalog_name = ? WHERE id = ? AND catalog_number = ?";
        $update_catalog_stmt = $conn->prepare($update_catalog_sql);
        $update_catalog_stmt->bind_param("sis", $catalog_name, $catalog_id, $catalog_number);
        $update_catalog_stmt->execute();
        $update_catalog_stmt->close();
        
        // Check if lot exists, create if not
        $lot_check_sql = "SELECT id FROM lots WHERE catalog_id = ? AND lot_number = ?";
        $lot_check_stmt = $conn->prepare($lot_check_sql);
        $lot_check_stmt->bind_param("is", $catalog_id, $lot_number);
        $lot_check_stmt->execute();
        $lot_check_result = $lot_check_stmt->get_result();
        
        if ($lot_check_result->num_rows === 0) {
            // Create lot
            $create_lot_sql = "INSERT INTO lots (catalog_id, lot_number, template_id) VALUES (?, ?, ?)";
            $create_lot_stmt = $conn->prepare($create_lot_sql);
            $create_lot_stmt->bind_param("isi", $catalog_id, $lot_number, $template_id);
            $create_lot_stmt->execute();
            $lot_id = $conn->insert_id;
            $create_lot_stmt->close();
        } else {
            $lot_row = $lot_check_result->fetch_assoc();
            $lot_id = $lot_row['id'];
        }
        $lot_check_stmt->close();
        
        // Process key-value pairs
        $catalog_updates = 0;
        $catalog_inserts = 0;
        $lot_updates = 0;
        $lot_inserts = 0;
        
        foreach ($key_values as $kv) {
            $key = trim($kv['key']);
            $value = trim($kv['value']);
            $source = $kv['source'];
            
            if ($source === 'catalog') {
                // Check if exists
                $check_sql = "SELECT id FROM catalog_details 
                             WHERE catalog_number = ? AND template_id = ? AND `key` = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("sis", $catalog_number, $template_id, $key);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    // Update existing
                    $update_sql = "UPDATE catalog_details 
                                  SET `value` = ?, updated_at = CURRENT_TIMESTAMP 
                                  WHERE catalog_number = ? AND template_id = ? AND `key` = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ssis", $value, $catalog_number, $template_id, $key);
                    $update_stmt->execute();
                    $catalog_updates++;
                    $update_stmt->close();
                } else {
                    // Insert new
                    $insert_sql = "INSERT INTO catalog_details 
                                  (catalog_number, template_id, `key`, `value`) 
                                  VALUES (?, ?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->bind_param("siss", $catalog_number, $template_id, $key, $value);
                    $insert_stmt->execute();
                    $catalog_inserts++;
                    $insert_stmt->close();
                }
                $check_stmt->close();
                
            } elseif ($source === 'lot') {
                // Check if exists
                $check_sql = "SELECT id FROM lot_details 
                             WHERE lot_number = ? AND template_id = ? AND `key` = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("sis", $lot_number, $template_id, $key);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    // Update existing
                    $update_sql = "UPDATE lot_details 
                                  SET `value` = ?, updated_at = CURRENT_TIMESTAMP 
                                  WHERE lot_number = ? AND template_id = ? AND `key` = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ssis", $value, $lot_number, $template_id, $key);
                    $update_stmt->execute();
                    $lot_updates++;
                    $update_stmt->close();
                } else {
                    // Insert new
                    $insert_sql = "INSERT INTO lot_details 
                                  (lot_number, template_id, `key`, `value`) 
                                  VALUES (?, ?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->bind_param("siss", $lot_number, $template_id, $key, $value);
                    $insert_stmt->execute();
                    $lot_inserts++;
                    $insert_stmt->close();
                }
                $check_stmt->close();
            }
        }
        
        // Commit transaction
        $conn->commit();
        $conn->autocommit(true);
        
        echo json_encode([
            'success' => true,
            'message' => 'All data saved successfully',
            'stats' => [
                'catalog_updates' => $catalog_updates,
                'catalog_inserts' => $catalog_inserts,
                'lot_updates' => $lot_updates,
                'lot_inserts' => $lot_inserts,
                'total_processed' => count($key_values)
            ],
            'lot_id' => $lot_id
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        $conn->autocommit(true);
        throw $e;
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
?>