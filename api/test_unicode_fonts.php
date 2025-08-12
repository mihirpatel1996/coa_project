<?php
// test_unicode_fonts.php
// Place this file in your /api/ directory and access it directly

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../vendor/autoload.php';

echo "<h1>TCPDF Unicode Font Test</h1>";

// Test string with special characters
$test_string = 'Test: 的µ®αβ‑""γɛⅤ：κθ，→（）';

echo "<h2>1. Checking TCPDF Version</h2>";
if (defined('TCPDF_VERSION')) {
    echo "<p>TCPDF Version: " . TCPDF_VERSION . "</p>";
} else {
    echo "<p style='color: red;'>TCPDF version not found!</p>";
}

echo "<h2>2. Testing Font Files</h2>";

// Get TCPDF fonts path
// $tcpdf_fonts_path = TCPDF_FONTS_PATH;
$tcpdf_fonts_path = dirname(__FILE__) . '/../vendor/tecnickcom/tcpdf/fonts/';
echo "<p>TCPDF Fonts Path: " . $tcpdf_fonts_path . "</p>";

// Check if Unicode fonts exist
$unicode_fonts = [
    'helvetica' => 'helvetica.php',
    'freeserif' => 'freeserif.php',
    'freesans' => 'freesans.php',
    'dejavusans' => 'dejavusans.php'
];

foreach ($unicode_fonts as $font => $file) {
    $font_file = $tcpdf_fonts_path . $file;
    if (file_exists($font_file)) {
        echo "<p style='color: green;'>✓ Font '$font' found at: $font_file</p>";
    } else {
        echo "<p style='color: red;'>✗ Font '$font' NOT found at: $font_file</p>";
    }
}

echo "<h2>3. Testing PDF Generation</h2>";

try {
    // Create new PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('Unicode Test');
    $pdf->SetTitle('Unicode Font Test');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(TRUE, 15);
    
    // Add a page
    $pdf->AddPage();
    
    // Test each font
    $y_position = 20;
    
    foreach ($unicode_fonts as $font => $file) {
        try {
            $pdf->SetFont($font, '', 12);
            $pdf->SetY($y_position);
            $pdf->Write(0, "Font: $font");
            $pdf->Ln();
            $pdf->Write(0, $test_string);
            $pdf->Ln(10);
            $y_position += 20;
            echo "<p style='color: green;'>✓ Font '$font' works in PDF</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Font '$font' error: " . $e->getMessage() . "</p>";
        }
    }
    
    // Output PDF to browser
    $pdf_content = $pdf->Output('unicode_test.pdf', 'S');
    
    // Save to file for inspection
    file_put_contents('unicode_test.pdf', $pdf_content);
    echo "<p style='color: green;'>✓ PDF generated successfully!</p>";
    echo "<p><a href='unicode_test.pdf' target='_blank'>Download Test PDF</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ PDF generation error: " . $e->getMessage() . "</p>";
}

echo "<h2>4. Testing Character Encoding</h2>";

// Test string encoding
echo "<p>Original string: " . htmlspecialchars($test_string) . "</p>";
echo "<p>UTF-8 encoded: " . (mb_check_encoding($test_string, 'UTF-8') ? 'Yes' : 'No') . "</p>";
echo "<p>String length: " . mb_strlen($test_string, 'UTF-8') . " characters</p>";

// Test individual characters
echo "<h3>Individual character codes:</h3>";
echo "<pre>";
$chars = mb_str_split($test_string);
foreach ($chars as $char) {
    $code = mb_ord($char);
    echo "Character: '$char' - Unicode: U+" . sprintf('%04X', $code) . " - UTF-8: " . bin2hex($char) . "\n";
}
echo "</pre>";

echo "<h2>5. Alternative Solution</h2>";
echo "<p>If Unicode fonts are not working, you can try:</p>";
echo "<ol>";
echo "<li>Download the full TCPDF package with fonts from <a href='https://github.com/tecnickcom/TCPDF/releases'>TCPDF GitHub</a></li>";
echo "<li>Use TCPDF's addTTFfont() method to add custom Unicode fonts</li>";
echo "<li>Consider using a different PDF library like mPDF which has better Unicode support out of the box</li>";
echo "</ol>";

// Test if we can use HTML entities
echo "<h2>6. HTML Entity Test</h2>";
$html_test = 'Test: &#x7684;&#xB5;&#xAE;&#x3B1;&#x3B2;&#x2011;&#x201C;&#x201D;&#x3B3;&#x3B5;&#x2164;&#xFF1A;&#x3BA;&#x3B8;&#xFF0C;&#x2192;&#xFF08;&#xFF09;';
echo "<p>HTML entities: " . htmlspecialchars($html_test) . "</p>";
echo "<p>Decoded: " . html_entity_decode($html_test, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "</p>";
?>