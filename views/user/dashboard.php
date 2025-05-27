<?php
session_start();
require_once '../../includes/user/header.php';
require_once '../../includes/user/sidebar.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch user data from session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch attendance data from the database
include '../../db/connect.php';
$query = "SELECT * FROM time_log WHERE employee_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$attendance_records = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<div class="dashboard">
    <h1>Welcome, <?php echo htmlspecialchars($username); ?></h1>
    <h2>Your Attendance Records</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($attendance_records)): ?>
                <tr>
                    <td colspan="4">No attendance records found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($attendance_records as $record): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($record['date']); ?></td>
                        <td><?php echo htmlspecialchars($record['time_in']); ?></td>
                        <td><?php echo htmlspecialchars($record['time_out']); ?></td>
                        <td><?php echo htmlspecialchars($record['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="../../assets/js/user/dashboard.js"></script>
<?php require_once '../../includes/user/footer.php'; ?>