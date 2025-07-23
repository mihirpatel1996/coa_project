<?php
// api/add_template_key.php
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

    $template_id = isset($input['template_id']) ? trim($input['template_id']) : '';
    $section_id = isset($input['section_id']) ? trim($input['section_id']) : '';
    $key_name = isset($input['key_name']) ? trim($input['key_name']) : '';
    $key_source = isset($input['key_source']) ? trim($input['key_source']) : '';

    if (empty($template_id) || empty($section_id) || empty($key_name) || empty($key_source)) {
        throw new Exception('Missing required parameters');
    }

    $conn = getDBConnection();

    $insert_sql = "INSERT INTO template_keys (template_id, section_id, key_name, key_source) VALUES (?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iiss", $template_id, $section_id, $key_name, $key_source);

    if ($insert_stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Template key added successfully'
        ]);
    } else {
        throw new Exception('Failed to add template key');
    }

    $insert_stmt->close();

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