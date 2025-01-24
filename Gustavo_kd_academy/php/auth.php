<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

require 'db_connect.php';

$user_id = $_SESSION['user_id'];

// Use a prepared statement to fetch user details securely
$sql = "SELECT id, name, role FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);

// Check if the statement prepared successfully
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    exit();
}

// Bind the user ID and execute the query
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if a user was found
if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    echo json_encode([
        'status' => 'success',
        'role' => $user['role'],
        'name' => $user['name']
    ]);
} else {
    // User not found
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
}

// Close the statement and database connection
$stmt->close();
$conn->close();
?>
