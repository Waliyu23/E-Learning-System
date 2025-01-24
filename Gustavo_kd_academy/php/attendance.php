<?php
require 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Missing session data']);
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Utility function for SQL debugging
function debugSQL($stmt) {
    $stmt->execute();
    if ($stmt->error) {
        error_log("SQL Error: " . $stmt->error, 3, "/path/to/error.log"); // Log the error
        echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
        exit();
    }
    return $stmt->get_result();
}

// Fetch attendance records
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

    $sql = ($role === 'admin') 
        ? "SELECT attendance.*, students.name AS student_name, courses.name AS course_name 
           FROM attendance 
           JOIN users AS students ON attendance.student_id = students.id 
           JOIN courses ON attendance.course_id = courses.id 
           LIMIT ? OFFSET ?"
        : (($role === 'teacher') 
           ? "SELECT attendance.*, students.name AS student_name, courses.name AS course_name 
              FROM attendance 
              JOIN users AS students ON attendance.student_id = students.id 
              JOIN courses ON attendance.course_id = courses.id 
              WHERE courses.teacher_id = ? 
              LIMIT ? OFFSET ?"
           : "SELECT attendance.*, courses.name AS course_name 
              FROM attendance 
              JOIN courses ON attendance.course_id = courses.id 
              WHERE attendance.student_id = ? 
              LIMIT ? OFFSET ?");

    $stmt = $conn->prepare($sql);

    if ($role === 'admin') {
        $stmt->bind_param("ii", $limit, $offset);
    } else {
        $stmt->bind_param("iii", $user_id, $limit, $offset);
    }

    $result = debugSQL($stmt);
    echo json_encode(['status' => 'success', 'data' => $result->fetch_all(MYSQLI_ASSOC)]);
    exit();
}

// Add attendance record
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['student_id'], $_POST['course_id'], $_POST['date'], $_POST['status'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO attendance (student_id, course_id, date, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $_POST['student_id'], $_POST['course_id'], $_POST['date'], $_POST['status']);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Attendance added successfully']);
    } else {
        error_log("SQL Error: " . $stmt->error, 3, "/path/to/error.log");
        echo json_encode(['status' => 'error', 'message' => 'Failed to add attendance']);
    }
    exit();
}

// Delete attendance record
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $data);

    if (!isset($data['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameter: id']);
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM attendance WHERE id = ?");
    $stmt->bind_param("i", $data['id']);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Attendance record deleted successfully']);
    } else {
        error_log("SQL Error: " . $stmt->error, 3, "/path/to/error.log");
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete attendance record']);
    }
    exit();
}

// Handle unsupported HTTP methods
http_response_code(405); // Method Not Allowed
echo json_encode(['status' => 'error', 'message' => 'Invalid HTTP method']);
exit();
?>
