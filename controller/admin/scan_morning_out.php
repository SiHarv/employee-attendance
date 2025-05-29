<?php
// Suppress errors from being sent to the browser, but log them to a file for debugging
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/process_out_scan_error.log');

require_once '../../db/connect.php';
session_start();

header('Content-Type: application/json');

// Check DB connection
if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error'
    ]);
    exit;
}

if (!isset($_SESSION['admin_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit;
}

function getSettings($conn) {
    $stmt = $conn->prepare("SELECT time_out FROM settings WHERE id = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        return ['time_out' => '17:00:00'];
    }
    return $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonData = json_decode(file_get_contents('php://input'), true);

    if (!isset($jsonData['qrCode']) || empty($jsonData['qrCode'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid QR code data'
        ]);
        exit;
    }

    $qrCode = $jsonData['qrCode'];

    // Check if the QR code exists in the users table
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE code = ?");
    $stmt->bind_param("s", $qrCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Unknown QR code. Employee not found.'
        ]);
        exit;
    }

    $employee = $result->fetch_assoc();
    $employee_id = $employee['id'];
    $employee_name = $employee['username'];

    $settings = getSettings($conn);

    // Check today's morning_time_log for this employee
    $stmt = $conn->prepare("SELECT id, time_in, time_out FROM morning_time_log WHERE employee_id = ? AND DATE(time_in) = CURDATE()");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $check_result = $stmt->get_result();

    if ($check_result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'No time-in record found for today. Please time in first.',
            'employeeName' => $employee_name
        ]);
        exit;
    }

    $log = $check_result->fetch_assoc();

    if (!is_null($log['time_out'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Already timed out today.',
            'employeeName' => $employee_name
        ]);
        exit;
    }

    // Get current time from database server in 24-hour format
    $currentTimeResult = $conn->query("SELECT CURRENT_TIME() as now");
    if (!$currentTimeResult) {
        echo json_encode([
            'success' => false,
            'message' => 'Error getting current time: ' . $conn->error
        ]);
        exit;
    }
    $currentTimeRow = $currentTimeResult->fetch_assoc();
    $currentTime = $currentTimeRow['now'];
    $timeOutSetting = $settings['time_out'];

    // Compare using timestamps to avoid string comparison issues
    if (strtotime($currentTime) < strtotime($timeOutSetting)) {
        echo json_encode([
            'success' => false,
            'message' => 'Not yet time to time out. Come back at time out.',
            'comebackTime' => $timeOutSetting,
            'employeeName' => $employee_name
        ]);
        exit;
    }

    // It's time to time out, update the record
    $stmt2 = $conn->prepare("UPDATE morning_time_log SET time_out = NOW() WHERE id = ?");
    $stmt2->bind_param("i", $log['id']);
    if ($stmt2->execute()) {
        // Get the actual time_out from database for accurate display
        $timeQuery = $conn->query("SELECT TIME_FORMAT(time_out, '%H:%i') as formatted_time FROM morning_time_log WHERE id = " . $log['id']);
        $timeRow = $timeQuery->fetch_assoc();
        $timeOut = $timeRow['formatted_time'];

        echo json_encode([
            'success' => true,
            'message' => "Time out recorded successfully! {$employee_name}.",
            'employeeName' => $employee_name,
            'status' => 'timeout',
            'time' => $timeOut
        ]);
        exit;
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error recording time out: ' . $stmt2->error
        ]);
        exit;
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}
