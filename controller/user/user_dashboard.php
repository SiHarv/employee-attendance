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

// Fetch attendance records (exclude empty/zero time_in)
$stmt = $conn->prepare("SELECT id, DATE(time_in) AS date, TIME(time_in) AS time_in, TIME(time_out) AS time_out, time_in, time_out, status 
    FROM time_log 
    WHERE employee_id = ? AND time_in IS NOT NULL AND TIME(time_in) != '00:00:00'
    ORDER BY time_in DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$attendance_result = $stmt->get_result();

$attendance = [];
while ($row = $attendance_result->fetch_assoc()) {
    // Calculate total hours if time_out exists
    $hours = '-';
    if (!empty($row['time_out']) && !empty($row['time_in'])) {
        $timeIn = new DateTime($row['time_in']);
        $timeOut = new DateTime($row['time_out']);
        $interval = $timeIn->diff($timeOut);
        $hours = $interval->h + ($interval->i / 60);
        $hours = round($hours, 2) . ' hrs';
    }
    $row['total_hours'] = $hours;
    $attendance[] = $row;
}

// For use in dashboard.php
return [
    'user' => $user,
    'attendance' => $attendance
];
