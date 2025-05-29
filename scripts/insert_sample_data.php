<?php
require_once '../db/connect.php';

// Function to generate a random date within a range
function randomDate($start_date, $end_date) {
    $min = strtotime($start_date);
    $max = strtotime($end_date);
    $rand_timestamp = mt_rand($min, $max);
    return date('Y-m-d H:i:s', $rand_timestamp);
}

// Function to generate a random time with seconds
function randomTime($start_time, $end_time) {
    $start_timestamp = strtotime("2023-01-01 $start_time");
    $end_timestamp = strtotime("2023-01-01 $end_time");
    if ($start_timestamp > $end_timestamp) {
        $end_timestamp = strtotime("2023-01-02 $end_time");
    }
    $random_timestamp = rand($start_timestamp, $end_timestamp);
    return date('H:i:s', $random_timestamp);
}

// Function to check if execution was successful
function executeAndReport($stmt, $operation) {
    if ($stmt->execute()) {
        echo "$operation successfully!<br>";
        return true;
    } else {
        echo "Error in $operation: " . $stmt->error . "<br>";
        return false;
    }
}

// Clear existing data
echo "<h2>Cleaning existing data...</h2>";
$conn->query("DELETE FROM morning_time_log");
$conn->query("DELETE FROM users WHERE username != 'admin'");
echo "Existing data cleared.<br><br>";

// Insert sample employees/users
echo "<h2>Inserting sample employees...</h2>";

$sample_employees = [
    ['John Smith', 'john.smith@example.com', 'password123'],
    ['Maria Garcia', 'maria.garcia@example.com', 'secure456'],
    ['David Johnson', 'david.j@example.com', 'david2023'],
    ['Sarah Williams', 'sarah.w@example.com', 'williams789'],
    ['Michael Brown', 'michael.b@example.com', 'brownmike'],
    ['Jessica Davis', 'jessica.d@example.com', 'jessica2023'],
    ['Robert Wilson', 'robert.w@example.com', 'wilson123'],
    ['Jennifer Taylor', 'jennifer.t@example.com', 'taylor456'],
    ['William Martinez', 'william.m@example.com', 'wmart789'],
    ['Lisa Anderson', 'lisa.a@example.com', 'lisa2023']
];

$insert_user_stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
$insert_user_stmt->bind_param("sss", $username, $email, $hashed_password);

$employee_ids = [];

foreach ($sample_employees as $employee) {
    $username = $employee[0];
    $email = $employee[1];
    $hashed_password = password_hash($employee[2], PASSWORD_DEFAULT);
    
    if (executeAndReport($insert_user_stmt, "Inserted employee: $username")) {
        $employee_ids[] = $conn->insert_id;
    }
}

// Get settings for time in/out
$settings_result = $conn->query("SELECT time_in, time_out, threshold_minute FROM settings WHERE id = 1");
$settings = $settings_result->fetch_assoc();
$standard_time_in = $settings['time_in'];
$standard_time_out = $settings['time_out'];
$threshold_minute = $settings['threshold_minute'];

// Insert attendance records for the last 7 days
echo "<br><h2>Inserting attendance records...</h2>";

$insert_log_stmt = $conn->prepare("INSERT INTO morning_time_log (employee_id, time_in, time_out, status) VALUES (?, ?, ?, ?)");
$insert_log_stmt->bind_param("isss", $employee_id, $time_in, $time_out, $status);

$today = date('Y-m-d');
$days_back = 7;

for ($i = $days_back; $i >= 0; $i--) {
    $current_date = date('Y-m-d', strtotime("-$i days"));
    echo "<h3>Creating records for: $current_date</h3>";
    
    foreach ($employee_ids as $employee_id) {
        // 85% chance the employee attended work
        if (mt_rand(1, 100) <= 85) {
            // Generate time in (potentially late)
            $base_time_in = $standard_time_in;
            $variance_minutes = mt_rand(-15, 30); // -15 to +30 minutes from standard time
            
            // Calculate time in
            $time_in_obj = new DateTime("$current_date $base_time_in");
            $time_in_obj->modify("$variance_minutes minutes");
            $time_in = $time_in_obj->format('Y-m-d H:i:s');
            
            // Determine status based on threshold
            $status = ($variance_minutes > $threshold_minute) ? 'late' : 'present';
            
            // Calculate time out (standard +/- some variance)
            $base_time_out = $standard_time_out;
            $out_variance = mt_rand(-15, 30); // -15 to +30 minutes from standard time
            
            $time_out_obj = new DateTime("$current_date $base_time_out");
            $time_out_obj->modify("$out_variance minutes");
            $time_out = $time_out_obj->format('Y-m-d H:i:s');
            
            // For today's record, possibly don't have time_out yet
            if ($current_date == $today && mt_rand(0, 1) == 0) {
                $time_out = null;
            }
            
            executeAndReport($insert_log_stmt, "Inserted attendance for employee ID $employee_id on $current_date");
        } else {
            // Employee absent, no record needed
            echo "Employee ID $employee_id was absent on $current_date<br>";
        }
    }
}

echo "<br><h2>Sample data insertion complete!</h2>";
echo "Inserted " . count($sample_employees) . " employee records.<br>";
echo "Generated attendance records for the past $days_back days.<br>";
echo "<a href='../index.php'>Go to homepage</a>";
?>
