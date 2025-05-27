<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../db/connect.php';
session_start();
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Not authenticated. Please log in again.',
        'debug' => ['session' => $_SESSION]
    ]);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method',
        'debug' => ['request_method' => $_SERVER['REQUEST_METHOD']]
    ]);
    exit;
}

// Collect and validate data
$employee_id = intval($_POST['employee_id'] ?? 0);
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$code = trim($_POST['code'] ?? '');

if (!$employee_id || !$username || !$email || !$code) {
    echo json_encode([
        'status' => 'error',
        'message' => 'All fields except password are required.',
        'debug' => $_POST
    ]);
    exit;
}

try {
    // Check for duplicate username/email (excluding current employee)
    $check_query = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
    $stmt = $conn->prepare($check_query);
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
    $stmt->bind_param("ssi", $username, $email, $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        throw new Exception('Username or email already exists.');
    }
    $stmt->close();

    // Update query
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_query = "UPDATE users SET username = ?, email = ?, password = ?, code = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
        $stmt->bind_param("ssssi", $username, $email, $hashed_password, $code, $employee_id);
    } else {
        $update_query = "UPDATE users SET username = ?, email = ?, code = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
        $stmt->bind_param("sssi", $username, $email, $code, $employee_id);
    }

    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Employee updated successfully'
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'debug' => [
            'exception' => [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ],
            'post_data' => $_POST
        ]
    ]);
    exit;
}
