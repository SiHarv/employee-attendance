<?php
session_start();
include_once '../../db/connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$current_time = date('H:i:s');
$status = 'absent';

// Fetch user settings for attendance
$query = "SELECT time_in, threshold_minute FROM settings LIMIT 1";
$result = $conn->query($query);
$settings = $result->fetch_assoc();

$time_in = $settings['time_in'];
$threshold_minute = $settings['threshold_minute'];
$threshold_time = date('H:i:s', strtotime($time_in) + ($threshold_minute * 60));

// Check attendance status
if ($current_time <= $threshold_time) {
    $status = 'present';
} elseif ($current_time > $threshold_time) {
    $status = 'late';
}

// Insert attendance log
$stmt = $conn->prepare("INSERT INTO time_log (employee_id, time_in, status) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user_id, $current_time, $status);
$stmt->execute();
$stmt->close();

$conn->close();

header('Location: ../../views/user/attendance.php?status=' . $status);
exit();
?>