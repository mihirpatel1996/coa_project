<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/templates_config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate required fields
    $catalog_number = isset($input['catalog_number']) ? trim($input['catalog_number']) : '';
    $catalog_name = isset($input['catalog_name']) ? trim($input['catalog_name']) : '';
    $lot_number = isset($input['lot_number']) ? trim($input['lot_number']) : '';
    $template_code = isset($input['template_code']) ? trim($input['template_code']) : '';
    $key_values = isset($input['key_values']) ? $input['key_values'] : [];
    
    // Validation
    if (empty($catalog_number)) {
        throw new Exception('Catalog number is required');
    }
    
    if (empty($catalog_name)) {
        throw new Exception('Catalog name is required');
    }
    
    // if (empty($lot_number)) {
    //     throw new Exception('Lot number is required');
    // }
    
    if (!isset(TEMPLATES[$template_code])) {
        throw new Exception('Invalid template code');
    }
    
    if (empty($key_values)) {
        throw new Exception('Key-value pairs are required');
    }
    
    $conn = getDBConnection();
    $conn->autocommit(false);
    
    try {
        // ADD TEMPLATE CHANGE DETECTION HERE
        // Check if template has changed
        $is_template_changed = false;
        $template_check_sql = "SELECT templateCode FROM catalogs WHERE catalogNumber = ?";
        $template_check_stmt = $conn->prepare($template_check_sql);
        $template_check_stmt->bind_param("s", $catalog_number);
        $template_check_stmt->execute();
        $template_result = $template_check_stmt->get_result();

        if ($template_result->num_rows > 0) {
            $existing = $template_result->fetch_assoc();
            $old_template_code = $existing['templateCode'];
            $is_template_changed = ($old_template_code !== $template_code);
        }
        $template_check_stmt->close();

        // If template changed, clear all existing data first
        if ($is_template_changed) {
            // Clear all field data from catalogs (keep only catalogNumber, catalogName, templateCode)
            $clear_catalog_sql = "UPDATE catalogs SET 
                source = NULL, predictedNTerminal = NULL, predictedMolMass = NULL, 
                observedMolMass = NULL, activity = NULL, shipping = NULL, 
                stability = NULL, detail = NULL, formulation = NULL, 
                reconstitution = NULL, cas = NULL, 
                templateCode = ?,
                updatedAt = CURRENT_TIMESTAMP
                WHERE catalogNumber = ?";
            $clear_catalog_stmt = $conn->prepare($clear_catalog_sql);
            $clear_catalog_stmt->bind_param("ss", $template_code, $catalog_number);
            $clear_catalog_stmt->execute();
            $clear_catalog_stmt->close();
            
            // Update all lots to new template and clear their field data
            $clear_lots_sql = "UPDATE lots SET 
                activity = NULL, concentration = NULL, 
                purity = NULL, formulation = NULL,
                templateCode = ?,
                updatedAt = CURRENT_TIMESTAMP
                WHERE catalogNumber = ?";
            $clear_lots_stmt = $conn->prepare($clear_lots_sql);
            $clear_lots_stmt->bind_param("ss", $template_code, $catalog_number);
            $clear_lots_stmt->execute();
            $clear_lots_stmt->close();
        }

        // Update catalog name
        $update_catalog_sql = "UPDATE catalogs SET catalogName = ? WHERE catalogNumber = ?";
        $update_catalog_stmt = $conn->prepare($update_catalog_sql);
        $update_catalog_stmt->bind_param("ss", $catalog_name, $catalog_number);
        $update_catalog_stmt->execute();
        $update_catalog_stmt->close();

        $lot_id = null;

        // Only process lot if lot_number is provided
        if (!empty($lot_number)) {
            // Check if lot exists, create if not
            $lot_check_sql = "SELECT id FROM lots WHERE catalogNumber = ? AND lotNumber = ?";
            $lot_check_stmt = $conn->prepare($lot_check_sql);
            $lot_check_stmt->bind_param("ss", $catalog_number, $lot_number);
            $lot_check_stmt->execute();
            $lot_check_result = $lot_check_stmt->get_result();
            
            if ($lot_check_result->num_rows === 0) {
                // Create lot
                $create_lot_sql = "INSERT INTO lots (catalogNumber, lotNumber, templateCode) VALUES (?, ?, ?)";
                $create_lot_stmt = $conn->prepare($create_lot_sql);
                $create_lot_stmt->bind_param("sss", $catalog_number, $lot_number, $template_code);
                $create_lot_stmt->execute();
                $lot_id = $conn->insert_id;
                $create_lot_stmt->close();
            } else {
                $lot_row = $lot_check_result->fetch_assoc();
                $lot_id = $lot_row['id'];
            }
            $lot_check_stmt->close();
        }
        
        // Build dynamic updates for catalog fields
        $catalog_fields = [];
        $catalog_values = [];
        $catalog_types = "";
        
        // Build dynamic updates for lot fields
        $lot_fields = [];
        $lot_values = [];
        $lot_types = "";
        
        // Process key-value pairs
        foreach ($key_values as $kv) {
            $key = trim($kv['key']);
            $value = trim($kv['value']);
            $source = $kv['source'];
            
            // Find the db_field name from TEMPLATE_FIELDS
            $db_field = null;
            foreach (TEMPLATE_FIELDS[$template_code] as $section_fields) {
                foreach ($section_fields as $field) {
                    if ($field['field_name'] === $key && $field['field_source'] === $source) {
                        $db_field = $field['db_field'];
                        break 2;
                    }
                }
            }
            
            if (!$db_field) {
                continue; // Skip if field not found in template
            }
            
            if ($source === 'catalog') {
                $catalog_fields[] = "`$db_field` = ?";
                $catalog_values[] = $value;
                $catalog_types .= "s";
            } elseif ($source === 'lot') {
                $lot_fields[] = "`$db_field` = ?";
                $lot_values[] = $value;
                $lot_types .= "s";
            }
        }
        
        // Update catalog fields if any
        if (!empty($catalog_fields)) {
            $catalog_values[] = $catalog_number;
            $catalog_types .= "s";
            
            $update_catalog_data_sql = "UPDATE catalogs SET " . implode(", ", $catalog_fields) . " WHERE catalogNumber = ?";
            $update_catalog_data_stmt = $conn->prepare($update_catalog_data_sql);
            $update_catalog_data_stmt->bind_param($catalog_types, ...$catalog_values);
            $update_catalog_data_stmt->execute();
            $update_catalog_data_stmt->close();
        }
        
        // Update lot fields if any
        if (!empty($lot_fields) && $lot_id !== null) {
            $lot_values[] = $lot_id;
            $lot_types .= "i";
            
            $update_lot_data_sql = "UPDATE lots SET " . implode(", ", $lot_fields) . " WHERE id = ?";
            $update_lot_data_stmt = $conn->prepare($update_lot_data_sql);
            $update_lot_data_stmt->bind_param($lot_types, ...$lot_values);
            $update_lot_data_stmt->execute();
            $update_lot_data_stmt->close();
        }
        
        // Commit transaction
        $conn->commit();
        $conn->autocommit(true);
        
        echo json_encode([
            'success' => true,
            'message' => 'All data saved successfully',
            'stats' => [
                'catalog_fields_updated' => count($catalog_fields),
                'lot_fields_updated' => count($lot_fields),
                'total_processed' => count($key_values)
            ],
            'lot_id' => $lot_id
        ]);
        
    } catch (Exception $e) {
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