<?php
// api/get_section_data.php (FIXED FOR YOUR CURRENT STRUCTURE)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../config/database.php';

try {
    // Get parameters
    $catalog_id = isset($_GET['catalog_id']) ? intval($_GET['catalog_id']) : 0;
    $lot_number = isset($_GET['lot_number']) ? trim($_GET['lot_number']) : '';
    
    if ($catalog_id <= 0) {
        throw new Exception('Valid catalog ID is required');
    }
    
    // Get database connection
    $conn = getDBConnection();
    
    // First, check what tables and columns exist
    $lots_structure = $conn->query("SHOW COLUMNS FROM lots");
    $lots_columns = [];
    while ($row = $lots_structure->fetch_assoc()) {
        $lots_columns[] = $row['Field'];
    }
    
    $sections_data = [];
    
    // Get all sections in order
    $sections_sql = "SELECT id, section_name FROM sections ORDER BY default_order";
    $sections_result = $conn->query($sections_sql);
    
    if ($sections_result && $sections_result->num_rows > 0) {
        while ($section = $sections_result->fetch_assoc()) {
            $section_id = $section['id'];
            $section_name = $section['section_name'];
            
            $key_values = [];
            
            // Get catalog data for this section (this should work)
            $catalog_sql = "SELECT `key`, `value`, `order`, 'catalog' as source 
                           FROM catalog_details 
                           WHERE catalog_id = ? AND section_id = ? 
                           ORDER BY `order`";
            $catalog_stmt = $conn->prepare($catalog_sql);
            $catalog_stmt->bind_param("ii", $catalog_id, $section_id);
            $catalog_stmt->execute();
            $catalog_result = $catalog_stmt->get_result();
            
            while ($row = $catalog_result->fetch_assoc()) {
                $key_values[] = [
                    'key' => $row['key'],
                    'value' => $row['value'],
                    'order' => $row['order'],
                    'source' => $row['source']
                ];
            }
            $catalog_stmt->close();
            
            // Handle lot data based on current table structure
            if (!empty($lot_number)) {
                if (in_array('key', $lots_columns) && in_array('value', $lots_columns) && in_array('section_id', $lots_columns)) {
                    // Old mixed structure - lots table has key-value pairs
                    $lot_sql = "SELECT `key`, `value`, `order`, 'lot' as source 
                               FROM lots 
                               WHERE catalog_id = ? AND section_id = ? AND lot_number = ? 
                               ORDER BY `order`";
                    $lot_stmt = $conn->prepare($lot_sql);
                    $lot_stmt->bind_param("iis", $catalog_id, $section_id, $lot_number);
                    $lot_stmt->execute();
                    $lot_result = $lot_stmt->get_result();
                    
                    while ($row = $lot_result->fetch_assoc()) {
                        $key_values[] = [
                            'key' => $row['key'],
                            'value' => $row['value'],
                            'order' => $row['order'],
                            'source' => $row['source']
                        ];
                    }
                    $lot_stmt->close();
                    
                } else {
                    // Current structure - lots table only has metadata
                    // Check if lot_details table exists
                    $lot_details_check = $conn->query("SHOW TABLES LIKE 'lot_details'");
                    
                    if ($lot_details_check->num_rows > 0) {
                        // New structure - get lot_id and query lot_details
                        $lot_id_sql = "SELECT id FROM lots WHERE catalog_id = ? AND lot_number = ?";
                        $lot_id_stmt = $conn->prepare($lot_id_sql);
                        $lot_id_stmt->bind_param("is", $catalog_id, $lot_number);
                        $lot_id_stmt->execute();
                        $lot_id_result = $lot_id_stmt->get_result();
                        
                        if ($lot_id_result->num_rows > 0) {
                            $lot_row = $lot_id_result->fetch_assoc();
                            $lot_id = $lot_row['id'];
                            
                            $lot_details_sql = "SELECT `key`, `value`, `order`, 'lot' as source 
                                               FROM lot_details 
                                               WHERE lot_id = ? AND section_id = ? 
                                               ORDER BY `order`";
                            $lot_details_stmt = $conn->prepare($lot_details_sql);
                            $lot_details_stmt->bind_param("ii", $lot_id, $section_id);
                            $lot_details_stmt->execute();
                            $lot_details_result = $lot_details_stmt->get_result();
                            
                            while ($row = $lot_details_result->fetch_assoc()) {
                                $key_values[] = [
                                    'key' => $row['key'],
                                    'value' => $row['value'],
                                    'order' => $row['order'],
                                    'source' => $row['source']
                                ];
                            }
                            $lot_details_stmt->close();
                        }
                        $lot_id_stmt->close();
                    } else {
                        // No lot_details table and lots table has no key-value pairs
                        // This means lot-specific data doesn't exist yet
                        // Just return catalog data (which we already have)
                    }
                }
            }
            
            // Sort by order
            usort($key_values, function($a, $b) {
                return $a['order'] - $b['order'];
            });
            
            $sections_data[] = [
                'section_id' => $section_id,
                'section_name' => $section_name,
                'key_values' => $key_values
            ];
        }
    }
    
    // Return sections data as JSON with debug info
    echo json_encode([
        'sections_data' => $sections_data,
        'debug_info' => [
            'catalog_id' => $catalog_id,
            'lot_number' => $lot_number,
            'lots_table_columns' => $lots_columns,
            'lot_details_table_exists' => ($conn->query("SHOW TABLES LIKE 'lot_details'")->num_rows > 0)
        ]
    ]);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Error fetching section data: ' . $e->getMessage(),
        'debug_info' => [
            'catalog_id' => isset($catalog_id) ? $catalog_id : 'not set',
            'lot_number' => isset($lot_number) ? $lot_number : 'not set'
        ]
    ]);
} finally {
    // Close connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>