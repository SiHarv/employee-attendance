<?php
require_once '../../db/connect.php';
require_once '../../lib/qrcode-go.php';

session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../login.php');
    exit();
}

$attendance_records = [];
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'];
    $status = $_POST['status'];
    $time_in = date('Y-m-d H:i:s');

    // Check if the employee exists
    $stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update attendance record
        $stmt = $conn->prepare("INSERT INTO time_log (employee_id, time_in, status) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $employee_id, $time_in, $status);

        if ($stmt->execute()) {
            $attendance_records[] = [
                'employee_id' => $employee_id,
                'time_in' => $time_in,
                'status' => $status
            ];
        } else {
            $error_message = 'Failed to log attendance.';
        }
    } else {
        $error_message = 'Employee not found.';
    }
}

$stmt = $conn->prepare("SELECT * FROM time_log ORDER BY time_in DESC");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $attendance_records[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Attendance Management</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <?php require_once '../../includes/admin/header.php'; ?>
    <?php require_once '../../includes/admin/sidebar.php'; ?>

    <div class="main-content">
        <h1>Attendance Management</h1>

        <?php if ($error_message): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="employee_id">Employee ID:</label>
            <input type="text" name="employee_id" required>
            <label for="status">Status:</label>
            <select name="status" required>
                <option value="present">Present</option>
                <option value="late">Late</option>
                <option value="absent">Absent</option>
            </select>
            <button type="submit">Log Attendance</button>
        </form>

        <h2>Attendance Records</h2>
        <table>
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>Time In</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendance_records as $record): ?>
                    <tr>
                        <td><?php echo $record['employee_id']; ?></td>
                        <td><?php echo $record['time_in']; ?></td>
                        <td><?php echo $record['status']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>