<?php
require_once '../../db/connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Morning Edit Attendance Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $attendance_id = $data['attendance_id'];
    $time_in = $data['time_in'];
    $time_out = $data['time_out'] ?: null;
    $status = $data['status'];

    $stmt = $conn->prepare("UPDATE morning_time_log SET time_in = ?, time_out = ?, status = ? WHERE id = ?");
    $stmt->bind_param("sssi", $time_in, $time_out, $status, $attendance_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Attendance updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating attendance: ' . $stmt->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}


// Afternoon Edit Attendance Handler
//Code for afternoon attendance edit would be similar to the morning one, but using the `afternoon_time_log` table.
// Note: Ensure to handle the afternoon attendance edit in a similar manner as the morning one.
