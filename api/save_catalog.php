<?php
// api/save_catalog.php
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
    
    if (empty($catalog_name)) {
        throw new Exception('Catalog name is required');
    }
    
    if (empty($catalog_number)) {
        throw new Exception('Catalog number is required');
    }
    
    // Get database connection
    $conn = getDBConnection();
    
    // Check if catalog number already exists
    $check_sql = "SELECT id, catalog_name FROM catalogs WHERE catalog_number = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $catalog_number);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Catalog number exists, check if we need to update the name
        $existing_catalog = $check_result->fetch_assoc();
        $existing_id = $existing_catalog['id'];
        $existing_name = $existing_catalog['catalog_name'];
        
        if (empty($existing_name) || $existing_name !== $catalog_name) {
            // Update the catalog name if it's empty or different
            $update_sql = "UPDATE catalogs SET catalog_name = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $catalog_name, $existing_id);
            
            if ($update_stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'catalog_id' => $existing_id,
                    'message' => 'Catalog name updated successfully',
                    'action' => 'updated'
                ]);
            } else {
                throw new Exception('Failed to update catalog name');
            }
            
            $update_stmt->close();
        } else {
            // Catalog already exists with the same name
            echo json_encode([
                'success' => true,
                'catalog_id' => $existing_id,
                'message' => 'Catalog already exists',
                'action' => 'existing'
            ]);
        }
    } else {
        // Create new catalog
        $insert_sql = "INSERT INTO catalogs (catalog_name, catalog_number) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ss", $catalog_name, $catalog_number);
        
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
    
} catch (Exception $e) {
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    // Close connections
    if (isset($check_stmt)) {
        $check_stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>