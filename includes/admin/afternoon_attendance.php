<?php
require_once '../../db/connect.php';

// Get filter parameters
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$employeeId = isset($_GET['employee_id']) ? $_GET['employee_id'] : '';

// Prepare query with filters - simpler query without pagination
$query = "SELECT t.*, u.username 
          FROM afternoon_time_log t 
          JOIN users u ON t.employee_id = u.id 
          WHERE 1=1";

if ($startDate) {
    $query .= " AND DATE(t.time_in) >= '$startDate'";
}
if ($endDate) {
    $query .= " AND DATE(t.time_in) <= '$endDate'";
}
if ($status && $status != 'all') {
    $query .= " AND t.status = '$status'";
}
if ($employeeId) {
    $query .= " AND t.employee_id = $employeeId";
}

$query .= " ORDER BY t.time_in DESC";
$result = $conn->query($query);

// Calculate statistics
$totalRecords = $result ? $result->num_rows : 0;
$presentCount = 0;
$lateCount = 0;
$absentCount = 0;
$totalHours = 0;

// Clone result to use for stats calculation
if ($totalRecords > 0) {
    $statsResult = $conn->query($query);
    while ($row = $statsResult->fetch_assoc()) {
        if ($row['status'] == 'present') {
            $presentCount++;
        } elseif ($row['status'] == 'late') {
            $lateCount++;
        } elseif ($row['status'] == 'absent') {
            $absentCount++;
        }

        // Calculate hours if time_out is available
        if (!empty($row['time_out'])) {
            $timeIn = new DateTime($row['time_in']);
            $timeOut = new DateTime($row['time_out']);
            $interval = $timeIn->diff($timeOut);
            $hours = $interval->h + ($interval->i / 60);
            $totalHours += $hours;
        }
    }
}
$avgHours = $totalRecords > 0 ? round($totalHours / $totalRecords, 2) : 0;
?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
        <h6 class="m-0 font-weight-bold text-primary">
            Afternoon Attendance Records
            <span class="text-muted ms-2">(<?php echo $totalRecords; ?> records)</span>
        </h6>
        <div class="input-group" style="width: 250px;">
            <input type="text" id="searchAfternoonReport" class="form-control form-control-sm" placeholder="Search...">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="afternoonReportTable">
                <thead class="table-dark">
                    <tr>
                        <th>Employee</th>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Total Hours</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$result || $result->num_rows == 0): ?>
                        <tr>
                            <td colspan="7" class="text-center">No records found</td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        // Reset result pointer if needed
                        $result->data_seek(0);
                        while($row = $result->fetch_assoc()): ?>
                            <?php
                            // Calculate hours if time_out exists
                            $hours = '-';
                            if (!empty($row['time_out'])) {
                                $timeIn = new DateTime($row['time_in']);
                                $timeOut = new DateTime($row['time_out']);
                                $interval = $timeIn->diff($timeOut);
                                $hours = $interval->h + ($interval->i / 60);
                                $hours = round($hours, 2) . ' hrs';
                            }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['time_in'])); ?></td>
                                <td><?php echo date('h:i A', strtotime($row['time_in'])); ?></td>
                                <td><?php echo $row['time_out'] ? date('h:i A', strtotime($row['time_out'])) : '-'; ?></td>
                                <td><?php echo $hours; ?></td>
                                <td>
                                    <span class="badge rounded-pill <?php 
                                        echo match($row['status']) {
                                            'present' => 'bg-success',
                                            'late' => 'bg-warning',
                                            'absent' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                    ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="editAttendance(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['username']); ?>', '<?php echo $row['time_in']; ?>', '<?php echo $row['time_out'] ? $row['time_out'] : ''; ?>', '<?php echo $row['status']; ?>')">
                                        <i class="bi bi-pencil me-1"></i>Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white text-center">
        <p class="mt-2 mb-2">End of records</p>
    </div>
</div>

<script>
$(document).ready(function() {
    // Search functionality for afternoon
    $("#searchAfternoonReport").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#afternoonReportTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
});
</script>