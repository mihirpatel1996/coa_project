<?php
/*

// Test script to verify uploadLogs table is working
require_once '../config/database.php';

header('Content-Type: text/plain');

try {
    $conn = getDBConnection();
    
    echo "Testing uploadLogs table...\n\n";
    
    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE 'uploadLogs'");
    if ($result && $result->num_rows > 0) {
        echo "✓ Table 'uploadLogs' exists\n";
    } else {
        echo "✗ Table 'uploadLogs' does NOT exist\n";
        exit;
    }
    
    // Check table structure
    echo "\nTable structure:\n";
    $result = $conn->query("DESCRIBE uploadLogs");
    while ($row = $result->fetch_assoc()) {
        echo sprintf("  %s: %s %s\n", 
            $row['Field'], 
            $row['Type'],
            $row['Null'] === 'YES' ? '(nullable)' : '(not null)'
        );
    }
    
    // Try a test insert
    echo "\nTesting insert...\n";
    
    $testSummary = [
        'uploadType' => 'catalog',
        'fileName' => 'test.csv',
        'totalRows' => 10,
        'successCount' => 8,
        'skippedCount' => 2,
        'errorCount' => 0
    ];
    
    $sql = "INSERT INTO uploadLogs (status, summary, errorMessage, uploadedAt) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo "✗ Failed to prepare statement: " . $conn->error . "\n";
        exit;
    }
    
    $status = 'test';
    $summaryJson = json_encode($testSummary);
    $errorMessage = null;
    
    $stmt->bind_param("sss", $status, $summaryJson, $errorMessage);
    
    if ($stmt->execute()) {
        $insertId = $conn->insert_id;
        echo "✓ Test insert successful! ID: $insertId\n";
        
        // Verify the insert
        $verify = $conn->query("SELECT * FROM uploadLogs WHERE id = $insertId");
        if ($verify && $verify->num_rows > 0) {
            $row = $verify->fetch_assoc();
            echo "\nInserted data:\n";
            echo "  ID: " . $row['id'] . "\n";
            echo "  Status: " . $row['status'] . "\n";
            echo "  Summary: " . $row['summary'] . "\n";
            echo "  Error: " . ($row['errorMessage'] ?? '(null)') . "\n";
            echo "  Uploaded At: " . $row['uploadedAt'] . "\n";
            
            // Try to decode JSON
            $decoded = json_decode($row['summary'], true);
            if ($decoded) {
                echo "\nDecoded summary:\n";
                foreach ($decoded as $key => $value) {
                    echo "  $key: $value\n";
                }
            }
            
            // Clean up test record
            $conn->query("DELETE FROM uploadLogs WHERE id = $insertId");
            echo "\n✓ Test record cleaned up\n";
        }
    } else {
        echo "✗ Insert failed: " . $stmt->error . "\n";
    }
    
    $stmt->close();
    
    // Show recent logs
    echo "\nRecent upload logs:\n";
    $result = $conn->query("SELECT id, status, uploadedAt FROM uploadLogs ORDER BY id DESC LIMIT 5");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo sprintf("  ID: %d, Status: %s, Time: %s\n", 
                $row['id'], 
                $row['status'], 
                $row['uploadedAt']
            );
        }
    } else {
        echo "  No logs found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

*/
?>