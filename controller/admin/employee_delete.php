<?php
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

$employee_id = intval($_POST['employee_id'] ?? 0);

if (!$employee_id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No employee ID provided.',
        'debug' => $_POST
    ]);
    exit;
}

try {
    // Optionally, delete attendance records first if you want to enforce cascading delete
    $stmt = $conn->prepare("DELETE FROM morning_time_log WHERE employee_id = ?");
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $stmt->close();

    // Delete employee
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Employee deleted successfully.'
        ]);
    } else {
        throw new Exception('Employee not found or already deleted.');
    }
    $stmt->close();

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
