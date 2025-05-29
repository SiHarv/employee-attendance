<?php
require_once '../../db/connect.php';
session_start();

// Set content type to JSON
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
    $stmt = $conn->prepare("SELECT set_am_time_in, set_am_time_out FROM settings WHERE id = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        // Default settings if none found
        return [
            'set_am_time_in' => '08:00:00',
            'set_am_time_out' => '17:00:00'
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
    
    // Get current settings
    $settings = getSettings($conn);

    // Check if employee already logged in today (using DB server date)
    $stmt = $conn->prepare("SELECT id, time_in, time_out FROM morning_time_log WHERE employee_id = ? AND DATE(time_in) = CURDATE()");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $check_result = $stmt->get_result();

    if ($check_result->num_rows > 0) {
        $log = $check_result->fetch_assoc();
        // If time_out is not set, check if it's time to time out
        if (is_null($log['time_out'])) {
            // Get current time and time_out from settings
            $currentTime = date('H:i:s');
            $timeOutSetting = $settings['set_am_time_out'];
            if ($currentTime < $timeOutSetting) {
                // Not yet time to time out
                echo json_encode([
                    'success' => false,
                    'message' => 'Already logged in today. Come back at time out.',
                    'comebackTime' => date('h:i A', strtotime($timeOutSetting)),
                    'employeeName' => $employee_name
                ]);
                exit;
            } else {
                // It's time to time out, update the record
                $stmt2 = $conn->prepare("UPDATE morning_time_log SET time_out = NOW() WHERE id = ?");
                $stmt2->bind_param("i", $log['id']);
                if ($stmt2->execute()) {
                    echo json_encode([
                        'success' => true,
                        'message' => "Time out recorded successfully! {$employee_name}.",
                        'employeeName' => $employee_name,
                        'status' => 'timeout',
                        'time' => date('h:i A')
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error recording time out: ' . $stmt2->error
                    ]);
                }
                exit;
            }
        } else {
            // Already timed out today
            echo json_encode([
                'success' => false,
                'message' => 'Already logged in and timed out today.',
                'employeeName' => $employee_name
            ]);
            exit;
        }
    }
    
    // Using 15 minutes as fixed threshold since it's no longer in settings table
    $threshold_minute = 15;
    
    // Insert with NOW() and determine status using SQL
    $sql = "
        INSERT INTO morning_time_log (employee_id, time_in, status)
        VALUES (
            ?,
            NOW(),
            CASE
                WHEN TIME(NOW()) <= ADDTIME(?, SEC_TO_TIME(? * 60)) THEN 'present'
                ELSE 'late'
            END
        )
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "isi",
        $employee_id,
        $settings['set_am_time_in'],
        $threshold_minute
    );
    
    if ($stmt->execute()) {
        // Get the inserted row for accurate time and status
        $log_id = $stmt->insert_id;
        $stmt2 = $conn->prepare("SELECT time_in, status FROM morning_time_log WHERE id = ?");
        $stmt2->bind_param("i", $log_id);
        $stmt2->execute();
        $log = $stmt2->get_result()->fetch_assoc();
        
        $statusMessage = $log['status'] === 'present' ? 'on time' : 'late';
        $time_formatted = date('h:i A', strtotime($log['time_in']));
        
        echo json_encode([
            'success' => true,
            'message' => "Attendance recorded successfully! {$employee_name} is {$statusMessage}.",
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
