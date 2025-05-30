<?php
session_start();
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit;
}

require_once '../../db/connect.php';

// Get settings for time settings
$settings_query = "SELECT set_am_time_in, set_am_time_out, set_pm_time_in, set_pm_time_out FROM settings WHERE id = 1";
$settings_result = $conn->query($settings_query);
$settings = $settings_result->fetch_assoc();

if (!$settings) {
    $settings = [
        'set_am_time_in' => '08:00:00',
        'set_am_time_out' => '17:00:00',
        'set_pm_time_in' => '13:00:00',
        'set_pm_time_out' => '17:00:00'
    ];
}

// Get recent scans for the day
$today = date('Y-m-d');
$recent_scans_query = "SELECT tl.*, u.username, u.code, 
                      CASE 
                        WHEN tl.source = 'morning' THEN 'Morning'
                        WHEN tl.source = 'afternoon' THEN 'Afternoon'
                      END AS period_label
                      FROM (
                          SELECT *, 'morning' as source FROM morning_time_log
                          WHERE DATE(time_in) = CURDATE()
                          UNION ALL
                          SELECT *, 'afternoon' as source FROM afternoon_time_log
                          WHERE DATE(time_in) = CURDATE()
                      ) tl 
                      JOIN users u ON tl.employee_id = u.id 
                      ORDER BY tl.time_in DESC 
                      LIMIT 10";
$result = $conn->query($recent_scans_query);

$scans = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $scans[] = [
            'username' => htmlspecialchars($row['username']),
            'time_in' => $row['time_in'],
            'time_out' => $row['time_out'],
            'source' => $row['source'],
            'status' => $row['status']
        ];
    }
}

// Return both the time settings and recent scans in the response
echo json_encode([
    'success' => true,
    'scans' => $scans,
    'settings' => [
        'set_am_time_in' => $settings['set_am_time_in'],
        'set_am_time_out' => $settings['set_am_time_out'],
        'set_pm_time_in' => $settings['set_pm_time_in'],
        'set_pm_time_out' => $settings['set_pm_time_out']
    ]
]);

$conn->close();
?>
