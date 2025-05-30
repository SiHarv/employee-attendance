<?php
require_once '../../db/connect.php';
session_start();

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
    $stmt = $conn->prepare("SELECT set_am_time_in, set_am_time_out, set_pm_time_in, set_pm_time_out, threshold_minute FROM settings WHERE id = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        // Default settings if none found
        return [
            'set_am_time_in' => '08:00:00',
            'set_am_time_out' => '12:00:00',
            'set_pm_time_in' => '13:00:00',
            'set_pm_time_out' => '17:00:00',
            'threshold_minute' => 15
        ];
    }
    return $result->fetch_assoc();
}

// Set timezone to Philippines
date_default_timezone_set('Asia/Manila');

// Get current settings
$settings = getSettings($conn);

// Current time
$current_time = date('H:i:s');

// Determine time periods and recommended modes
// Default to morning in
$session = 'am';
$recommended_mode = 'in';
$auto_switch = false;
$force_switch = false;

// Define time boundaries (with buffer periods)
// Buffer helps with transition periods
$morning_time_in_start = '05:00:00'; // Early morning
$morning_time_in_end = $settings['set_am_time_out'];
$morning_time_out_start = $settings['set_am_time_out'];
$morning_time_out_end = $settings['set_pm_time_in']; 

$afternoon_time_in_start = $settings['set_pm_time_in'];
$afternoon_time_in_end = date('H:i:s', strtotime($settings['set_pm_time_out']) - (30 * 60)); // 30 min before PM out
$afternoon_time_out_start = date('H:i:s', strtotime($settings['set_pm_time_out']) - (30 * 60)); // 30 min before PM out
$afternoon_time_out_end = '23:59:59';

// Determine session and mode
if ($current_time >= $afternoon_time_in_start && $current_time < $afternoon_time_out_start) {
    // Afternoon Time In period
    $session = 'pm';
    $recommended_mode = 'in';
    $auto_switch = true;
} 
elseif ($current_time >= $afternoon_time_out_start) {
    // Afternoon Time Out period
    $session = 'pm';
    $recommended_mode = 'out';
    $auto_switch = true;
    // Force switch when it's definitely PM out time
    if ($current_time >= $settings['set_pm_time_out']) {
        $force_switch = true;
    }
} 
elseif ($current_time >= $morning_time_out_start && $current_time < $afternoon_time_in_start) {
    // Morning Time Out period
    $session = 'am';
    $recommended_mode = 'out';
    $auto_switch = true;
    // Force switch when it's definitely AM out time
    if ($current_time >= date('H:i:s', strtotime($settings['set_am_time_out']) + (15 * 60))) { // 15 min after AM out
        $force_switch = true;
    }
} 
else {
    // Morning Time In period (default)
    $session = 'am';
    $recommended_mode = 'in';
    $auto_switch = true;
}

echo json_encode([
    'success' => true,
    'current_time' => $current_time,
    'formatted_time' => date('g:i A'), // Changed from h:i A to g:i A to remove leading zero in hour
    'session' => $session,
    'recommended_mode' => $recommended_mode,
    'auto_switch' => $auto_switch,     // Whether auto-switching is recommended
    'force_switch' => $force_switch,   // Whether to force the switch (override user selection)
    'settings' => [
        'am_time_in' => date('g:i A', strtotime($settings['set_am_time_in'])),
        'am_time_out' => date('g:i A', strtotime($settings['set_am_time_out'])),
        'pm_time_in' => date('g:i A', strtotime($settings['set_pm_time_in'])),
        'pm_time_out' => date('g:i A', strtotime($settings['set_pm_time_out'])),
    ]
]);
