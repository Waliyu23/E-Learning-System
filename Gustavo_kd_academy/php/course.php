<?php
require 'db_connect.php';

// Add a new course
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $teacher_id = isset($_POST['teacher_id']) ? $_POST['teacher_id'] : null; // Handle teacher_id assignment

    $sql = "INSERT INTO courses (name, description, teacher_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $name, $description, $teacher_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Course added successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add course']);
    }
    exit();
}

// Fetch all courses with teacher information
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT courses.id, courses.name, courses.description, courses.teacher_id, 
                   users.name AS teacher_name
            FROM courses
            LEFT JOIN users ON courses.teacher_id = users.id";
    $result = $conn->query($sql);

    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }

    echo json_encode($courses);
    exit();
}

// Delete a course
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $id = $_DELETE['id'];

    $sql = "DELETE FROM courses WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Course deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete course']);
    }
    exit();
}
?>
