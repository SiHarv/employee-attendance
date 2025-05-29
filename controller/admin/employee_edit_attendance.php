<?php
require_once '../../db/connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Morning & Afternoon Edit Attendance Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['attendance_id']) || !isset($data['time_in']) || !isset($data['status'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    
    $attendance_id = $data['attendance_id'];
    $time_in = $data['time_in'];
    $time_out = !empty($data['time_out']) ? $data['time_out'] : null;
    $status = $data['status'];
    $type = isset($data['type']) ? $data['type'] : 'morning';
    
    try {
        if ($type === 'afternoon') {
            $table = 'afternoon_time_log';
        } else {
            $table = 'morning_time_log';
        }
        
        // If time_out is empty, set it to NULL
        if (empty($time_out)) {
            $stmt = $conn->prepare("UPDATE $table SET time_in = ?, time_out = NULL, status = ? WHERE id = ?");
            $stmt->bind_param("ssi", $time_in, $status, $attendance_id);
        } else {
            $stmt = $conn->prepare("UPDATE $table SET time_in = ?, time_out = ?, status = ? WHERE id = ?");
            $stmt->bind_param("sssi", $time_in, $time_out, $status, $attendance_id);
        }

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Attendance updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating attendance: ' . $stmt->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Exception: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
