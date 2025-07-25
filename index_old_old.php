<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Generator - Setup Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <?php
        require_once 'config/database.php';
        
        // Test database connection and display results
        testDBConnection();
        
        // Test sample queries
        echo "<hr>";
        echo "<h3>Sample Data:</h3>";
        
        // Get catalogs
        $result = executeQuery("SELECT * FROM catalogs");
        $catalogs = fetchAllAssoc($result);
        
        if (!empty($catalogs)) {
            echo "<h4>Catalogs:</h4>";
            echo "<ul>";
            foreach ($catalogs as $catalog) {
                echo "<li>ID: {$catalog['id']}, Catalog Number: {$catalog['catalog_number']}</li>";
            }
            echo "</ul>";
        }
        
        // Get sections
        $result = executeQuery("SELECT * FROM sections ORDER BY default_order");
        $sections = fetchAllAssoc($result);
        
        if (!empty($sections)) {
            echo "<h4>Sections:</h4>";
            echo "<ul>";
            foreach ($sections as $section) {
                echo "<li>ID: {$section['id']}, Name: {$section['section_name']}, Order: {$section['default_order']}</li>";
            }
            echo "</ul>";
        }
        
        // Get catalog details
        $result = executeQuery("SELECT cd.*, s.section_name FROM catalog_details cd JOIN sections s ON cd.section_id = s.id ORDER BY s.default_order");
        $catalogDetails = fetchAllAssoc($result);
        
        if (!empty($catalogDetails)) {
            echo "<h4>Catalog Details:</h4>";
            echo "<div class='table-responsive'>";
            echo "<table class='table table-striped'>";
            echo "<thead><tr><th>Section</th><th>Key</th><th>Value</th></tr></thead>";
            echo "<tbody>";
            foreach ($catalogDetails as $detail) {
                echo "<tr>";
                echo "<td>{$detail['section_name']}</td>";
                echo "<td>{$detail['key']}</td>";
                echo "<td>" . htmlspecialchars($detail['value']) . "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
            echo "</div>";
        }
        
        // Get lots
        $result = executeQuery("SELECT l.*, s.section_name FROM lots l JOIN sections s ON l.section_id = s.id ORDER BY l.lot_number, s.default_order");
        $lots = fetchAllAssoc($result);
        
        if (!empty($lots)) {
            echo "<h4>Lot Details:</h4>";
            echo "<div class='table-responsive'>";
            echo "<table class='table table-striped'>";
            echo "<thead><tr><th>Lot Number</th><th>Section</th><th>Key</th><th>Value</th></tr></thead>";
            echo "<tbody>";
            foreach ($lots as $lot) {
                echo "<tr>";
                echo "<td>{$lot['lot_number']}</td>";
                echo "<td>{$lot['section_name']}</td>";
                echo "<td>{$lot['key']}</td>";
                echo "<td>" . htmlspecialchars($lot['value']) . "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
            echo "</div>";
        }
        
        // Close connection
        closeDBConnection();
        ?>
        
        <hr>
        <div class="alert alert-info">
            <h4>Next Steps:</h4>
            <ul>
                <li>✓ Database connection is working</li>
                <li>✓ Sample data is loaded</li>
                <li>⏳ Create main PDF generator interface</li>
                <li>⏳ Add TCPDF integration</li>
                <li>⏳ Build dynamic form interface</li>
            </ul>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>