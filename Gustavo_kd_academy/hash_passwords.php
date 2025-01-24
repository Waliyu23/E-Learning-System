<?php
include 'db_connect.php';

// User data
$name = 'Admin User';
$email = 'husseinwaliyu23@gmail.com';
$password = 'Albiruni@23'; // Plain-text password
$role = 'admin';

// Hash the password using PHP's password_hash()
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Prepare the SQL query
$query = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);

if ($stmt->execute()) {
    echo "User inserted successfully.";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
