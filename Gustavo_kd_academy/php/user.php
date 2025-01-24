<?php
require 'db_connect.php';

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Helper function to log errors
function logError($message) {
    $logFile = __DIR__ . "/error.log"; // Path to log file
    error_log(date("[Y-m-d H:i:s]") . " $message\n", 3, $logFile);
}

// Handle GET requests to fetch users
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $role = isset($_GET['role']) ? $_GET['role'] : null;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

    try {
        if ($role) {
            $sql = "SELECT id, name, email, role FROM users WHERE role = ? LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sii", $role, $limit, $offset);
        } else {
            $sql = "SELECT id, name, email, role FROM users LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $limit, $offset);
        }

        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        echo json_encode(['status' => 'success', 'data' => $users]);
    } catch (Exception $e) {
        logError($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch users']);
    } finally {
        $stmt->close();
        $conn->close();
    }
    exit();
}

// Handle POST requests to add users
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['name'], $_POST['email'], $_POST['role'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit();
    }

    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
        exit();
    }

    // Validate role
    $allowedRoles = ['admin', 'teacher', 'student'];
    if (!in_array($role, $allowedRoles)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid role']);
        exit();
    }

    try {
        $sql = "INSERT INTO users (name, email, role) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $stmt->bind_param("sss", $name, $email, $role);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'User added successfully', 'id' => $stmt->insert_id]);
        } else {
            throw new Exception("Failed to add user");
        }
    } catch (Exception $e) {
        logError($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to add user']);
    } finally {
        $stmt->close();
        $conn->close();
    }
    exit();
}

// Handle PUT requests to update users
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"), $data);

    if (!isset($data['id'], $data['name'], $data['email'], $data['role'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit();
    }

    $id = $data['id'];
    $name = $data['name'];
    $email = $data['email'];
    $role = $data['role'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
        exit();
    }

    // Validate role
    $allowedRoles = ['admin', 'teacher', 'student'];
    if (!in_array($role, $allowedRoles)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid role']);
        exit();
    }

    try {
        $sql = "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $stmt->bind_param("sssi", $name, $email, $role, $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'User updated successfully']);
        } else {
            throw new Exception("Failed to update user");
        }
    } catch (Exception $e) {
        logError($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to update user']);
    } finally {
        $stmt->close();
        $conn->close();
    }
    exit();
}

// Handle DELETE requests to delete users
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $data);

    if (!isset($data['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required field: id']);
        exit();
    }

    $id = $data['id'];

    try {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'User deleted successfully']);
        } else {
            throw new Exception("Failed to delete user");
        }
    } catch (Exception $e) {
        logError($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete user']);
    } finally {
        $stmt->close();
        $conn->close();
    }
    exit();
}

// Handle unsupported HTTP methods
http_response_code(405); // Method Not Allowed
echo json_encode(['status' => 'error', 'message' => 'Invalid HTTP method']);
exit();
?>
