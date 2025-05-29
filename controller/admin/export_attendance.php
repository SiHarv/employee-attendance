<?php
ob_start();
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../views/admin/login.php");
    exit();
}

require_once '../../db/connect.php';

// Get parameters
$filename = isset($_GET['filename']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '_', $_GET['filename']) : 'attendance_report';
$format = isset($_GET['format']) && $_GET['format'] === 'csv' ? 'csv' : 'excel';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : null;
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : null;
$columns = isset($_GET['columns']) ? explode(',', $_GET['columns']) : ['employee_id', 'name', 'date', 'time_in', 'time_out', 'status'];

// Validate columns and map to your schema
$allowed_columns = [
    'employee_id' => 't.employee_id',
    'name' => 'u.username AS name',
    'date' => 'DATE(t.time_in) AS date',
    'time_in' => 't.time_in',
    'time_out' => 't.time_out',
    'status' => 't.status'
];
$select = [];
foreach ($columns as $col) {
    if (isset($allowed_columns[$col])) {
        $select[] = $allowed_columns[$col];
    }
}
if (empty($select)) {
    die('No valid columns selected.');
}

// Build query using morning_time_log and users
$sql = "SELECT " . implode(", ", $select) . " FROM morning_time_log t LEFT JOIN users u ON t.employee_id = u.id WHERE 1";
$params = [];
$types = '';
if ($date_from) {
    $sql .= " AND DATE(t.time_in) >= ?";
    $params[] = $date_from;
    $types .= 's';
}
if ($date_to) {
    $sql .= " AND DATE(t.time_in) <= ?";
    $params[] = $date_to;
    $types .= 's';
}
$sql .= " ORDER BY t.time_in ASC";

// Prepare and execute
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Set headers
if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
} else {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
}

// Output
$output = fopen('php://output', 'w');
// Output header row
fputcsv($output, $columns);
// Output data rows
while ($row = $result->fetch_assoc()) {
    $data = [];
    foreach ($columns as $col) {
        $data[] = isset($row[$col]) ? $row[$col] : '';
    }
    fputcsv($output, $data);
}
fclose($output);
exit;
