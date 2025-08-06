<?php
// api/pdf_common.php - Template-based version

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
    
    return file_get_contents($templatePath);
}

/**
 * Generate HTML content for a section
 */
function generateSectionHTML($sectionId, $catalogData, $lotData, $templateCode) {
    $html = '';
    
    if (!isset(TEMPLATE_FIELDS[$templateCode][$sectionId])) {
        return '<p>No data available for this section.</p>';
    }
    
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
        if (empty($value)) {
            continue;
        }
        
        // Format special fields
        $value = formatFieldValue($field_name, $value);
        
        // Add field to HTML
        //$html .= '<p><strong>' . htmlspecialchars($field_name) . ':</strong> ' . $value . '</p>' . "\n";
        // Use div with class instead of p tags
        $html .= '<div class="field-item"><strong>' . htmlspecialchars($field_name) . ':</strong> ' . $value . '</div>' . "\n";
    }
    
    return $html ?: '<p>No data available for this section.</p>';
}

/**
 * Replace placeholders in template
 */
function replacePlaceholders($html, $catalogData, $lotData, $templateCode) {
    // Basic replacements
    $replacements = [
        '[CATALOG_NAME]' => htmlspecialchars($catalogData['catalogName'] ?? ''),
        '[CATALOG_NUMBER]' => htmlspecialchars($catalogData['catalogNumber'] ?? ''),
        '[LOT_NUMBER]' => htmlspecialchars($lotData['lotNumber'] ?? ''),
    ];
    
    // Replace basic placeholders
    foreach ($replacements as $placeholder => $value) {
        $html = str_replace($placeholder, $value, $html);
    }
    
    // Generate and replace section content
    $html = str_replace('[DESCRIPTION]', generateSectionHTML(1, $catalogData, $lotData, $templateCode), $html);
    $html = str_replace('[SPECIFICATIONS]', generateSectionHTML(2, $catalogData, $lotData, $templateCode), $html);
    $html = str_replace('[PREPARATION_AND_STORAGE]', generateSectionHTML(3, $catalogData, $lotData, $templateCode), $html);
    
    // Handle conditional lot number display
    if (empty($lotData['lotNumber'])) {
        // Remove the entire lot number line if no lot
        $html = preg_replace('/<strong>Lot Number:<\/strong>.*?<br>/s', '', $html);
        $html = preg_replace('/Lot Number: <strong>\[LOT_NUMBER\]<\/strong><br>/s', '', $html);
    }
    
    return $html;
}

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
    
    // Don't close connection here - let the calling script handle it
    // $conn->close();
    
    return [
        'catalog' => $catalog_data,
        'lot' => $lot_data,
        'template_code' => $catalog_data['templateCode']
    ];
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
    // $pdf->SetMargins(20, 15, 20);
    $pdf->SetMargins(15, 10, 15);
    $pdf->SetAutoPageBreak(TRUE, 20);
    
    // Set font
    $pdf->SetFont('helvetica', '', 10);
    
    // Add a page
    $pdf->AddPage();
    
    // Write HTML to PDF
    $pdf->writeHTML($html, true, false, true, false, '');
    
    return $pdf;
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
            $value = str_replace(
                ['-20C', '-80C', '-70C', '4C', '-20oC', '-80oC', '-70oC', '4oC'], 
                ['-20°C', '-80°C', '-70°C', '4°C', '-20°C', '-80°C', '-70°C', '4°C'], 
                $value
            );
            break;
            
        case 'Activity':
            // Convert \n to <br> for multiline activity descriptions
            $value = nl2br($value);
            break;
    }
    
    // Handle line breaks for all fields
    $value = nl2br($value);
    
    // Ensure proper encoding
    return $value; // Already escaped in generateSectionHTML
}

/**
 * Generate filename for PDF
 */
function generateFilename($catalog_number, $lot_number) {
    if (!empty($lot_number)) {
        return sprintf(
            'CoA_%s_%s_%s.pdf',
            $catalog_number,
            $lot_number,
            date('Ymd')
        );
    } else {
        return sprintf(
            'CoA_%s_%s.pdf',
            $catalog_number,
            date('Ymd')
        );
    }
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
?>