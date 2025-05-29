<?php
require_once '../../db/connect.php';

// Get filter parameters
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$employeeId = isset($_GET['employee_id']) ? $_GET['employee_id'] : '';

// Pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$rowsPerPage = isset($_GET['rows_per_page']) ? intval($_GET['rows_per_page']) : 6;
$offset = ($page - 1) * $rowsPerPage;

// Count total records for pagination
$countQuery = "SELECT COUNT(*) as total FROM morning_time_log t 
               JOIN users u ON t.employee_id = u.id 
               WHERE 1=1";
               
if ($startDate) {
    $countQuery .= " AND DATE(t.time_in) >= '$startDate'";
}
if ($endDate) {
    $countQuery .= " AND DATE(t.time_in) <= '$endDate'";
}
if ($status && $status != 'all') {
    $countQuery .= " AND t.status = '$status'";
}
if ($employeeId) {
    $countQuery .= " AND t.employee_id = $employeeId";
}

$countResult = $conn->query($countQuery);
$totalRecords = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $rowsPerPage);

// Prepare query with filters and pagination
$query = "SELECT t.*, u.username 
          FROM morning_time_log t 
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

$query .= " ORDER BY t.time_in DESC LIMIT $rowsPerPage OFFSET $offset";
$result = $conn->query($query);

// Calculate statistics
$totalRecords = $result->num_rows;
$presentCount = 0;
$lateCount = 0;
$absentCount = 0;
$totalHours = 0;

// Clone result to use for stats calculation
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
$avgHours = $totalRecords > 0 ? round($totalHours / $totalRecords, 2) : 0;

// Calculate total hours per day for the user (combining both morning and afternoon)
$query_total_hours = "
    SELECT 
        t_combined.employee_id,
        t_combined.log_date,
        SUM(t_combined.hours) AS total_hours
    FROM (
        -- Get hours from morning logs
        SELECT 
            employee_id,
            DATE(time_in) AS log_date,
            TIMESTAMPDIFF(HOUR, time_in, time_out) + 
            TIMESTAMPDIFF(MINUTE, time_in, time_out) % 60 / 60 AS hours
        FROM 
            morning_time_log
        WHERE 
            time_out IS NOT NULL
            
        UNION ALL
        
        -- Get hours from afternoon logs
        SELECT 
            employee_id,
            DATE(time_in) AS log_date,
            TIMESTAMPDIFF(HOUR, time_in, time_out) + 
            TIMESTAMPDIFF(MINUTE, time_in, time_out) % 60 / 60 AS hours
        FROM 
            afternoon_time_log
        WHERE 
            time_out IS NOT NULL
    ) AS t_combined
    GROUP BY 
        t_combined.employee_id, t_combined.log_date";

$total_hours_result = $conn->query($query_total_hours);
$total_hours_by_employee = [];

if ($total_hours_result && $total_hours_result->num_rows > 0) {
    while ($row = $total_hours_result->fetch_assoc()) {
        $key = $row['employee_id'] . '_' . $row['log_date'];
        $total_hours_by_employee[$key] = round($row['total_hours'], 2);
    }
}
?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
        <h6 class="m-0 font-weight-bold text-primary">
            Morning Attendance Records
            <span class="text-muted ms-2">(<?php echo $totalRecords; ?> records)</span>
        </h6>
        <div class="input-group" style="width: 250px;">
            <input type="text" id="searchReport" class="form-control form-control-sm" placeholder="Search...">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive" id="reportTableContainer">
            <table class="table table-striped table-hover" id="reportTable">
                <thead class="table-dark">
                    <tr>
                        <th>Employee</th>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Total Hours</th>
                        <th>Total Hours Today</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows == 0): ?>
                        <tr>
                            <td colspan="8" class="text-center">No records found</td>
                        </tr>
                    <?php else: ?>
                        <?php while($row = $result->fetch_assoc()): ?>
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

                            // Get total hours for the employee on that day
                            $date = date('Y-m-d', strtotime($row['time_in']));
                            $key = $row['employee_id'] . '_' . $date;
                            $total_hours_today = isset($total_hours_by_employee[$key]) ? 
                                $total_hours_by_employee[$key] . ' hrs' : '-';
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['time_in'])); ?></td>
                                <td><?php echo date('h:i A', strtotime($row['time_in'])); ?></td>
                                <td><?php echo $row['time_out'] ? date('h:i A', strtotime($row['time_out'])) : '-'; ?></td>
                                <td><?php echo $hours; ?></td>
                                <td><?php echo $total_hours_today; ?></td>
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
        
        <!-- Pagination controls -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="#" data-page="<?php echo $page-1; ?>">Previous</a>
                </li>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="#" data-page="<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                
                <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="#" data-page="<?php echo $page+1; ?>">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
    <div class="card-footer bg-white text-center">
        <p class="mt-2 mb-2">End of records</p>
    </div>
</div>

<script>
$(document).ready(function() {
    // Search functionality
    $("#searchReport").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#reportTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
});

// Function to load a specific page of morning attendance
function loadMorningPage(page) {
    $.ajax({
        url: '../../includes/admin/morning_attendance.php',
        type: 'GET',
        data: {
            page: page,
            rows_per_page: 6,
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            status: $('#status').val(),
            employee_id: $('#employee_id').val()
        },
        success: function(response) {
            $('#morningAttendanceContainer').html(response);
        },
        error: function(xhr, status, error) {
            console.error("Error loading morning attendance: " + error);
        }
    });
}

// Use direct click handlers for each pagination link
$(document).ready(function() {
    $(".pagination .page-link").on("click", function(e) {
        e.preventDefault();
        var page = $(this).data("page");
        loadMorningPage(page);
    });
});
</script>
