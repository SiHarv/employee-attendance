<?php
session_start();
require_once 'db/connect.php';
require_once 'includes/admin/header.php';
require_once 'includes/admin/sidebar.php';

// Check if the user is logged in as admin
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}

// Fetch attendance settings from the database
$query = "SELECT * FROM settings LIMIT 1";
$result = $conn->query($query);
$settings = $result->fetch_assoc();

// Handle attendance log submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = $_POST['employee_id'];
    $time_in = date('Y-m-d H:i:s');
    $status = 'present'; // Default status

    // Check if the employee is late
    $threshold_time_in = $settings['threshold_minute'];
    $time_in_limit = date('Y-m-d H:i:s', strtotime("+$threshold_time_in minutes", strtotime($settings['time_in'])));
    
    if ($time_in > $time_in_limit) {
        $status = 'late';
    }

    // Insert attendance log into the database
    $stmt = $conn->prepare("INSERT INTO time_log (employee_id, time_in, status) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $employee_id, $time_in, $status);
    $stmt->execute();
    $stmt->close();
}

// Fetch attendance logs
$attendance_query = "SELECT * FROM time_log ORDER BY time_in DESC";
$attendance_result = $conn->query($attendance_query);
?>

<div class="container">
    <h1>Admin Attendance Management</h1>
    <form method="POST" action="">
        <label for="employee_id">Employee ID:</label>
        <input type="number" name="employee_id" required>
        <button type="submit">Log Attendance</button>
    </form>

    <h2>Attendance Logs</h2>
    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Time In</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $attendance_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['employee_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['time_in']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php
require_once 'includes/admin/footer.php';
?>