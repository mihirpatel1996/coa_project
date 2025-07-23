<?php
// api/save_catalog.php (FIXED FOR TEMPLATE SUPPORT)
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
    $catalog_name = isset($input['catalog_name']) ? trim($input['catalog_name']) : '';
    $catalog_number = isset($input['catalog_number']) ? trim($input['catalog_number']) : '';
    $template_id = isset($input['template_id']) ? intval($input['template_id']) : null;
    
    if (empty($catalog_name)) {
        throw new Exception('Catalog name is required');
    }
    
    if (empty($catalog_number)) {
        throw new Exception('Catalog number is required');
    }
    
    // Get database connection
    $conn = getDBConnection();
    
    // Get default template if none specified
    if (!$template_id) {
        $template_sql = "SELECT id FROM templates WHERE is_default = 1 LIMIT 1";
        $template_result = $conn->query($template_sql);
        
        if ($template_result && $template_result->num_rows > 0) {
            $template_row = $template_result->fetch_assoc();
            $template_id = $template_row['id'];
        }
    }
    
    // Check if catalog number already exists
    $check_sql = "SELECT id, catalog_name, template_id FROM catalogs WHERE catalog_number = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $catalog_number);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Catalog number exists, check if we need to update
        $existing_catalog = $check_result->fetch_assoc();
        $existing_id = $existing_catalog['id'];
        $existing_name = $existing_catalog['catalog_name'];
        $existing_template_id = $existing_catalog['template_id'];
        
        $needs_update = false;
        $update_fields = [];
        $update_values = [];
        $update_types = "";
        
        if (empty($existing_name) || $existing_name !== $catalog_name) {
            $needs_update = true;
            $update_fields[] = "catalog_name = ?";
            $update_values[] = $catalog_name;
            $update_types .= "s";
        }
        
        if ($template_id && $existing_template_id != $template_id) {
            $needs_update = true;
            $update_fields[] = "template_id = ?";
            $update_values[] = $template_id;
            $update_types .= "i";
        }
        
        if ($needs_update) {
            $update_sql = "UPDATE catalogs SET " . implode(", ", $update_fields) . " WHERE id = ?";
            $update_values[] = $existing_id;
            $update_types .= "i";
            
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param($update_types, ...$update_values);
            
            if ($update_stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'catalog_id' => $existing_id,
                    'message' => 'Catalog updated successfully',
                    'action' => 'updated'
                ]);
            } else {
                throw new Exception('Failed to update catalog');
            }
            
            $update_stmt->close();
        } else {
            // Catalog already exists with the same data
            echo json_encode([
                'success' => true,
                'catalog_id' => $existing_id,
                'message' => 'Catalog already exists',
                'action' => 'existing'
            ]);
        }
    } else {
        // Create new catalog
        $insert_sql = "INSERT INTO catalogs (catalog_name, catalog_number, template_id) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ssi", $catalog_name, $catalog_number, $template_id);
        
        if ($insert_stmt->execute()) {
            $catalog_id = $conn->insert_id;
            
            echo json_encode([
                'success' => true,
                'catalog_id' => $catalog_id,
                'message' => 'Catalog created successfully',
                'action' => 'created'
            ]);
        } else {
            throw new Exception('Failed to create catalog');
        }
        
        $insert_stmt->close();
    }
    
    $check_stmt->close();
    
} catch (Exception $e) {
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    // Close connections
    if (isset($conn)) {
        $conn->close();
    }
}
?>