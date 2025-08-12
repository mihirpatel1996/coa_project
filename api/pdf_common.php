<?php
// api/pdf_common_mpdf.php - mPDF version (migrated from TCPDF)

// Required files
require_once '../vendor/autoload.php';
require_once '../config/database.php';
require_once '../config/templates_config.php';

// Import mPDF
use Mpdf\Mpdf;

/**
 * Load HTML template from file
 */
function loadHTMLTemplate($templateFile = 'coa_template_new.html') {
    $templatePath = __DIR__ . '/coa_templates/' . $templateFile;
    
    if (!file_exists($templatePath)) {
        throw new Exception("Template file not found: $templateFile");
    }
    
    $html = file_get_contents($templatePath);
    
    // mPDF handles CSS much better than TCPDF, so we don't need to remove CSS properties
    // In fact, mPDF supports most standard CSS including margins, padding, box-sizing
    
    return $html;
}

/**
 * Generate HTML content for a section - mPDF optimized version
 */
function generateSectionHTML($sectionId, $catalogData, $lotData, $templateCode) {
    $html = '';
    
    if (!isset(TEMPLATE_FIELDS[$templateCode][$sectionId])) {
        return '<tr><td colspan="2">No data available for this section.</td></tr>';
    }
    
    $hasContent = false;
    
    foreach (TEMPLATE_FIELDS[$templateCode][$sectionId] as $field_config) {
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
        if (empty($value) || $value === null || trim($value) === '') {
            continue;
        }
        
        $hasContent = true;
        
        // Format special fields
        $value = formatFieldValue($field_name, $value);

        // Add field as table row - mPDF supports style attributes well
        $html .= '<tr>' . "\n";
        $html .= '  <td width="35%" style="font-weight: bold; vertical-align: top; padding: 2px 0;">' . htmlspecialchars($field_name) . ':</td>' . "\n";
        $html .= '  <td width="65%" style="vertical-align: top; padding: 2px 0;">' .  htmlspecialchars($value) . '</td>' . "\n";
        $html .= '</tr>' . "\n";
    }
    
    if (!$hasContent) {
        return '<tr><td colspan="2">No data available for this section.</td></tr>';
    }
    
    return $html;
}

/**
 * Format field values for mPDF compatibility
 */
function formatFieldValue($field_name, $value) {
    // First, ensure we're working with a string
    $value = (string) $value;
    
    // Handle line breaks - mPDF handles <br /> well
    $value = str_replace("\n", "<br />", $value);
    
    // mPDF handles HTML entities better than TCPDF
    // No need to replace &deg; as mPDF handles it correctly
    
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
            // Handle temperature symbols - mPDF handles degree symbol well
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
    // First, let's check if the template exists
    if (!isset(TEMPLATE_FIELDS[$templateCode])) {
        // Template doesn't exist, show error
        $error_msg = "Template code '$templateCode' not found. Available templates: " . 
                     implode(', ', array_keys(TEMPLATE_FIELDS));
        $replacements = [
            '[CATALOG_NAME]' => htmlspecialchars($catalogData['catalogName'] ?? ''),
            '[CATALOG_NUMBER]' => htmlspecialchars($catalogData['catalogNumber'] ?? ''),
            '[LOT_NUMBER]' => htmlspecialchars($lotData['lotNumber'] ?? ''),
            '[DESCRIPTION]' => '<p style="color: red;">' . $error_msg . '</p>',
            '[SPECIFICATIONS]' => '<p style="color: red;">' . $error_msg . '</p>',
            '[PREPARATION_AND_STORAGE]' => '<p style="color: red;">' . $error_msg . '</p>'
        ];
    } else {
        // Get all sections for this template
        $all_sections = array_keys(TEMPLATE_FIELDS[$templateCode]);
        
        // Method 1: Try to find sections by name
        $desc_section = null;
        $spec_section = null;
        $prep_section = null;
        
        foreach ($all_sections as $section) {
            $lower = strtolower($section);
            if (strpos($lower, 'description') !== false) {
                $desc_section = $section;
            } elseif (strpos($lower, 'spec') !== false) {
                $spec_section = $section;
            } elseif (strpos($lower, 'preparation') !== false || strpos($lower, 'storage') !== false || strpos($lower, 'prep') !== false) {
                $prep_section = $section;
            }
        }
        
        // Method 2: If not found by name, use array positions
        if ($desc_section === null && isset($all_sections[0])) $desc_section = $all_sections[0];
        if ($spec_section === null && isset($all_sections[1])) $spec_section = $all_sections[1];
        if ($prep_section === null && isset($all_sections[2])) $prep_section = $all_sections[2];
        
        // Generate content
        $descriptionContent = $desc_section ? 
            '<table width="100%" cellpadding="2" cellspacing="0" border="0">' . 
            generateSectionHTML($desc_section, $catalogData, $lotData, $templateCode) . 
            '</table>' : 
            '<p>Description section not found</p>';
        
        $specificationsContent = $spec_section ? 
            '<table width="100%" cellpadding="2" cellspacing="0" border="0">' . 
            generateSectionHTML($spec_section, $catalogData, $lotData, $templateCode) . 
            '</table>' : 
            '<p>Specifications section not found</p>';
        
        $preparationContent = $prep_section ? 
            '<table width="100%" cellpadding="2" cellspacing="0" border="0">' . 
            generateSectionHTML($prep_section, $catalogData, $lotData, $templateCode) . 
            '</table>' : 
            '<p>Preparation section not found</p>';
        
        // Set replacements
        $replacements = [
            '[CATALOG_NAME]' => htmlspecialchars($catalogData['catalogName'] ?? ''),
            '[CATALOG_NUMBER]' => htmlspecialchars($catalogData['catalogNumber'] ?? ''),
            '[LOT_NUMBER]' => htmlspecialchars($lotData['lotNumber'] ?? ''),
            '[DESCRIPTION]' => $descriptionContent,
            '[SPECIFICATIONS]' => $specificationsContent,
            '[PREPARATION_AND_STORAGE]' => $preparationContent
        ];
    }
    
    // Replace all placeholders
    foreach ($replacements as $placeholder => $value) {
        $html = str_replace($placeholder, $value, $html);
    }
    
    return $html;
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
            $required = $field_config['required'] ?? false;
            
            if (!$required) continue;
            
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
 * Generate PDF object using mPDF
 */
function generatePDF($catalog_data, $lot_data, $template_code) {
    // Load HTML template
    $html = loadHTMLTemplate('coa_template_new.html');
    
    // Replace placeholders with data
    $html = replacePlaceholders($html, $catalog_data, $lot_data, $template_code);
    
    // Create new mPDF document with full Unicode support (proven configuration)
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
        'default_font' => 'helvetica',  // lowercase for consistency
        // Enable full Unicode support
        'useSubstitutions' => true,
        'useKerning' => true,
        'autoScriptToLang' => true,
        'autoLangToFont' => true,
        'backup_substitute_fonts' => ['helvetica', 'freesans'],
        // Set temp directory if needed
        'tempDir' => sys_get_temp_dir() . '/mpdf'
    ]);
    
    // Set document information
    $mpdf->SetTitle('Certificate of Analysis - ' . $catalog_data['catalogNumber']);
    $mpdf->SetAuthor('Sino Biological');
    $mpdf->SetSubject('Certificate of Analysis');
    $mpdf->SetKeywords('CoA, Certificate, Analysis, ' . $catalog_data['catalogNumber']);
    
    // Define footer HTML with better mPDF support
    $footerHtml = '
    <div style="border-top: 1px solid black; padding-top: 5px; text-align: center; font-size: 8pt; color: #333333;">
        Tel: +86-400-890-9989 (Global), +1-215-583-7898 (USA), +49(0)6196 9678656 (Europe)&nbsp;&nbsp;&nbsp;Website: www.sinobiological.com
    </div>';
    
    // Set HTML footer
    $mpdf->SetHTMLFooter($footerHtml);
    
    // Optional: Add watermark if needed
    // $mpdf->SetWatermarkText('DRAFT');
    // $mpdf->showWatermarkText = true;
    
    // Write HTML to PDF
    $mpdf->WriteHTML($html);

    return $mpdf;
}

/**
 * Generate filename for PDF
 */
function generateFilename($catalog_number, $lot_number) {
    $filename = 'CoA_' . $catalog_number;
    if (!empty($lot_number)) {
        // Replace any slashes or special characters in lot number
        $lot_clean = str_replace(['/', '\\', ' ', '.'], '-', $lot_number);
        $filename .= '_' . $lot_clean;
    }
    $filename .= '_' . date('Ymd') . '.pdf';
    
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

/**
 * Additional helper functions for mPDF
 */

/**
 * Create spacing row for table-based layouts
 */
function createSpacingRow($height = 5) {
    return '<tr><td height="' . $height . '" colspan="2">&nbsp;</td></tr>';
}

/**
 * Wrap content in a table (mPDF handles tables well)
 */
function wrapInTable($content, $width = '100%', $cellpadding = 2) {
    return '<table width="' . $width . '" cellpadding="' . $cellpadding . '" cellspacing="0" border="0">' . $content . '</table>';
}

/**
 * Create a subsection within a section
 */
function createSubsectionRow($title, $content) {
    $html = '<tr>' . "\n";
    $html .= '  <td colspan="2">' . "\n";
    $html .= '    <table width="100%" cellpadding="0" cellspacing="0" border="0">' . "\n";
    $html .= '      <tr><td style="font-weight: bold;">' . htmlspecialchars($title) . '</td></tr>' . "\n";
    $html .= '      <tr><td>' . $content . '</td></tr>' . "\n";
    $html .= '    </table>' . "\n";
    $html .= '  </td>' . "\n";
    $html .= '</tr>' . "\n";
    
    return $html;
}

/**
 * Optional: Add custom fonts to mPDF if needed
 */
function addCustomFonts($mpdf) {
    // Example: Add custom font
    // $mpdf->AddFontDirectory(__DIR__ . '/fonts');
    // $mpdf->fontdata['customfont'] = [
    //     'R' => 'CustomFont-Regular.ttf',
    //     'B' => 'CustomFont-Bold.ttf',
    // ];
    
    return $mpdf;
}