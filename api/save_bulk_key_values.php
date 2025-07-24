<?php
// api/save_bulk_key_values.php
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
    $lot_number = isset($input['lot_number']) ? trim($input['lot_number']) : '';
    $template_id = isset($input['template_id']) ? intval($input['template_id']) : 0;
    $key_values = isset($input['key_values']) ? $input['key_values'] : [];
    
    if ($catalog_id <= 0) {
        throw new Exception('Valid catalog ID is required');
    }
    
    if ($template_id <= 0) {
        throw new Exception('Valid template ID is required');
    }
    
    if (empty($key_values)) {
        throw new Exception('No key-value pairs to save');
    }
    
    // Get database connection
    $conn = getDBConnection();
    
    // Get catalog information and check if template needs updating
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
    $current_catalog_template_id = $catalog_info['template_id'];
    $catalog_stmt->close();
    
    // Verify template exists
    $template_check_sql = "SELECT id FROM templates WHERE id = ?";
    $template_check_stmt = $conn->prepare($template_check_sql);
    $template_check_stmt->bind_param("i", $template_id);
    $template_check_stmt->execute();
    $template_check_result = $template_check_stmt->get_result();
    
    if ($template_check_result->num_rows === 0) {
        throw new Exception('Template not found');
    }
    $template_check_stmt->close();
    
    // Start transaction
    $conn->autocommit(false);
    
    // Update catalog's template_id if it has changed
    if ($current_catalog_template_id != $template_id) {
        $update_catalog_template_sql = "UPDATE catalogs SET template_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $update_catalog_stmt = $conn->prepare($update_catalog_template_sql);
        $update_catalog_stmt->bind_param("ii", $template_id, $catalog_id);
        $update_catalog_stmt->execute();
        $update_catalog_stmt->close();
    }
    
    $saved_count = 0;
    $updated_count = 0;
    $inserted_count = 0;
    $errors = [];
    
    // Process all key-value pairs
    foreach ($key_values as $item) {
        try {
            $section_id = intval($item['section_id']);
            $key = trim($item['key']);
            $value = trim($item['value']);
            $source = trim($item['source']);
            
            // Validate each item
            if ($section_id <= 0) {
                throw new Exception("Invalid section ID for key: {$key}");
            }
            
            if (empty($key)) {
                throw new Exception("Empty key name found");
            }
            
            if (empty($value)) {
                throw new Exception("Empty value for key: {$key}");
            }
            
            if (!in_array($source, ['catalog', 'lot'])) {
                throw new Exception("Invalid source for key: {$key}");
            }
            
            if ($source === 'catalog') {
                // Check if record exists with all matching criteria
                $check_sql = "SELECT id FROM catalog_details 
                             WHERE catalog_number = ? 
                             AND template_id = ? 
                             AND section_id = ? 
                             AND `key` = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("siis", $catalog_number, $template_id, $section_id, $key);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    // Record exists, update it
                    $row = $check_result->fetch_assoc();
                    $record_id = $row['id'];
                    
                    $update_sql = "UPDATE catalog_details 
                                  SET `value` = ?, updated_at = CURRENT_TIMESTAMP 
                                  WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("si", $value, $record_id);
                    
                    if ($update_stmt->execute()) {
                        $updated_count++;
                        $saved_count++;
                    } else {
                        throw new Exception("Failed to update catalog detail for key: {$key}");
                    }
                    $update_stmt->close();
                } else {
                    // Record doesn't exist, insert new
                    $insert_sql = "INSERT INTO catalog_details 
                                  (catalog_number, template_id, section_id, `key`, `value`) 
                                  VALUES (?, ?, ?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->bind_param("siiss", $catalog_number, $template_id, $section_id, $key, $value);
                    
                    if ($insert_stmt->execute()) {
                        $inserted_count++;
                        $saved_count++;
                    } else {
                        throw new Exception("Failed to insert catalog detail for key: {$key}");
                    }
                    $insert_stmt->close();
                }
                $check_stmt->close();
                
            } elseif ($source === 'lot' && !empty($lot_number)) {
                // Ensure lot exists
                $lot_check_sql = "SELECT id, template_id FROM lots WHERE catalog_id = ? AND lot_number = ?";
                $lot_check_stmt = $conn->prepare($lot_check_sql);
                $lot_check_stmt->bind_param("is", $catalog_id, $lot_number);
                $lot_check_stmt->execute();
                $lot_check_result = $lot_check_stmt->get_result();
                
                if ($lot_check_result->num_rows === 0) {
                    // Create lot if it doesn't exist
                    $create_lot_sql = "INSERT INTO lots (catalog_id, lot_number, template_id) VALUES (?, ?, ?)";
                    $create_lot_stmt = $conn->prepare($create_lot_sql);
                    $create_lot_stmt->bind_param("isi", $catalog_id, $lot_number, $template_id);
                    
                    if (!$create_lot_stmt->execute()) {
                        throw new Exception("Failed to create lot: {$lot_number}");
                    }
                    $create_lot_stmt->close();
                } else {
                    // Update lot's template_id if it exists but has different template
                    $lot_info = $lot_check_result->fetch_assoc();
                    $lot_id = $lot_info['id'];
                    $current_lot_template_id = $lot_info['template_id'];
                    
                    if ($current_lot_template_id != $template_id) {
                        $update_lot_template_sql = "UPDATE lots SET template_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                        $update_lot_template_stmt = $conn->prepare($update_lot_template_sql);
                        $update_lot_template_stmt->bind_param("ii", $template_id, $lot_id);
                        $update_lot_template_stmt->execute();
                        $update_lot_template_stmt->close();
                    }
                }
                $lot_check_stmt->close();
                
                // Check if lot detail exists with all matching criteria
                $check_sql = "SELECT id FROM lot_details 
                             WHERE lot_number = ? 
                             AND template_id = ? 
                             AND section_id = ? 
                             AND `key` = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("siis", $lot_number, $template_id, $section_id, $key);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    // Record exists, update it
                    $row = $check_result->fetch_assoc();
                    $record_id = $row['id'];
                    
                    $update_sql = "UPDATE lot_details 
                                  SET `value` = ?, updated_at = CURRENT_TIMESTAMP 
                                  WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("si", $value, $record_id);
                    
                    if ($update_stmt->execute()) {
                        $updated_count++;
                        $saved_count++;
                    } else {
                        throw new Exception("Failed to update lot detail for key: {$key}");
                    }
                    $update_stmt->close();
                } else {
                    // Record doesn't exist, insert new
                    $insert_sql = "INSERT INTO lot_details 
                                  (lot_number, template_id, section_id, `key`, `value`) 
                                  VALUES (?, ?, ?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->bind_param("siiss", $lot_number, $template_id, $section_id, $key, $value);
                    
                    if ($insert_stmt->execute()) {
                        $inserted_count++;
                        $saved_count++;
                    } else {
                        throw new Exception("Failed to insert lot detail for key: {$key}");
                    }
                    $insert_stmt->close();
                }
                $check_stmt->close();
                
            } elseif ($source === 'lot' && empty($lot_number)) {
                throw new Exception("Lot number required for lot data: {$key}");
            }
            
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
    
    // Check if we had any errors
    if (empty($errors)) {
        // Commit transaction
        $conn->commit();
        $conn->autocommit(true);
        
        $message = "Successfully saved all {$saved_count} key-value pairs";
        if ($updated_count > 0 || $inserted_count > 0) {
            $message .= " ({$updated_count} updated, {$inserted_count} new)";
        }
        
        echo json_encode([
            'success' => true,
            'message' => $message,
            'saved_count' => $saved_count,
            'updated_count' => $updated_count,
            'inserted_count' => $inserted_count
        ]);
    } else {
        // Rollback transaction
        $conn->rollback();
        $conn->autocommit(true);
        
        // Return partial success with errors
        if ($saved_count > 0) {
            throw new Exception("Saved {$saved_count} items but encountered errors: " . implode('; ', $errors));
        } else {
            throw new Exception("Failed to save any items: " . implode('; ', $errors));
        }
    }
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn)) {
        $conn->rollback();
        $conn->autocommit(true);
    }
    
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'saved_count' => isset($saved_count) ? $saved_count : 0,
        'debug_info' => [
            'catalog_id' => $catalog_id,
            'template_id' => isset($template_id) ? $template_id : null,
            'lot_number' => isset($lot_number) ? $lot_number : null
        ]
    ]);
} finally {
    // Close connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>