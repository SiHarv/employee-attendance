<?php
require_once '../../db/connect.php';
session_start();

header('Content-Type: application/json');

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit;
}

// Function to get current settings for time comparisons
function getSettings($conn) {
    $stmt = $conn->prepare("SELECT set_pm_time_in FROM settings WHERE id = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        // Default settings if none found
        return [
            'set_pm_time_in' => '13:00:00'
        ];
    }
    return $result->fetch_assoc();
}

// Process scan data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON data from the request
    $jsonData = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($jsonData['qrCode']) || empty($jsonData['qrCode'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid QR code data'
        ]);
        exit;
    }
    
    $qrCode = $jsonData['qrCode'];
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

    // Get current settings
    $settings = getSettings($conn);

    // Check if employee already logged in today (using DB server date)
    $stmt = $conn->prepare("SELECT id, time_out FROM afternoon_time_log WHERE employee_id = ? AND DATE(time_in) = CURDATE()");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (empty($row['time_out'])) {
            // Allow another scan if time_out is empty (not yet timed out)
            echo json_encode([
                'success' => false,
                'message' => 'Already logged in for PM today. Please time out before scanning again.',
                'employeeName' => $employee_name
            ]);
            exit;
        } else {
            // Already has time_in and time_out for today
            echo json_encode([
                'success' => false,
                'message' => 'Already logged in and timed out for PM today.',
                'employeeName' => $employee_name
            ]);
            exit;
        }
    }

    // Insert with NOW() and determine status using SQL
    $sql = "
        INSERT INTO afternoon_time_log (employee_id, time_in, status)
        VALUES (
            ?,
            NOW(),
            CASE
                WHEN TIME(NOW()) > ? THEN 'late'
                ELSE 'present'
            END
        )
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "is",
        $employee_id,
        $settings['set_pm_time_in']
    );

    if ($stmt->execute()) {
        // Get the inserted row for accurate time and status
        $log_id = $stmt->insert_id;
        $stmt2 = $conn->prepare("SELECT time_in, status FROM afternoon_time_log WHERE id = ?");
        $stmt2->bind_param("i", $log_id);
        $stmt2->execute();
        $log = $stmt2->get_result()->fetch_assoc();

        $statusMessage = $log['status'] === 'present' ? 'on time' : 'late';
        $time_formatted = date('h:i A', strtotime($log['time_in']));

        echo json_encode([
            'success' => true,
            'message' => "PM Attendance recorded successfully! {$employee_name} is {$statusMessage}.",
            'employeeName' => $employee_name,
            'status' => $log['status'],
            'time' => $time_formatted
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error recording attendance: ' . $stmt->error
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
