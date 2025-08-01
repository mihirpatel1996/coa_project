<?php
// api/test_pdf_fixed.php
// Fixed test script that properly saves PDFs to the pdf folder

require_once '../vendor/autoload.php';

echo "<h1>PDF Generation Test (Fixed)</h1>";

// Check TCPDF
echo "<h3>1. Checking TCPDF Installation:</h3>";
if (class_exists('TCPDF')) {
    echo "<p style='color: green;'>✓ TCPDF is installed</p>";
} else {
    echo "<p style='color: red;'>✗ TCPDF is not installed</p>";
    exit;
}

// Check pdf directory - FIXED PATH
echo "<h3>2. Checking PDF Directory:</h3>";
// We are in /api/test_pdf_fixed.php, so go up one level to project root
$current_script_dir = dirname(__FILE__); // This gives us /api
$project_root = dirname($current_script_dir); // This gives us project root
$pdf_dir = $project_root . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR;

echo "<p>Current script directory: " . $current_script_dir . "</p>";
echo "<p>Project root: " . $project_root . "</p>";
echo "<p>PDF directory path: " . $pdf_dir . "</p>";

// Create directory if it doesn't exist
if (!file_exists($pdf_dir)) {
    echo "<p style='color: orange;'>! PDF directory does not exist, creating it...</p>";
    if (@mkdir($pdf_dir, 0755, true)) {
        echo "<p style='color: green;'>✓ PDF directory created successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create PDF directory</p>";
        echo "<p>Please manually create this folder: " . $pdf_dir . "</p>";
        exit;
    }
}

// Check if writable
if (is_writable($pdf_dir)) {
    echo "<p style='color: green;'>✓ PDF directory is writable</p>";
} else {
    echo "<p style='color: red;'>✗ PDF directory is not writable</p>";
    echo "<p>Please check permissions on: " . $pdf_dir . "</p>";
}

// Test simple PDF generation
echo "<h3>3. Testing PDF Generation:</h3>";
try {
    // Create new PDF
    $pdf = new TCPDF();
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', 'B', 16);
    
    // Add title
    $pdf->Cell(0, 10, 'TCPDF Test Document', 0, 1, 'C');
    
    $pdf->Ln(10);
    
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Write(0, 'This is a test PDF generated on: ' . date('Y-m-d H:i:s'));
    $pdf->Ln(10);
    $pdf->Write(0, 'If you can read this, PDF generation is working correctly!');
    $pdf->Ln(10);
    $pdf->Write(0, 'Project Root: ' . $project_root);
    $pdf->Ln(5);
    $pdf->Write(0, 'PDF Directory: ' . $pdf_dir);
    
    // Generate filename
    $test_filename = 'test_' . date('YmdHis') . '.pdf';
    $full_path = $pdf_dir . $test_filename;
    
    echo "<p>Attempting to save PDF to: " . $full_path . "</p>";
    
    // Method 1: Try direct output to file
    try {
        $pdf->Output($full_path, 'F');
        
        if (file_exists($full_path)) {
            echo "<p style='color: green;'>✓ Method 1 (Output F): PDF created successfully!</p>";
        } else {
            throw new Exception("File not created with method 1");
        }
    } catch (Exception $e1) {
        echo "<p style='color: orange;'>! Method 1 failed: " . $e1->getMessage() . "</p>";
        
        // Method 2: Try getting content as string and saving with file_put_contents
        echo "<p>Trying alternative method...</p>";
        try {
            $pdf_content = $pdf->Output('', 'S');
            $bytes_written = file_put_contents($full_path, $pdf_content);
            
            if ($bytes_written !== false) {
                echo "<p style='color: green;'>✓ Method 2 (file_put_contents): PDF created successfully!</p>";
                echo "<p>Bytes written: " . $bytes_written . "</p>";
            } else {
                throw new Exception("file_put_contents failed");
            }
        } catch (Exception $e2) {
            echo "<p style='color: red;'>✗ Method 2 failed: " . $e2->getMessage() . "</p>";
        }
    }
    
    // Check if file exists
    if (file_exists($full_path)) {
        $file_size = filesize($full_path);
        echo "<p style='color: green;'>✓ PDF file exists!</p>";
        echo "<p>File size: " . number_format($file_size) . " bytes</p>";
        echo "<p>Filename: " . $test_filename . "</p>";
        
        // Create download link
        $pdf_url = '../pdf/' . $test_filename;
        echo "<p><a href='" . $pdf_url . "' target='_blank' class='btn btn-primary'>📄 View Test PDF</a></p>";
        
        // Also create direct download link
        echo "<p><a href='" . $pdf_url . "' download='" . $test_filename . "'>⬇️ Download Test PDF</a></p>";
    } else {
        echo "<p style='color: red;'>✗ PDF file was not created</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// List existing PDFs in the directory
echo "<h3>4. Existing PDFs in directory:</h3>";
if (file_exists($pdf_dir) && is_dir($pdf_dir)) {
    $files = scandir($pdf_dir);
    $pdf_files = array_filter($files, function($file) {
        return pathinfo($file, PATHINFO_EXTENSION) === 'pdf';
    });
    
    if (count($pdf_files) > 0) {
        echo "<ul>";
        foreach ($pdf_files as $file) {
            $file_path = $pdf_dir . $file;
            $file_size = filesize($file_path);
            $file_date = date("Y-m-d H:i:s", filemtime($file_path));
            echo "<li>";
            echo $file . " (" . number_format($file_size) . " bytes, " . $file_date . ")";
            echo " - <a href='../pdf/" . $file . "' target='_blank'>View</a>";
            echo " | <a href='../pdf/" . $file . "' download='" . $file . "'>Download</a>";
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No PDF files found in directory</p>";
    }
} else {
    echo "<p style='color: red;'>PDF directory not accessible</p>";
}

// Database test (separate connection)
echo "<h3>5. Database Connection Test:</h3>";
try {
    require_once '../config/database.php';
    $conn = getDBConnection();
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Check if log table exists
    $result = $conn->query("SHOW TABLES LIKE 'pdf_generation_log'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✓ PDF generation log table exists</p>";
        
        // Show recent logs
        $log_result = $conn->query("SELECT * FROM pdf_generation_log ORDER BY generated_at DESC LIMIT 5");
        if ($log_result && $log_result->num_rows > 0) {
            echo "<h4>Recent PDF Generation Logs:</h4>";
            echo "<ul>";
            while ($log = $log_result->fetch_assoc()) {
                echo "<li>" . $log['catalog_number'] . " / " . $log['lot_number'] . 
                     " - " . $log['generated_at'] . "</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p style='color: orange;'>! PDF generation log table does not exist</p>";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='test_pdf_simple.php'>Test Simple PDF Output</a> | ";
echo "<a href='../'>Back to Main Application</a></p>";
?>