<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    exit("Unauthorized");
}

require_once '../../db/connect.php';

// Get recent scans for the day
$today = date('Y-m-d');
$recent_scans_query = "SELECT tl.*, u.username 
                      FROM morning_time_log tl 
                      JOIN users u ON tl.employee_id = u.id 
                      WHERE DATE(tl.time_in) = ? 
                      ORDER BY tl.time_in DESC 
                      LIMIT 10";
$stmt = $conn->prepare($recent_scans_query);
$stmt->bind_param("s", $today);
$stmt->execute();
$recent_scans = $stmt->get_result();

if ($recent_scans->num_rows > 0) {
    while ($scan = $recent_scans->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($scan['username']) . '</td>';
        echo '<td>' . date('h:i A', strtotime($scan['time_in'])) . '</td>';
        echo '<td>';
        echo '<span class="status-badge ' . ($scan['status'] === 'present' ? 'status-present' : 'status-late') . '">';
        echo ucfirst($scan['status']);
        echo '</span>';
        echo '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="3" class="text-center">No scans recorded today</td></tr>';
}
