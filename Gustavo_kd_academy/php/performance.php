<?php
require 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Utility function for error logging
function logError($error) {
    error_log("SQL Error: $error", 3, __DIR__ . "/error.log"); // Log error to the file
}

// Fetch performance records (combined logic for admin, teacher, and student)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // SQL query based on user role
        if ($role === 'admin') {
            // Admin can see all performance records
            $sql = "SELECT performance.*, students.name AS student_name, courses.name AS course_name 
                    FROM performance 
                    JOIN users AS students ON performance.student_id = students.id 
                    JOIN courses ON performance.course_id = courses.id";
        } elseif ($role === 'teacher') {
            // Teacher can see performance records for courses they teach
            $sql = "SELECT performance.*, students.name AS student_name, courses.name AS course_name 
                    FROM performance 
                    JOIN users AS students ON performance.student_id = students.id 
                    JOIN courses ON performance.course_id = courses.id 
                    WHERE courses.teacher_id = ?";
        } else {
            // Student can only see their own performance records
            $sql = "SELECT performance.*, courses.name AS course_name 
                    FROM performance 
                    JOIN courses ON performance.course_id = courses.id 
                    WHERE performance.student_id = ?";
        }

        // Prepare and execute the SQL statement
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        // Bind the user ID for teacher and student queries
        if ($role !== 'admin') {
            $stmt->bind_param("i", $user_id);
        }

        // Execute the query
        $stmt->execute();
        $result = $stmt->get_result();

        $performanceRecords = [];
        while ($row = $result->fetch_assoc()) {
            $performanceRecords[] = [
                'id' => $row['id'],
                'student_name' => htmlspecialchars($row['student_name']),
                'course_name' => htmlspecialchars($row['course_name']),
                'grade' => htmlspecialchars($row['grade']),
                'remarks' => htmlspecialchars($row['remarks']),
                'deletable' => ($role === 'admin') // Only admin can delete performance records
            ];
        }

        // Return a JSON response
        echo json_encode(['status' => 'success', 'data' => $performanceRecords]);
    } catch (Exception $e) {
        logError($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch performance records']);
    }

    exit();
}

// Add performance record (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['student_id'], $_POST['course_id'], $_POST['grade'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit();
    }

    // Insert the performance record into the database
    $stmt = $conn->prepare("INSERT INTO performance (student_id, course_id, grade, remarks) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        logError($conn->error);
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
        exit();
    }

    $stmt->bind_param("iiss", $_POST['student_id'], $_POST['course_id'], $_POST['grade'], $_POST['remarks']);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Performance added successfully']);
    } else {
        logError($stmt->error);
        echo json_encode(['status' => 'error', 'message' => 'Failed to add performance']);
    }

    exit();
}

// Delete performance record (DELETE request)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if ($role !== 'admin') {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit();
    }

    parse_str(file_get_contents("php://input"), $data);
    if (!isset($data['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required field: id']);
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM performance WHERE id = ?");
    if (!$stmt) {
        logError($conn->error);
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
        exit();
    }

    $stmt->bind_param("i", $data['id']);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Performance record deleted successfully']);
    } else {
        logError($stmt->error);
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete performance record']);
    }

    exit();
}

// Handle unsupported HTTP methods
http_response_code(405); // Method Not Allowed
echo json_encode(['status' => 'error', 'message' => 'Invalid HTTP method']);
exit();
?>
