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

// Determine session and recommended mode
$session = 'am'; // default
$recommended_mode = 'in'; // default

// PM session (after PM time in)
if ($current_time >= $settings['set_pm_time_in']) {
    $session = 'pm';
    
    // If after PM time out, recommend time out
    if ($current_time >= $settings['set_pm_time_out']) {
        $recommended_mode = 'out';
    }
} 
// AM session
else {
    $session = 'am';
    
    // If after AM time out, recommend time out
    if ($current_time >= $settings['set_am_time_out']) {
        $recommended_mode = 'out';
    }
}

echo json_encode([
    'success' => true,
    'current_time' => $current_time,
    'formatted_time' => date('h:i A'),
    'session' => $session,
    'recommended_mode' => $recommended_mode,
    'settings' => [
        'am_time_in' => date('h:i A', strtotime($settings['set_am_time_in'])),
        'am_time_out' => date('h:i A', strtotime($settings['set_am_time_out'])),
        'pm_time_in' => date('h:i A', strtotime($settings['set_pm_time_in'])),
        'pm_time_out' => date('h:i A', strtotime($settings['set_pm_time_out'])),
    ]
]);
