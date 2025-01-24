<?php
session_start();
require 'db_connect.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        // Query to fetch user by email
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: ../views/dashboard.html");
                } elseif ($user['role'] === 'teacher') {
                    header("Location: ../views/dashboard.html");
                } elseif ($user['role'] === 'student') {
                    header("Location: ../views/dashboard.html");
                }
                exit();
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "No user found with this email.";
        }
    } else {
        echo "Please fill in all fields.";
    }
}
?>
