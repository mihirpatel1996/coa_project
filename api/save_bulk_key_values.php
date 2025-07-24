<?php
// api/save_bulk_key_values.php
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
    
    $catalog_id = $input['catalog_id'] ?? 0;
    $lot_number = $input['lot_number'] ?? '';
    $key_values = $input['key_values'] ?? [];
    
    if ($catalog_id <= 0) {
        throw new Exception('Valid catalog ID is required');
    }
    
    if (empty($key_values)) {
        throw new Exception('No key-value pairs to save');
    }
    
    $conn = getDBConnection();
    
    // Get catalog info
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
    
    // Start transaction
    $conn->autocommit(false);
    
    $saved_count = 0;
    $errors = [];
    
    foreach ($key_values as $item) {
        $section_id = $item['section_id'];
        $key = $item['key'];
        $value = $item['value'];
        $source = $item['source'];
        
        try {
            if ($source === 'catalog') {
                // Upsert catalog_details
                $upsert_sql = "INSERT INTO catalog_details (catalog_number, template_id, section_id, `key`, `value`) 
                               VALUES (?, ?, ?, ?, ?) 
                               ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), updated_at = CURRENT_TIMESTAMP";
                $stmt = $conn->prepare($upsert_sql);
                $stmt->bind_param("siiss", $catalog_number, $template_id, $section_id, $key, $value);
                $stmt->execute();
                $stmt->close();
                $saved_count++;
                
            } elseif ($source === 'lot' && !empty($lot_number)) {
                // Check if lot exists
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
                    $create_lot_stmt->close();
                }
                $lot_check_stmt->close();
                
                // Upsert lot_details
                $upsert_sql = "INSERT INTO lot_details (lot_number, template_id, section_id, `key`, `value`) 
                               VALUES (?, ?, ?, ?, ?) 
                               ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), updated_at = CURRENT_TIMESTAMP";
                $stmt = $conn->prepare($upsert_sql);
                $stmt->bind_param("siiss", $lot_number, $template_id, $section_id, $key, $value);
                $stmt->execute();
                $stmt->close();
                $saved_count++;
            }
        } catch (Exception $e) {
            $errors[] = "Error saving {$key}: " . $e->getMessage();
        }
    }
    
    if (empty($errors)) {
        $conn->commit();
        echo json_encode([
            'success' => true,
            'message' => "Successfully saved {$saved_count} key-value pairs",
            'saved_count' => $saved_count
        ]);
    } else {
        $conn->rollback();
        throw new Exception(implode(', ', $errors));
    }
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->autocommit(true);
        $conn->close();
    }
}
?>