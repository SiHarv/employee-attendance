<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../db/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Fetch attendance records
$stmt = $conn->prepare("SELECT id, DATE(time_in) AS date, TIME(time_in) AS time_in, TIME(time_out) AS time_out, status FROM time_log WHERE employee_id = ? ORDER BY time_in DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$attendance_result = $stmt->get_result();

$attendance = [];
while ($row = $attendance_result->fetch_assoc()) {
    $attendance[] = $row;
}

// For use in dashboard.php
return [
    'user' => $user,
    'attendance' => $attendance
];
