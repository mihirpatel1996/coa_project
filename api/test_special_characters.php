<?php
// api/test_your_special_chars.php
// Test specifically for the characters: 的µ®αβ‑""γɛⅤ：κθ，→（）

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../vendor/autoload.php';

// Your problematic string
$your_string = '的µ®αβ‑""γɛⅤ：κθ，→（）';

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Special Character Test</title></head><body>";
echo "<h1>Testing Special Character Rendering</h1>";
echo "<p>Original string: $your_string</p>";

// Test 1: Basic mPDF
echo "<h2>Test 1: Basic mPDF with DejaVuSans</h2>";
try {
    $mpdf1 = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'default_font' => 'dejavusans',
        'default_font_size' => 12,
    ]);
    
    $html1 = '
    <html>
    <head><meta charset="UTF-8"></head>
    <body style="font-family: dejavusans;">
        <h1>Basic mPDF Test</h1>
        <p>Your string: ' . $your_string . '</p>
    </body>
    </html>';
    
    $mpdf1->WriteHTML($html1);
    $pdf1 = $mpdf1->Output('', 'S');
    
    echo "<p>✓ Basic mPDF generated (" . strlen($pdf1) . " bytes)</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Error: " . $e->getMessage() . "</p>";
}

// Test 2: mPDF with full Unicode support
echo "<h2>Test 2: mPDF with Full Unicode Support</h2>";
try {
    $mpdf2 = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'default_font' => 'dejavusans',
        'default_font_size' => 12,
        'useSubstitutions' => true,
        'useKerning' => true,
        'autoScriptToLang' => true,
        'autoLangToFont' => true,
        'backup_substitute_fonts' => ['dejavusans', 'freesans'],
    ]);
    
    $html2 = '
    <html>
    <head><meta charset="UTF-8"></head>
    <body>
        <h1>Full Unicode Support Test</h1>
        <table border="1" cellpadding="5" style="width: 100%;">
            <tr>
                <th>Character</th>
                <th>Description</th>
                <th>Unicode</th>
            </tr>
            <tr>
                <td>的</td>
                <td>Chinese character</td>
                <td>U+7684</td>
            </tr>
            <tr>
                <td>µ</td>
                <td>Micro sign</td>
                <td>U+00B5</td>
            </tr>
            <tr>
                <td>®</td>
                <td>Registered trademark</td>
                <td>U+00AE</td>
            </tr>
            <tr>
                <td>α β γ</td>
                <td>Greek letters</td>
                <td>U+03B1, U+03B2, U+03B3</td>
            </tr>
            <tr>
                <td>→</td>
                <td>Right arrow</td>
                <td>U+2192</td>
            </tr>
        </table>
        <p style="margin-top: 20px;"><strong>Full string:</strong> ' . $your_string . '</p>
    </body>
    </html>';
    
    $mpdf2->WriteHTML($html2);
    file_put_contents('test_unicode_full.pdf', $mpdf2->Output('', 'S'));
    
    echo "<p>✓ Full Unicode mPDF generated - <a href='test_unicode_full.pdf' target='_blank'>Download PDF</a></p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Error: " . $e->getMessage() . "</p>";
}

// Test 3: TCPDF comparison
echo "<h2>Test 3: TCPDF with Unicode Font</h2>";
if (class_exists('TCPDF')) {
    try {
        $pdf = new TCPDF();
        $pdf->SetFont('dejavusans', '', 12);
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(TRUE, 10);
        
        // Convert to HTML entities for TCPDF
        $tcpdf_string = htmlentities($your_string, ENT_QUOTES, 'UTF-8', false);
        
        $html3 = '
        <h1>TCPDF Test</h1>
        <p>Original: ' . $your_string . '</p>
        <p>With entities: ' . $tcpdf_string . '</p>
        ';
        
        $pdf->writeHTML($html3, true, false, true, false, '');
        file_put_contents('test_unicode_tcpdf.pdf', $pdf->Output('', 'S'));
        
        echo "<p>✓ TCPDF generated - <a href='test_unicode_tcpdf.pdf' target='_blank'>Download PDF</a></p>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>✗ Error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color:orange;'>⚠ TCPDF not available for comparison</p>";
}

// Character analysis
echo "<h2>Character Analysis</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Position</th><th>Character</th><th>Unicode</th><th>HTML Entity</th></tr>";

$chars = mb_str_split($your_string);
foreach ($chars as $i => $char) {
    $unicode = 'U+' . str_pad(dechex(mb_ord($char)), 4, '0', STR_PAD_LEFT);
    $entity = htmlentities($char, ENT_HTML5, 'UTF-8');
    echo "<tr>";
    echo "<td>$i</td>";
    echo "<td>$char</td>";
    echo "<td>$unicode</td>";
    echo "<td>" . ($entity !== $char ? $entity : '-') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Recommendations
echo "<h2>Recommendations</h2>";
echo "<ol>";
echo "<li><strong>Use mPDF with Unicode settings</strong> - It handles these characters better than TCPDF</li>";
echo "<li><strong>Ensure UTF-8 throughout</strong> - Database, PHP files, and HTML must all use UTF-8</li>";
echo "<li><strong>Use DejaVuSans font</strong> - It has good Unicode coverage</li>";
echo "<li><strong>Enable substitutions</strong> - Allows mPDF to find alternative fonts for missing glyphs</li>";
echo "</ol>";

echo "<h2>Quick Fix Code</h2>";
echo "<pre style='background: #f0f0f0; padding: 10px;'>";
echo htmlspecialchars('
// In your generatePDF function:
$mpdf = new \Mpdf\Mpdf([
    \'mode\' => \'utf-8\',
    \'format\' => \'A4\',
    \'default_font\' => \'dejavusans\',
    \'useSubstitutions\' => true,
    \'autoScriptToLang\' => true,
    \'autoLangToFont\' => true,
]);
');
echo "</pre>";

echo "</body></html>";

// Also create a direct download test
if (isset($_GET['download'])) {
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'default_font' => 'dejavusans',
        'useSubstitutions' => true,
        'autoScriptToLang' => true,
        'autoLangToFont' => true,
    ]);
    
    $html = '<h1>Special Characters: ' . $your_string . '</h1>';
    $mpdf->WriteHTML($html);
    $mpdf->Output('special_chars_test.pdf', 'D');
    exit;
}
?>