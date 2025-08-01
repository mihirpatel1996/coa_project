<?php
// api/check_pdf.php
// Clean PDF system check without connection issues

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PDF Generation System Check</h1>";

// Store results
$all_good = true;

// 1. Check TCPDF
echo "<h2>1. TCPDF Check</h2>";
if (file_exists('../vendor/autoload.php')) {
    require_once '../vendor/autoload.php';
    if (class_exists('TCPDF')) {
        echo "<p style='color: green;'>✓ TCPDF is installed</p>";
    } else {
        echo "<p style='color: red;'>✗ TCPDF not found</p>";
        $all_good = false;
    }
} else {
    echo "<p style='color: red;'>✗ Vendor autoload missing - run: composer install</p>";
    $all_good = false;
}

// 2. Check images
echo "<h2>2. Required Images</h2>";
$images = [
    'images/signalchem_sino_logo.png' => 'Logo',
    'images/signature.jpg' => 'Signature'
];

foreach ($images as $path => $name) {
    if (file_exists(__DIR__ . '/' . $path)) {
        echo "<p style='color: green;'>✓ $name found</p>";
    } else {
        echo "<p style='color: red;'>✗ $name missing</p>";
        echo "<p>→ Run: <a href='images/create_images.php'>Create Images</a></p>";
        $all_good = false;
    }
}

// 3. Test with sample data
echo "<h2>3. Sample Data Test</h2>";
try {
    require_once '../config/database.php';
    require_once '../config/templates_config.php';
    require_once 'pdf_common.php';
    
    $conn = getDBConnection();
    
    // Get sample data
    $sql = "SELECT c.catalogNumber, c.catalogName, l.lotNumber 
            FROM catalogs c 
            JOIN lots l ON c.catalogNumber = l.catalogNumber 
            WHERE c.catalogName IS NOT NULL AND c.catalogName != ''
            LIMIT 3";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✓ Found test data:</p>";
        echo "<table border='1' cellpadding='5' style='margin: 10px 0;'>";
        echo "<tr><th>Catalog Number</th><th>Catalog Name</th><th>Lot Number</th><th>Actions</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['catalogNumber']) . "</td>";
            echo "<td>" . htmlspecialchars($row['catalogName']) . "</td>";
            echo "<td>" . htmlspecialchars($row['lotNumber']) . "</td>";
            echo "<td>";
            echo "<a href='preview_pdf.php?catalog_number=" . urlencode($row['catalogNumber']) . 
                 "&lot_number=" . urlencode($row['lotNumber']) . "' target='_blank'>Preview</a> | ";
            echo "<a href='generate_pdf.php?catalog_number=" . urlencode($row['catalogNumber']) . 
                 "&lot_number=" . urlencode($row['lotNumber']) . "'>Download</a>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Test PDF generation with first record
        $result->data_seek(0);
        $test_row = $result->fetch_assoc();
        
        echo "<p>Testing PDF generation with: " . $test_row['catalogNumber'] . " / " . $test_row['lotNumber'] . "</p>";
        
        try {
            $data = getCoAData($test_row['catalogNumber'], $test_row['lotNumber']);
            $errors = validateAllFields($data['catalog'], $data['lot'], $data['template_code']);
            
            if (empty($errors)) {
                echo "<p style='color: green;'>✓ Data validation passed</p>";
                
                // Try to generate PDF
                $pdf = generatePDF($data['catalog'], $data['lot'], $data['template_code']);
                $pdf_content = $pdf->Output('', 'S');
                
                if (strlen($pdf_content) > 10000) {
                    echo "<p style='color: green;'>✓ PDF generation successful (" . number_format(strlen($pdf_content)) . " bytes)</p>";
                } else {
                    echo "<p style='color: orange;'>⚠ PDF seems too small</p>";
                }
            } else {
                echo "<p style='color: orange;'>⚠ Missing fields: " . implode(', ', $errors) . "</p>";
                $all_good = false;
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Generation error: " . $e->getMessage() . "</p>";
            $all_good = false;
        }
        
    } else {
        echo "<p style='color: red;'>✗ No test data found</p>";
        $all_good = false;
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    $all_good = false;
}

// Summary
echo "<hr>";
echo "<h2>Summary</h2>";
if ($all_good) {
    echo "<p style='color: green; font-size: 1.2em;'>✓ All systems operational! PDF generation should work.</p>";
    echo "<p>Try the test links above or go back to the <a href='../'>main application</a>.</p>";
} else {
    echo "<p style='color: red; font-size: 1.2em;'>✗ Some issues found. Please fix the red items above.</p>";
}

// Quick fix for current generate_pdf.php
echo "<hr>";
echo "<h2>Quick Fix</h2>";
echo "<p>Your current generate_pdf.php has debug code that breaks PDF generation.</p>";
echo "<p>To fix it quickly, create a file called <strong>generate_pdf_clean.php</strong> with the clean code:</p>";
echo "<pre style='background: #f0f0f0; padding: 10px; overflow-x: auto;'>";
echo htmlspecialchars('<?php
// api/generate_pdf_clean.php
header(\'Access-Control-Allow-Origin: *\');
require_once \'pdf_common.php\';

try {
    $catalog_number = $_GET[\'catalog_number\'] ?? \'\';
    $lot_number = $_GET[\'lot_number\'] ?? \'\';
    
    if (empty($catalog_number) || empty($lot_number)) {
        throw new Exception(\'Catalog and lot numbers required\');
    }
    
    $data = getCoAData($catalog_number, $lot_number);
    $errors = validateAllFields($data[\'catalog\'], $data[\'lot\'], $data[\'template_code\']);
    
    if (!empty($errors)) {
        throw new Exception(\'Missing fields: \' . implode(\', \', $errors));
    }
    
    $pdf = generatePDF($data[\'catalog\'], $data[\'lot\'], $data[\'template_code\']);
    $filename = generateFilename($catalog_number, $lot_number);
    
    // Log generation
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("INSERT INTO pdf_generation_log (catalogNumber, lotNumber, templateCode) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $catalog_number, $lot_number, $data[\'template_code\']);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        // Silent fail
    }
    
    $pdf->Output($filename, \'D\');
    
} catch (Exception $e) {
    displayError($e->getMessage());
}
?>');
echo "</pre>";
echo "<p>Then update your index.php to use generate_pdf_clean.php instead of generate_pdf.php</p>";
?>