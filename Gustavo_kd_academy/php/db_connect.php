<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gustavo_kd_academy";

// Improved error handling
try {
    // Create connection using MySQLi
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    // Log the error (optional, ensure the log path is writable)
    error_log($e->getMessage(), 3, '/path/to/your_error_log.log'); // Update this path
    // Display a generic error message to the client
    die("Database connection failed. Please try again later.");
}

// Set charset to utf8mb4 for better international support
if (!$conn->set_charset("utf8mb4")) {
    error_log("Error loading character set utf8mb4: " . $conn->error);
}
?>
