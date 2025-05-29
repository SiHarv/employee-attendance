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

// Function to get current settings
function getSettings($conn) {
    $stmt = $conn->prepare("SELECT set_am_time_out FROM settings WHERE id = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        return ['set_am_time_out' => '17:00:00'];
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
    
    // Check if employee exists
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

    // Check today's time log
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
            'message' => 'Comeback later for Time out. You have already timed out today.',
            'employeeName' => $employee_name
        ]);
        exit;
    }

    // Get settings
    $settings = getSettings($conn);

    // Set timezone and get current time
    date_default_timezone_set('Asia/Manila');
    $currentTime = strtotime(date('H:i:s'));
    $timeOutSetting = strtotime($settings['set_am_time_out']);

    // Compare times
    if ($currentTime < $timeOutSetting) {
        echo json_encode([
            'success' => false,
            'message' => 'Not yet time to time out. Come back at time out.',
            'comebackTime' => date('h:i A', $timeOutSetting),
            'employeeName' => $employee_name
        ]);
        exit;
    }

    // Update time out
    $stmt2 = $conn->prepare("UPDATE morning_time_log SET time_out = NOW() WHERE id = ?");
    $stmt2->bind_param("i", $log['id']);
    if ($stmt2->execute()) {
        echo json_encode([
            'success' => true,
            'message' => "Time out recorded successfully! {$employee_name}.",
            'employeeName' => $employee_name,
            'status' => 'timeout',
            'time' => date('h:i A', $currentTime)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error recording time out: ' . $stmt2->error
        ]);
    }
    exit;
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}
