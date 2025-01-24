<?php
include 'db_connect.php';

// Test user data
$users = [
    // Add students
    ['Student One', 'student1@example.com', 'Student@123', 'student'],
    ['Student Two', 'student2@example.com', 'Student@123', 'student'],
    ['Student Three', 'student3@example.com', 'Student@123', 'student'],
    // Add teachers
    ['Teacher One', 'teacher1@example.com', 'Teacher@123', 'teacher'],
    ['Teacher Two', 'teacher2@example.com', 'Teacher@123', 'teacher'],
    ['Teacher Three', 'teacher3@example.com', 'Teacher@123', 'teacher']
];

foreach ($users as $user) {
    $name = $user[0];
    $email = $user[1];
    $password = $user[2];
    $role = $user[3];

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert into the database
    $query = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);

    if ($stmt->execute()) {
        echo "User '$name' added successfully.<br>";
    } else {
        echo "Error adding user '$name': " . $stmt->error . "<br>";
    }
}

$stmt->close();
$conn->close();
?>
