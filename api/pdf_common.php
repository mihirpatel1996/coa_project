<?php
// api/pdf_common.php - TCPDF Optimized Template-based version

// Required files
require_once '../vendor/autoload.php';
require_once '../config/database.php';
require_once '../config/templates_config.php';

/**
 * Load HTML template from file
 */
function loadHTMLTemplate($templateFile = 'coa_template_new.html') {
    $templatePath = __DIR__ . '/coa_templates/' . $templateFile;
    
    if (!file_exists($templatePath)) {
        throw new Exception("Template file not found: $templateFile");
    }
    
    $html = file_get_contents($templatePath);
    
    // TCPDF-specific adjustments to ensure compatibility
    // Remove any problematic CSS properties that might have been added
    $html = preg_replace('/margin\s*:\s*[^;]+;/i', '', $html);
    $html = preg_replace('/padding\s*:\s*[^;]+;/i', '', $html);
    $html = preg_replace('/box-sizing\s*:\s*[^;]+;/i', '', $html);
    
    return $html;
}

/**
 * Generate HTML content for a section - TCPDF optimized version
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
        
        // Add field as table row with explicit width and styling
        $html .= '<tr>' . "\n";
        $html .= '  <td width="35%" style="font-weight: bold; vertical-align: top;">' . htmlspecialchars($field_name) . ':</td>' . "\n";
        $html .= '  <td width="65%" style="vertical-align: top;">' . $value . '</td>' . "\n";
        $html .= '</tr>' . "\n";
    }
    
    if (!$hasContent) {
        return '<tr><td colspan="2">No data available for this section.</td></tr>';
    }
    
    return $html;
}

/**
 * Format field values for TCPDF compatibility
 */
function formatFieldValue($field_name, $value) {
    // First, ensure we're working with a string
    $value = (string) $value;
    
    // Handle line breaks - convert \n to <br />
    $value = str_replace("\n", "<br />", $value);
    
    // Handle special HTML entities
    $value = str_replace('&deg;', '°', $value);
    
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
            // Handle temperature symbols - more comprehensive
            $value = preg_replace('/(-?\d+)\s*°\s*C/', '$1°C', $value);
            $value = preg_replace('/(-?\d+)\s*C\b/', '$1°C', $value);
            $value = str_replace('degrees C', '°C', $value);
            $value = str_replace('-20C', '-20°C', $value);
            $value = str_replace('-80C', '-80°C', $value);
            $value = str_replace('4C', '4°C', $value);
            $value = str_replace('RT', 'RT', $value); // Room Temperature
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
        $error_msg = "Template code '$templateCode' not found. Available templates: " . implode(', ', array_keys(TEMPLATE_FIELDS));
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
    
    // Perform replacements
    foreach ($replacements as $placeholder => $value) {
        $html = str_replace($placeholder, $value, $html);
    }
    
    return $html;
}

/**
 * Validate all required fields are filled
 */
function validateAllFields($catalogData, $lotData, $templateCode) {
    $errors = [];
    
    // Check catalog name
    if (empty($catalogData['catalogName'])) {
        $errors[] = 'Catalog Name';
    }
    
    // Check if we have at least some data in each section
    $sections = ['description', 'specifications', 'preparation_storage'];
    
    foreach ($sections as $section) {
        if (isset(TEMPLATE_FIELDS[$templateCode][$section])) {
            $hasData = false;
            foreach (TEMPLATE_FIELDS[$templateCode][$section] as $field_config) {
                $db_field = $field_config['db_field'];
                $source = $field_config['field_source'];
                
                if ($source === 'catalog' && !empty($catalogData[$db_field])) {
                    $hasData = true;
                    break;
                } elseif ($source === 'lot' && !empty($lotData[$db_field])) {
                    $hasData = true;
                    break;
                }
            }
            
            if (!$hasData) {
                $errors[] = ucfirst(str_replace('_', ' ', $section));
            }
        }
    }
    
    return $errors;
}

/**
 * Get CoA data from database
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

// Custom TCPDF class with table-based footer
class CustomTCPDF extends TCPDF {
    public function Footer() {
        // Position from bottom
        $this->SetY(-20);
        
        // Create table-based footer that matches the template style
        $footerHtml = '
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td height="1" style="border-top: 1px solid black;">&nbsp;</td>
            </tr>
            <!--<tr>
                <td height="1">&nbsp;</td>
            </tr>-->
            <tr>
                <td align="center" style="font-size: 8pt; color: #333333;">
                    Tel: +86-400-890-9989 (Global), +1-215-583-7898 (USA), +49(0)6196 9678656 (Europe)&nbsp;&nbsp;&nbsp;Website: www.sinobiological.com
                </td>
            </tr>
        </table>';
        
        // Write HTML without resetting margins
        $this->writeHTML($footerHtml, false, false, false, false, '');
    }
}

/**
 * Generate PDF object using template
 */
function generatePDF($catalog_data, $lot_data, $template_code) {
    // Load HTML template
    $html = loadHTMLTemplate('coa_template_new.html');
    
    // Replace placeholders with data
    $html = replacePlaceholders($html, $catalog_data, $lot_data, $template_code);
    
    // Create new PDF document
    $pdf = new CustomTCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Remove default header
    $pdf->setPrintHeader(false);
    
    // Set document information
    $pdf->SetCreator('CoA Generator');
    $pdf->SetAuthor('Sino Biological');
    $pdf->SetTitle('Certificate of Analysis - ' . $catalog_data['catalogNumber']);
    $pdf->SetSubject('Certificate of Analysis');
    $pdf->SetKeywords('CoA, Certificate, Analysis, ' . $catalog_data['catalogNumber']);
    
    // Set margins (left, top, right) - optimized for table layout
    $pdf->SetMargins(15, 10, 15);
    
    // Set auto page breaks with margin for footer
    $pdf->SetAutoPageBreak(TRUE, 25);
    
    // Set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    
    // Set font subsetting to reduce file size
    $pdf->setFontSubsetting(true);
    
    // Set default font
    $pdf->SetFont('helvetica', '', 10);
    
    // Add a page
    $pdf->AddPage();
    
    // Output the HTML content with specific parameters for table rendering
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Reset pointer to the last page
    $pdf->lastPage();
    
    return $pdf;
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
 * Additional helper functions for complex content
 */

/**
 * Create spacing row for table-based layouts
 */
function createSpacingRow($height = 5) {
    return '<tr><td height="' . $height . '" colspan="2">&nbsp;</td></tr>';
}

/**
 * Ensure proper table structure for TCPDF
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
 * Create a bordered info box
 */
function createInfoBox($content, $bgcolor = '#ffffcc') {
    $html = '<tr>' . "\n";
    $html .= '  <td colspan="2">' . "\n";
    $html .= '    <table width="100%" cellpadding="5" cellspacing="0" border="1" bgcolor="' . $bgcolor . '">' . "\n";
    $html .= '      <tr><td>' . $content . '</td></tr>' . "\n";
    $html .= '    </table>' . "\n";
    $html .= '  </td>' . "\n";
    $html .= '</tr>' . "\n";
    
    return $html;
}

/**
 * Generate test results table
 */
function generateTestResultsTable($testData) {
    $html = '<tr><td colspan="2">';
    $html .= '<table width="100%" cellpadding="3" cellspacing="0" border="1">';
    
    // Header row
    $html .= '<tr bgcolor="#e0e0e0">';
    $html .= '<td width="40%"><strong>Test Parameter</strong></td>';
    $html .= '<td width="30%"><strong>Result</strong></td>';
    $html .= '<td width="30%"><strong>Specification</strong></td>';
    $html .= '</tr>';
    
    // Data rows
    foreach ($testData as $test) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($test['parameter']) . '</td>';
        $html .= '<td align="center">' . htmlspecialchars($test['result']) . '</td>';
        $html .= '<td align="center">' . htmlspecialchars($test['specification']) . '</td>';
        $html .= '</tr>';
    }
    
    $html .= '</table>';
    $html .= '</td></tr>';
    
    return $html;
}

?>