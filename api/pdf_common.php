<?php
// api/pdf_common_clean.php
// This is how pdf_common.php should be structured

// Required files - ONLY at the top
require_once '../vendor/autoload.php';
require_once '../config/database.php';
require_once '../config/templates_config.php';

// NO CODE HERE - Only function definitions below

/**
 * Validate all required fields based on template
 */
function validateAllFields($catalog_data, $lot_data, $template_code) {
    $errors = [];
    
    // Always required
    if (empty($catalog_data['catalogName'])) {
        $errors[] = 'Catalog Name';
    }
    
    // Check template-specific fields
    if (isset(TEMPLATE_FIELDS[$template_code])) {
        foreach (TEMPLATE_FIELDS[$template_code] as $section_id => $fields) {
            foreach ($fields as $field_config) {
                $db_field = $field_config['db_field'];
                $field_name = $field_config['field_name'];
                $source = $field_config['field_source'];
                
                if ($source === 'catalog') {
                    if (empty($catalog_data[$db_field])) {
                        $errors[] = $field_name;
                    }
                } else { // lot
                    if (empty($lot_data[$db_field])) {
                        $errors[] = $field_name;
                    }
                }
            }
        }
    }
    
    return $errors;
}

/**
 * Get catalog and lot data
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
 * Generate PDF object
 */
function generatePDF($catalog_data, $lot_data, $template_code) {
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('CoA Generator');
    $pdf->SetAuthor('SignalChem Biotech / Sino Biological');
    $pdf->SetTitle('Certificate of Analysis - ' . $catalog_data['catalogNumber']);
    $pdf->SetSubject('Certificate of Analysis');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins (left, top, right)
    $pdf->SetMargins(20, 15, 20);
    $pdf->SetAutoPageBreak(TRUE, 20);
    
    // Set font for the entire document
    $pdf->SetFont('helvetica', '', 10);
    
    // Add a page
    $pdf->AddPage();
    
    // Get HTML content
    $html = generateHTMLContent($catalog_data, $lot_data, $template_code);
    
    // Write HTML to PDF
    $pdf->writeHTML($html, true, false, true, false, '');
    
    return $pdf;
}

/**
 * Generate HTML content for PDF
 */
function generateHTMLContent($catalog_data, $lot_data, $template_code) {
    $html = '';
    
    // Header with logo
    $html .= '<table cellpadding="0" cellspacing="0" border="0" width="100%">
        <tr>
            <td width="50%">
                <img src="' . __DIR__ . '/images/signalchem_sino_logo.png" height="80" />
            </td>
        </tr>
    </table>';
    
    $html .= '<br/><br/>';
    
    // Product header
    $html .= '<h2 style="color: #000; margin-bottom: 5px;">' . htmlspecialchars($catalog_data['catalogName']) . '</h2>';
    $html .= '<p style="margin: 0; text-align: left; padding-bottom: 0px;">
        <strong>Catalog Number:</strong> ' . htmlspecialchars($catalog_data['catalogNumber']) . '
    </p>';
    if (!empty($lot_data) && !empty($lot_data['lotNumber'])) {
        $html .= '<p style="margin: 0; text-align: left; padding-top: 0px;">
            <strong>Lot Number:</strong> ' . htmlspecialchars($lot_data['lotNumber']) . '
        </p>';
    }
    $html .= '<br/>';
    $html .= '<h1 style="text-align: center; color: #333; border-top: 1px solid #ccc; padding: 10px;">Certificate of Analysis</h1>';
    $html .= '<br/>';
    
    // Description Section
    $html .= '<h3 style="background-color: #f0f0f0; padding: 5px;">Description</h3>';
    $html .= generateSectionContent(1, $catalog_data, $lot_data, $template_code);
    $html .= '<br/>';
    
    // Specifications Section
    $html .= '<h3 style="background-color: #f0f0f0; padding: 5px;">Specifications</h3>';
    $html .= generateSectionContent(2, $catalog_data, $lot_data, $template_code);
    $html .= '<br/>';
    
    // Preparation and Storage Section
    $html .= '<h3 style="background-color: #f0f0f0; padding: 5px;">Preparation and Storage</h3>';
    $html .= generateSectionContent(3, $catalog_data, $lot_data, $template_code);
    $html .= '<br/><br/>';
    
    // Footer disclaimer
    $html .= '<div style="border-top: 1px solid #ccc; padding-top: 20px; font-size: 9pt;">
        <p>The products are not to be used in humans. In the absence of any express written agreement to the contrary, 
        products sold by SINO BIOLOGICAL, INC. are for research-use-only (RUO).</p>
        
        <p>If you have any further questions, please contact Technical Services at <strong>support@sinobiological.com</strong></p>
        
        <br/>
        <table cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td>
                    <img src="' . __DIR__ . '/images/signature.jpg" height="40" /><br/>
                    <strong>Donna Morrison, PhD</strong><br/>
                    Quality Assurance, SignalChem Biotech / Sino Biological
                </td>
            </tr>
        </table>
        
        <p style="font-size: 8pt; color: #666; margin-top: 20px;">
            Tel: +86-400-890-9989 (Global), +1-215-583-7898 (USA), +49(0)6196 9678656 (Europe)<br/>
            Website: www.sinobiological.com
        </p>
    </div>';
    
    return $html;
}

/**
 * Generate content for a specific section
 */
function generateSectionContent($section_id, $catalog_data, $lot_data, $template_code) {
    $html = '<div style="padding-left: 10px;">';
    
    if (isset(TEMPLATE_FIELDS[$template_code][$section_id])) {
        foreach (TEMPLATE_FIELDS[$template_code][$section_id] as $field_config) {
            $field_name = $field_config['field_name'];
            $db_field = $field_config['db_field'];
            $source = $field_config['field_source'];
            
            $value = '';
            if ($source === 'catalog') {
                $value = $catalog_data[$db_field] ?? '';
            } else {
                $value = $lot_data[$db_field] ?? '';
            }
            
            // Format special fields
            $value = formatFieldValue($field_name, $value);
            
            if (!empty($value)) {
                $html .= '<p style="margin: 5px 0;"><strong>' . htmlspecialchars($field_name) . ':</strong> ' . $value . '</p>';
            }
        }
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * Format field values for display
 */
function formatFieldValue($field_name, $value) {
    // Handle special formatting
    switch ($field_name) {
        case 'Molecular Formula':
            // Convert numbers to subscripts (e.g., H2O becomes H<sub>2</sub>O)
            $value = preg_replace('/(\d+)/', '<sub>$1</sub>', $value);
            break;
            
        case 'Predicted Molecular Mass':
        case 'Observed Molecular Mass':
            // Ensure units
            if (!preg_match('/\s*(kDa|Da|g\/mol)$/i', $value)) {
                $value .= ' Da';
            }
            break;
            
        case 'Temperature':
        case 'Shipping':
        case 'Stability & Storage':
            // Fix temperature notation
            $value = str_replace(array('-20C', '-80C', '-70C', '4C'), 
                               array('-20°C', '-80°C', '-70°C', '4°C'), 
                               $value);
            break;
    }
    
    // Handle line breaks
    $value = nl2br($value);
    
    // Ensure UTF-8 encoding
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate filename for PDF
 */
function generateFilename($catalog_number, $lot_number) {
    return sprintf(
        'CoA_%s_%s_%s.pdf',
        $catalog_number,
        $lot_number,
        date('Ymd')
    );
}

/**
 * Display error page
 */
function displayError($message) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>PDF Generation Error</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .error { background-color: #f8d7da; border: 1px solid #f5c6cb; 
                    color: #721c24; padding: 20px; border-radius: 5px; }
            .back { margin-top: 20px; }
            a { color: #007bff; text-decoration: none; }
            a:hover { text-decoration: underline; }
        </style>
    </head>
    <body>
        <div class="error">
            <h2>PDF Generation Error</h2>
            <p>' . htmlspecialchars($message) . '</p>
        </div>
        <div class="back">
            <a href="javascript:window.close()">Close Window</a> | 
            <a href="javascript:history.back()">Go Back</a>
        </div>
    </body>
    </html>';
    exit;
}

// NO CODE HERE - This file should end with the last function
?>