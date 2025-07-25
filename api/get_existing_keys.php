<?php
// api/get_existing_keys.php (UPDATED WITHOUT section_id)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

try {
    // Get parameters
    $template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : 0;
    $section_id = isset($_GET['section_id']) ? intval($_GET['section_id']) : 0;
    $source = isset($_GET['source']) ? trim($_GET['source']) : '';
    
    if ($template_id <= 0) {
        throw new Exception('Valid template ID is required');
    }
    
    if ($section_id <= 0) {
        throw new Exception('Valid section ID is required');
    }
    
    if (!in_array($source, ['catalog', 'lot'])) {
        throw new Exception('Valid source is required (catalog or lot)');
    }
    
    $conn = getDBConnection();
    
    $keys = [];
    
    if ($source === 'catalog') {
        // Get unique keys from catalog_details that belong to this section via template_keys
        $sql = "SELECT DISTINCT cd.`key` 
                FROM catalog_details cd
                JOIN template_keys tk ON cd.template_id = tk.template_id 
                                     AND cd.`key` = tk.key_name
                WHERE tk.section_id = ? AND tk.template_id = ?
                ORDER BY cd.`key`";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $section_id, $template_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $keys[] = $row['key'];
        }
        $stmt->close();
        
    } elseif ($source === 'lot') {
        // Get unique keys from lot_details that belong to this section via template_keys
        $sql = "SELECT DISTINCT ld.`key` 
                FROM lot_details ld
                JOIN template_keys tk ON ld.template_id = tk.template_id 
                                     AND ld.`key` = tk.key_name
                WHERE tk.section_id = ? AND tk.template_id = ?
                ORDER BY ld.`key`";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $section_id, $template_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $keys[] = $row['key'];
        }
        $stmt->close();
    }
    
    // Return keys as JSON
    echo json_encode([
        'success' => true,
        'keys' => $keys,
        'debug_info' => [
            'template_id' => $template_id,
            'section_id' => $section_id,
            'source' => $source,
            'keys_found' => count($keys)
        ]
    ]);
    
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