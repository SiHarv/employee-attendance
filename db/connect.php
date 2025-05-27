<?php
// Database connection settings
$host = "localhost"; // Database host
$username = "root"; // Database username
$password = ""; // Database password
$database = "employee_attendance"; // Database name

global $conn;
try {
    $conn = new mysqli($host, $username, $password, $database);
    
    // Check for connection errors
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    return $conn;
} catch (Exception $e) {
    // Handle connection error
    die("Database connection error: " . $e->getMessage());
}
?>