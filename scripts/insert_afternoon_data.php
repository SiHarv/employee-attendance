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

// Clear existing afternoon attendance data
echo "<h2>Cleaning existing afternoon attendance data...</h2>";
$conn->query("DELETE FROM afternoon_time_log");
echo "Existing afternoon attendance data cleared.<br><br>";

// Create a direct list of specific afternoon attendance data
echo "<h2>Inserting fixed afternoon attendance records...</h2>";

// Create sample afternoon data with specific times for demonstration
$afternoon_records = [
    // Format: [employee_id, date, time_in, time_out, status]
    [1, '2025-05-23', '13:05:00', '17:00:00', 'present'],
    [2, '2025-05-23', '13:10:00', '17:15:00', 'present'], 
    [3, '2025-05-23', '13:20:00', '17:05:00', 'late'],
    [4, '2025-05-24', '13:00:00', '17:10:00', 'present'],
    [5, '2025-05-24', '13:25:00', '17:00:00', 'late'],
    [6, '2025-05-24', '13:05:00', '17:15:00', 'present'],
    [8, '2025-05-25', '12:55:00', '17:00:00', 'present'],
    [9, '2025-05-25', '13:10:00', '17:30:00', 'present'],
    [10, '2025-05-25', '13:30:00', '17:15:00', 'late'],
    [11, '2025-05-26', '13:00:00', '17:05:00', 'present']
];

// Prepare statement for fixed data
$insert_fixed_stmt = $conn->prepare("INSERT INTO afternoon_time_log (employee_id, time_in, time_out, status) VALUES (?, ?, ?, ?)");

if (!$insert_fixed_stmt) {
    die("Prepare statement failed: " . $conn->error);
}

$insert_fixed_stmt->bind_param("isss", $employee_id, $time_in, $time_out, $status);

// Insert the fixed sample data
$records_created = 0;

foreach ($afternoon_records as $record) {
    $employee_id = $record[0];
    $date = $record[1];
    $time_in_time = $record[2];
    $time_out_time = $record[3];
    $status = $record[4];
    
    // Create full datetime values
    $time_in = $date . ' ' . $time_in_time;
    $time_out = $date . ' ' . $time_out_time;
    
    // Insert the record
    if (executeAndReport($insert_fixed_stmt, "Inserted fixed afternoon record for employee ID $employee_id on $date")) {
        $records_created++;
    }
}

echo "<br><h3>Successfully inserted $records_created fixed afternoon attendance records.</h3>";

// Now also insert additional random records for the past few days
echo "<h2>Inserting additional random afternoon records...</h2>";

// Define set employee IDs to use (focusing on IDs that exist)
$employee_ids = [1, 2, 3, 4, 5, 6, 8, 9, 10, 11, 12, 13, 14];

// Get settings for afternoon time in/out
$settings_result = $conn->query("SELECT set_pm_time_in, set_pm_time_out FROM settings WHERE id = 1");
$settings = $settings_result->fetch_assoc();

// Default to 13:00:00 and 17:00:00 if settings aren't available
$standard_pm_time_in = isset($settings['set_pm_time_in']) ? $settings['set_pm_time_in'] : '13:00:00';
$standard_pm_time_out = isset($settings['set_pm_time_out']) ? $settings['set_pm_time_out'] : '17:00:00';

// Prepare the insert statement for random data
$insert_log_stmt = $conn->prepare("INSERT INTO afternoon_time_log (employee_id, time_in, time_out, status) VALUES (?, ?, ?, ?)");

if (!$insert_log_stmt) {
    die("Prepare statement failed: " . $conn->error);
}

$insert_log_stmt->bind_param("isss", $employee_id, $time_in, $time_out, $status);

$today = date('Y-m-d');
$random_records_created = 0;

// Create just a few random records for today
foreach ([1, 3, 5, 8, 10, 12] as $employee_id) {
    // Generate time in (potentially late)
    $variance_minutes = mt_rand(-15, 30);
    
    // Calculate time in
    $time_in_obj = new DateTime("$today $standard_pm_time_in");
    $time_in_obj->modify("$variance_minutes minutes");
    $time_in = $time_in_obj->format('Y-m-d H:i:s');
    
    // Determine status based on variance
    $status = ($variance_minutes > 15) ? 'late' : 'present';
    
    // 50% chance to have time_out
    if (mt_rand(0, 1) == 1) {
        // Calculate time out
        $out_variance = mt_rand(-15, 30);
        $time_out_obj = new DateTime("$today $standard_pm_time_out");
        $time_out_obj->modify("$out_variance minutes");
        $time_out = $time_out_obj->format('Y-m-d H:i:s');
    } else {
        $time_out = null;
    }
    
    // Insert the record
    if (executeAndReport($insert_log_stmt, "Inserted random afternoon record for employee ID $employee_id on $today")) {
        $random_records_created++;
    }
}

// Close prepared statements
$insert_fixed_stmt->close();
$insert_log_stmt->close();

echo "<br><h2>Sample afternoon data insertion complete!</h2>";
echo "Generated " . ($records_created + $random_records_created) . " afternoon attendance records.<br>";
echo "Check database table 'afternoon_time_log' for the new records.<br>";
echo "<a href='../index.php'>Go to homepage</a>";
?>
