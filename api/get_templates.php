<?php
require_once '../config/templates_config.php';

echo json_encode([
    'success' => true,
    'templates' => array_values(TEMPLATES)  // Convert to indexed array
]);
?>