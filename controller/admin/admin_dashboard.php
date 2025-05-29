<?php
require_once '../../includes/admin/header.php';
require_once '../../includes/admin/sidebar.php';

// Fetch attendance data from the database
include '../../db/connect.php';

$query = "SELECT * FROM morning_time_log ORDER BY time_in DESC";
$result = $conn->query($query);

?>

<div class="content">
    <h1>Admin Dashboard</h1>
    <div class="attendance-log">
        <h2>Attendance Log</h2>
        <table>
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['employee_id']}</td>
                                <td>{$row['time_in']}</td>
                                <td>{$row['time_out']}</td>
                                <td>{$row['status']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No attendance records found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once '../../includes/admin/footer.php';
?>