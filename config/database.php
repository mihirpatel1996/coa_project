<?php
// Database configuration for WAMP using MySQLi
define('DB_HOST', 'localhost');
define('DB_NAME', 'coa_db');
define('DB_USER', 'root');
define('DB_PASS', ''); // Usually empty for WAMP
define('DB_CHARSET', 'utf8mb4');

// Global database connection variable
$conn = null;

// Create database connection
function getDBConnection() {
    global $conn;
    
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Check connection
        if ($conn->connect_error) {
            die("Database connection failed: " . $conn->connect_error);
        }
        
        // Set charset
        $conn->set_charset(DB_CHARSET);
    }
    
    return $conn;
}

// Close database connection
function closeDBConnection() {
    global $conn;
    if ($conn && !$conn->connect_error) {
        $conn->close();
        $conn = null;
    }
}

// Escape string for SQL safety
function escapeString($string) {
    global $conn;
    if ($conn === null) {
        getDBConnection();
    }
    return $conn->real_escape_string($string);
}

// Execute a SELECT query and return results
function executeQuery($sql) {
    global $conn;
    if ($conn === null) {
        getDBConnection();
    }
    
    $result = $conn->query($sql);
    
    if ($result === false) {
        die("Query failed: " . $conn->error);
    }
    
    return $result;
}

// Execute INSERT, UPDATE, DELETE queries
function executeNonQuery($sql) {
    global $conn;
    if ($conn === null) {
        getDBConnection();
    }
    
    $result = $conn->query($sql);
    
    if ($result === false) {
        die("Query failed: " . $conn->error);
    }
    
    return $result;
}

// Get last inserted ID
function getLastInsertId() {
    global $conn;
    if ($conn === null) {
        getDBConnection();
    }
    
    return $conn->insert_id;
}

// Prepare and execute a statement with parameters
function executeStatement($sql, $types = "", $params = []) {
    global $conn;
    if ($conn === null) {
        getDBConnection();
    }
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        die("Statement preparation failed: " . $conn->error);
    }
    
    // Bind parameters if provided
    if (!empty($params) && !empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    
    // Execute statement
    $result = $stmt->execute();
    
    if ($result === false) {
        die("Statement execution failed: " . $stmt->error);
    }
    
    return $stmt;
}

// Get all rows from a result set as associative array
function fetchAllAssoc($result) {
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

// Get single row from a result set as associative array
function fetchAssoc($result) {
    return $result->fetch_assoc();
}

// Get row count from a result set
function getRowCount($result) {
    return $result->num_rows;
}

// Test database connection
function testDBConnection() {
    try {
        $conn = getDBConnection();
        echo "<h1>PDF Generator Setup</h1>";
        echo "<p style='color: green;'>✓ Database connection successful!</p>";
        
        // Test if tables exist
        $result = executeQuery("SHOW TABLES");
        $tables = fetchAllAssoc($result);
        
        echo "<h3>Database Tables:</h3>";
        echo "<ul>";
        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            echo "<li>$tableName</li>";
        }
        echo "</ul>";
        
        // Test sample data
        $result = executeQuery("SELECT COUNT(*) as count FROM catalogs");
        $row = fetchAssoc($result);
        echo "<p>Sample catalogs in database: " . $row['count'] . "</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    }
}

// Initialize database connection on include
getDBConnection();
?>