<?php
// api/verify_unicode_coa.php - Test Unicode support in actual CoA PDFs

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Use the mPDF version
require_once 'pdf_common_mpdf.php';

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Verify Unicode in CoA PDFs</title></head><body>";
echo "<h1>Testing Unicode Support in CoA PDFs</h1>";

// Test string with your problematic characters
// $test_string = '的µ®αβ‑""γɛⅤ：κθ，→（）';
$test_string = 'µ®αβ‑“”γɛⅤ：κθ，→（）';

try {
    // Create test catalog data with special characters
    $test_catalog_data = [
        'catalogNumber' => 'TEST-001',
        'catalogName' => 'Test Product with Unicode: ' . $test_string,
        'templateCode' => 'SUB', // or whatever template you use
        // Add other required fields based on your template
        'description' => 'This product contains special characters: µg/mL, ±0.5%, pH 7.4',
        'source' => 'Greek letters: α, β, γ, δ, ε. Arrows: → ← ↑ ↓',
        'predictedMolecularMass' => '25.5 kDa (±0.5) ',
        'formulation' => 'Buffer at -20°C with 10µM concentration',
    ];
    
    $test_lot_data = [
        'lotNumber' => 'TEST-LOT-001',
        'catalogNumber' => 'TEST-001',
        'purity' => '≥95% as determined by SDS-PAGE',
        'concentration' => '1.5 µg/µL in buffer µ®αβ‑“”γɛⅤ：κθ，→（）',
        'formulation' => 'Contains special symbols: ® ™ © and temperature: -80°C',
        'reconstitution' => 'Reconstitute with H₂O to 1mg/mL (10⁻³ M)',
        'shipping' => 'Ships at ≤-20°C. Store at -80°C upon receipt.',
        'stabilityAndStorage' => 'Stable for ≥12 months at -80°C. Avoid freeze/thaw cycles.',
    ];
    
    // Generate test PDF
    echo "<h2>Generating Test PDF with Unicode Characters...</h2>";
    
    $mpdf = generatePDF($test_catalog_data, $test_lot_data, 'SUB');
    $filename = 'Unicode_Test_CoA_' . date('Ymd_His') . '.pdf';
    
    // Save to file
    $pdf_content = $mpdf->Output('', 'S');
    file_put_contents($filename, $pdf_content);
    
    echo "<p style='color: green;'>✓ PDF generated successfully!</p>";
    echo "<p><a href='$filename' target='_blank'>Download Test PDF</a> (" . number_format(strlen($pdf_content)) . " bytes)</p>";
    
    // Show what was included
    echo "<h3>Characters Tested:</h3>";
    echo "<ul>";
    echo "<li><strong>Your test string:</strong> $test_string</li>";
    echo "<li><strong>Greek letters:</strong> α, β, γ, δ, ε, θ, κ, μ</li>";
    echo "<li><strong>Mathematical:</strong> ±, ≥, ≤, µ, ×, ÷</li>";
    echo "<li><strong>Arrows:</strong> →, ←, ↑, ↓</li>";
    echo "<li><strong>Symbols:</strong> ®, ™, ©, °C</li>";
    echo "<li><strong>Subscripts:</strong> H₂O, CO₂ (or H<sub>2</sub>O)</li>";
    echo "<li><strong>Superscripts:</strong> 10⁻³, x² (or 10<sup>-3</sup>)</li>";
    echo "<li><strong>Chinese:</strong> 的</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Test with real data if available
echo "<h2>Test with Real Database Data</h2>";
try {
    $conn = getDBConnection();
    
    // Find catalogs that might have special characters
    $sql = "SELECT DISTINCT c.catalogNumber, c.catalogName, l.lotNumber 
            FROM catalogs c 
            JOIN lots l ON c.catalogNumber = l.catalogNumber 
            WHERE (
                c.catalogName LIKE '%µ%' OR 
                c.catalogName LIKE '%°%' OR 
                c.catalogName LIKE '%±%' OR 
                c.description LIKE '%µ%' OR
                c.description LIKE '%°%' OR
                l.formulation LIKE '%µ%' OR
                l.formulation LIKE '%°%'
            )
            LIMIT 5";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "<p>Found products with special characters:</p>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Catalog Number</th><th>Product Name</th><th>Lot Number</th><th>Action</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['catalogNumber']) . "</td>";
            echo "<td>" . htmlspecialchars($row['catalogName']) . "</td>";
            echo "<td>" . htmlspecialchars($row['lotNumber']) . "</td>";
            echo "<td><a href='generate_pdf_mpdf.php?catalog_number=" . urlencode($row['catalogNumber']) . 
                 "&lot_number=" . urlencode($row['lotNumber']) . "' target='_blank'>Generate PDF</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No products found with special characters in the database.</p>";
        
        // Show any products for testing
        $sql2 = "SELECT c.catalogNumber, c.catalogName, l.lotNumber 
                FROM catalogs c 
                JOIN lots l ON c.catalogNumber = l.catalogNumber 
                LIMIT 3";
        $result2 = $conn->query($sql2);
        
        if ($result2 && $result2->num_rows > 0) {
            echo "<p>Sample products for testing:</p>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Catalog Number</th><th>Product Name</th><th>Lot Number</th><th>Action</th></tr>";
            
            while ($row = $result2->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['catalogNumber']) . "</td>";
                echo "<td>" . htmlspecialchars($row['catalogName']) . "</td>";
                echo "<td>" . htmlspecialchars($row['lotNumber']) . "</td>";
                echo "<td><a href='generate_pdf_mpdf.php?catalog_number=" . urlencode($row['catalogNumber']) . 
                     "&lot_number=" . urlencode($row['lotNumber']) . "' target='_blank'>Generate PDF</a></td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "<p style='color: orange;'>Could not check database: " . $e->getMessage() . "</p>";
}

echo "<h2>Implementation Complete!</h2>";
echo "<p>Your pdf_common_mpdf.php now has the same Unicode configuration that worked in Test 2.</p>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li>Test the generated PDFs above to confirm special characters render correctly</li>";
echo "<li>If everything looks good, you can replace pdf_common.php with pdf_common_mpdf.php</li>";
echo "<li>Update any references from generate_pdf.php to use the mPDF version</li>";
echo "</ol>";

echo "</body></html>";
?>