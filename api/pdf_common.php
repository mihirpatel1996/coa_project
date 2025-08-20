<?php
// api/pdf_common.php - mPDF with Hybrid Approach

// Required files
require_once '../vendor/autoload.php';
require_once '../config/database.php';
require_once '../config/templates_config.php';

// Import mPDF
use Mpdf\Mpdf;

/**
 * Load HTML template from file
 */
// function loadHTMLTemplate($templateFile = 'coa_template_new.html') {
//     $templatePath = __DIR__ . '/coa_templates/' . $templateFile;
    
//     if (!file_exists($templatePath)) {
//         throw new Exception("Template file not found: $templateFile");
//     }
    
//     $html = file_get_contents($templatePath);
    
//     return $html;
// }

/**
 * Generate HTML content for a section
 */
function generateSectionHTML($sectionId, $catalogData, $lotData, $templateCode) {
    // Return empty - we'll use direct rendering instead
    return '';
}

/**
 * Format field values
 */
function formatFieldValue($field_name, $value) {
    // First, ensure we're working with a string
    $value = (string) $value;
    
    // Handle line breaks
    $value = str_replace("\n", "<br />", $value);
    
    // Handle special formatting based on field name
    $field_lower = strtolower($field_name);
    
    switch($field_lower) {
        case 'molecular weight':
        case 'molecular mass':
        case 'predicted molecular mass':
            // Handle subscripts in chemical formulas
            $value = preg_replace('/H2O/', 'H<sub>2</sub>O', $value);
            $value = preg_replace('/CO2/', 'CO<sub>2</sub>', $value);
            $value = preg_replace('/H2/', 'H<sub>2</sub>', $value);
            $value = preg_replace('/O2/', 'O<sub>2</sub>', $value);
            break;
            
        case 'storage':
        case 'stability & storage':
        case 'shipping':
            // Handle temperature symbols
            $value = preg_replace('/(-?\d+)\s*°?\s*C\b/', '$1°C', $value);
            $value = str_replace('degrees C', '°C', $value);
            break;
            
        case 'concentration':
        case 'purity':
            // Ensure percentages are properly formatted
            $value = preg_replace('/(\d+\.?\d*)\s*%/', '$1%', $value);
            break;
            
        case 'ph':
            // Format pH properly
            $value = str_replace('pH', 'pH', $value);
            break;
    }
    
    // Handle superscripts for common notations
    $value = preg_replace('/10\^(-?\d+)/', '10<sup>$1</sup>', $value);
    
    // Clean up any double spaces
    $value = preg_replace('/\s+/', ' ', $value);
    
    return trim($value);
}

/**
 * Replace placeholders in template
 */
function replacePlaceholders($html, $catalogData, $lotData, $templateCode) {
    // We'll handle these directly in the PDF
    return $html;
}

/**
 * Render fields as HTML table
 */
function renderFieldsAsHTML($fields, $catalogData, $lotData) {
    $html = '<table width="100%" cellpadding="2" cellspacing="0" border="0">';
    $hasContent = false;
    
    foreach ($fields as $field_config) {
        $field_name = $field_config['field_name'];
        $db_field = $field_config['db_field'];
        $source = $field_config['field_source'];
        
        // Get value from appropriate source
        $value = '';
        if ($source === 'catalog' && isset($catalogData[$db_field])) {
            $value = $catalogData[$db_field];
        } elseif ($source === 'lot' && isset($lotData[$db_field])) {
            $value = $lotData[$db_field];
        }
        
        // Skip empty values
        if (empty($value) || trim($value) === '') {
            continue;
        }
        
        $hasContent = true;
        
        // Format value
        $value = formatFieldValue($field_name, $value);
        
        // Add row
        $html .= '<tr style="page-break-inside: auto;">';
        $html .= '<td width="30%" style="vertical-align: top; padding: 2px 0;">' . htmlspecialchars($field_name) . ':</td>';
        $html .= '<td width="70%" style="vertical-align: top; padding: 2px 0;">' . $value . '</td>';
        $html .= '</tr>';
    }
    
    if (!$hasContent) {
        $html .= '<tr><td colspan="2" style="font-style: italic;">No data available for this section.</td></tr>';
    }
    
    $html .= '</table>';
    
    return $html;
}

/**
 * Generate PDF object using mPDF
 */
function generatePDF($catalog_data, $lot_data, $template_code) {
    // Create new mPDF document with full Unicode support
    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 10,
        'margin_bottom' => 25,
        'margin_header' => 0,
        'margin_footer' => 10,
        'default_font_size' => 10,
        // 'default_font' => 'dejavusans',
        'default_font' => 'helvetica',
        'useSubstitutions' => true,
        'useKerning' => true,
        'autoScriptToLang' => true,
        'autoLangToFont' => true,
        // 'backup_substitute_fonts' => ['dejavusans', 'freesans'],
        'backup_substitute_fonts' => ['helvetica', 'freesans'],
        'tempDir' => sys_get_temp_dir() . '/mpdf'
    ]);
    
    // Set document information
    $mpdf->SetTitle('Certificate of Analysis - ' . $catalog_data['catalogNumber']);
    $mpdf->SetAuthor('Sino Biological');
    $mpdf->SetSubject('Certificate of Analysis');
    $mpdf->SetKeywords('CoA, Certificate, Analysis, ' . $catalog_data['catalogNumber']);

    // 8. PDF Footer (not HTML footer)
    $footerHtml = '
        <div style="text-align: center; font-size: 8pt; color: #333; margin-top: 3mm; border-top: 1px solid #333; padding-top: 3mm;">
            Tel: +86-400-890-9989 (Global), +1-215-583-7898 (USA), +49(0)6196 9678656 (Europe)&nbsp;&nbsp;&nbsp;
            Website: www.sinobiological.com
        </div>
    ';
    $mpdf->SetHTMLFooter($footerHtml);
    
    // Build complete HTML document
    $html = '
    <style>
        /* body { font-family: dejavusans, Arial, sans-serif; }*/
        body { font-family: helvetica, Arial, sans-serif; }
        .product-title { font-size: 16pt; font-weight: bold; margin-bottom: 5px; }
        .catalog-info { font-size: 10pt; margin-bottom: 2px; }
        .certificate-title { font-size: 14pt; font-weight: bold; text-align: center; margin: 10px 0 20px 0; }
        .section-header { background-color: #f0f0f0; font-size: 12pt; font-weight: bold; padding: 5px; margin-bottom: 10px; }
        .last-section { margin-top: 30px; }
        .disclaimer { line-height: 1.3; margin-bottom: 10px; }
        .contact-info { margin-bottom: 15px; }
        .contact-info strong { font-weight: bold; }
        .signature-section { margin: 20px 0; }
        .signature-image { height: 40px; margin-bottom: 5px; }
        .signature-name { font-weight: bold; font-size: 10pt; margin-bottom: 3px; }
        .signature-title { color: #333333; }
    </style>';
    
    // 1. Header with logo
    $html .= '
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="left">
                <img src="images/signalchem_sino_logo.png" alt="SinoBiological SignalChem Logo" height="55" />
            </td>
        </tr>
    </table>
    <br/>';
    
    // 2. Product info
    $html .= '
    <div class="product-title">' . htmlspecialchars($catalog_data['catalogName']) . '</div>
    <div class="catalog-info"><strong>Catalog Number:</strong> ' . htmlspecialchars($catalog_data['catalogNumber']) . '</div>
    <div class="catalog-info"><strong>Lot Number:</strong> ' . htmlspecialchars($lot_data['lotNumber'] ?? '') . '</div>';
    
    // 3. Certificate title with line
    $html .= '
    <hr style="border: 0; border-top: 1px solid #cccccc; margin: 15px 0;">
    <div class="certificate-title" style="text-align: center;">Certificate of Analysis</div>';
    
    // 4. Description section
    $html .= '<div class="section-header">Description</div>';
    if (isset(TEMPLATE_FIELDS[$template_code][1])) {
        $html .= renderFieldsAsHTML(TEMPLATE_FIELDS[$template_code][1], $catalog_data, $lot_data);
    }
    $html .= '<br/>';
    
    // 5. Specifications section
    $html .= '<div class="section-header">Specifications</div>';
    if (isset(TEMPLATE_FIELDS[$template_code][2])) {
        $html .= renderFieldsAsHTML(TEMPLATE_FIELDS[$template_code][2], $catalog_data, $lot_data);
    }
    $html .= '<br/>';
    
    // 6. Preparation and Storage section
    $html .= '<div class="section-header">Preparation and Storage</div>';
    if (isset(TEMPLATE_FIELDS[$template_code][3])) {
        $html .= renderFieldsAsHTML(TEMPLATE_FIELDS[$template_code][3], $catalog_data, $lot_data);
    }

    // 7. Footer section HTML
    $html .= '<div class="last-section">
    <div class="disclaimer">
        The products are not to be used in humans. In the absence of any express written agreement to the contrary, products sold by SINO BIOLOGICAL, INC. are for research-use-only (RUO).
    </div>
    
    <div class="contact-info">
        If you have any further questions, please contact Technical Services at <strong>support@sinobiological.com</strong>
    </div>
    
    <div class="signature-section">
        <img src="images/signature.jpg" alt="Signature" class="signature-image" />
        <div class="signature-name">Donna Morrison, PhD</div>
        <div class="signature-title">Quality Assurance, SignalChem Biotech / Sino Biological</div>
    </div>
    </div>';

    // Write main content
    $mpdf->WriteHTML($html);
    
    return $mpdf;
}

/**
 * Validate all required fields exist
 */
function validateAllFields($catalogData, $lotData, $templateCode) {
    $errors = [];
    
    if (!isset(TEMPLATE_FIELDS[$templateCode])) {
        return ["Template '$templateCode' not found"];
    }
    
    foreach (TEMPLATE_FIELDS[$templateCode] as $section_name => $fields) {
        foreach ($fields as $field_config) {
            $field_name = $field_config['field_name'];
            $db_field = $field_config['db_field'];
            $source = $field_config['field_source'];
            // $required = $field_config['required'] ?? false;
            
            // if (!$required) continue;
            
            $value = '';
            if ($source === 'catalog' && isset($catalogData[$db_field])) {
                $value = $catalogData[$db_field];
            } elseif ($source === 'lot' && isset($lotData[$db_field])) {
                $value = $lotData[$db_field];
            }
            
            if (empty($value) || trim($value) === '') {
                $errors[] = "$field_name (from $source.$db_field)";
            }
        }
    }
    
    return $errors;
}

/**
 * Get catalog and lot data from database
 */
function getCoAData($catalog_number, $lot_number) {
    $conn = getDBConnection();
    
    // Get catalog data
    $catalog_sql = "SELECT * FROM catalogs WHERE catalogNumber = ?";
    $catalog_stmt = $conn->prepare($catalog_sql);
    $catalog_stmt->bind_param("s", $catalog_number);
    $catalog_stmt->execute();
    $catalog_result = $catalog_stmt->get_result();
    
    if ($catalog_result->num_rows === 0) {
        throw new Exception('Catalog not found');
    }
    
    $catalog_data = $catalog_result->fetch_assoc();
    $catalog_stmt->close();
    
    // Get lot data
    $lot_data = [];
    if (!empty($lot_number)) {
        $lot_sql = "SELECT * FROM lots WHERE catalogNumber = ? AND lotNumber = ?";
        $lot_stmt = $conn->prepare($lot_sql);
        $lot_stmt->bind_param("ss", $catalog_number, $lot_number);
        $lot_stmt->execute();
        $lot_result = $lot_stmt->get_result();
        
        if ($lot_result->num_rows > 0) {
            $lot_data = $lot_result->fetch_assoc();
        }
        $lot_stmt->close();
    }
    
    // $conn->close();
    
    return [
        'catalog' => $catalog_data,
        'lot' => $lot_data,
        'template_code' => $catalog_data['templateCode']
    ];
}

/**
 * Generate filename for PDF
 */
function generateFilename($catalog_number, $lot_number) {
    // $filename = 'CoA_' . $catalog_number;
    $filename = $catalog_number;
    if (!empty($lot_number)) {
        // Replace any slashes or special characters in lot number
        $lot_clean = str_replace(['/', '\\', ' ', '.'], '-', $lot_number);
        $filename .= '_' . $lot_clean;
    }
    //$filename .= '_' . date('Ymd') . '.pdf';
    $filename .= '.pdf';
    
    return $filename;
}

/**
 * Display error page
 */
function displayError($message) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Error</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 50px; }
            .error { 
                background-color: #f8d7da; 
                border: 1px solid #f5c6cb; 
                color: #721c24; 
                padding: 15px; 
                border-radius: 4px; 
            }
        </style>
    </head>
    <body>
        <div class="error">
            <h2>Error Generating PDF</h2>
            <p>' . htmlspecialchars($message) . '</p>
            <p><a href="javascript:history.back()">Go Back</a></p>
        </div>
    </body>
    </html>';
    exit;
}
?>