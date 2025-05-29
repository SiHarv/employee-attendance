<?php
// Start the session
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <script src="../../assets/js/lib/jquery-3.7.1.min.js"></script>
    <script src="../../assets/js/lib/bootstrap.bundle.min.js" defer></script>
    <script src="../../assets/js/admin.js" defer></script>
</head>
<body>
    <?php
    require_once '../../includes/admin/header.php';
    require_once '../../includes/admin/sidebar.php';
    require_once '../../db/connect.php';

    // Get filter parameters
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
    $status = isset($_GET['status']) ? $_GET['status'] : 'all';
    $employeeId = isset($_GET['employee_id']) ? $_GET['employee_id'] : '';

    // Prepare query with filters
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
    
    $query .= " ORDER BY t.time_in DESC";
    $result = $conn->query($query);

    // Get employees for filter dropdown
    $employeesQuery = "SELECT id, username as name FROM users ORDER BY username";
    $employeesResult = $conn->query($employeesQuery);
    $employees = [];
    while ($row = $employeesResult->fetch_assoc()) {
        $employees[] = $row;
    }

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
    ?>

    <div class="main-content">
        <div class="container-fluid mt-4">
            <div class="content-wrapper">
                <div class="container-fluid">
                    <!-- Filter Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-light">
                            <h6 class="m-0 font-weight-bold text-primary">Filter Records</h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="" id="filterForm">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="start_date" class="form-label">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" 
                                               value="<?php echo htmlspecialchars($startDate); ?>">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="end_date" class="form-label">End Date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date"
                                               value="<?php echo htmlspecialchars($endDate); ?>">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="all" <?php echo ($status == 'all') ? 'selected' : ''; ?>>All Status</option>
                                            <option value="present" <?php echo ($status == 'present') ? 'selected' : ''; ?>>Present</option>
                                            <option value="absent" <?php echo ($status == 'absent') ? 'selected' : ''; ?>>Absent</option>
                                            <option value="late" <?php echo ($status == 'late') ? 'selected' : ''; ?>>Late</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="employee_id" class="form-label">Employee</label>
                                        <select class="form-select" id="employee_id" name="employee_id">
                                            <option value="">All Employees</option>
                                            <?php foreach ($employees as $employee): ?>
                                                <option value="<?php echo $employee['id']; ?>" 
                                                       <?php echo ($employeeId == $employee['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($employee['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-filter me-1"></i> Apply Filters
                                            </button>
                                            <a href="attendance.php" class="btn btn-outline-secondary">
                                                <i class="bi bi-x-circle me-1"></i> Reset
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-primary" onclick="setDateRange('Morning')">Morning</button>
                                            <button type="button" class="btn btn-outline-primary" onclick="setDateRange('Afternoon')">Afternoon</button>
                                        </div>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-primary" onclick="setDateRange('today')">Today</button>
                                            <button type="button" class="btn btn-outline-primary" onclick="setDateRange('yesterday')">Yesterday</button>
                                            <button type="button" class="btn btn-outline-primary" onclick="setDateRange('week')">This Week</button>
                                            <button type="button" class="btn btn-outline-primary" onclick="setDateRange('month')">This Month</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Attendance Table Containers -->
                    <div id="morningAttendanceContainer">
                        <?php require_once '../../includes/admin/morning_attendance.php'; ?>
                    </div>
                    <div id="afternoonAttendanceContainer" style="display:none;">
                        <?php require_once '../../includes/admin/afternoon_attendance.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include the edit modals -->
    <?php require_once 'modals/modal_edit_morning_attendance.php'; ?>
    <?php require_once 'modals/modal_edit_afternoon_attendance.php'; ?>

    <script>

    // Overwrite editAttendance to handle both morning and afternoon
    function editAttendance(id, name, timeIn, timeOut, status) {
        if (window.currentAttendanceType === 'afternoon') {
            // Afternoon modal
            document.getElementById('edit_afternoon_attendance_id').value = id;
            document.getElementById('edit_afternoon_employee_name').value = name;
            document.getElementById('edit_afternoon_time_in').value = timeIn ? timeIn.replace(' ', 'T') : '';
            document.getElementById('edit_afternoon_time_out').value = timeOut ? timeOut.replace(' ', 'T') : '';
            document.getElementById('edit_afternoon_status').value = status;
            new bootstrap.Modal(document.getElementById('editAfternoonAttendanceModal')).show();
        } else {
            // Morning modal
            document.getElementById('edit_attendance_id').value = id;
            document.getElementById('edit_employee_name').value = name;
            document.getElementById('edit_time_in').value = timeIn.replace(' ', 'T');
            if (timeOut) {
                document.getElementById('edit_time_out').value = timeOut.replace(' ', 'T');
            } else {
                document.getElementById('edit_time_out').value = '';
            }
            document.getElementById('edit_status').value = status;
            new bootstrap.Modal(document.getElementById('editAttendanceModal')).show();
        }
    }

    // Save handler for afternoon attendance
    document.addEventListener('DOMContentLoaded', function() {
        var btn = document.getElementById('saveAfternoonAttendanceChanges');
        if (btn) {
            btn.addEventListener('click', function() {
                const formData = {
                    attendance_id: document.getElementById('edit_afternoon_attendance_id').value,
                    time_in: document.getElementById('edit_afternoon_time_in').value.replace('T', ' '),
                    time_out: document.getElementById('edit_afternoon_time_out').value.replace('T', ' '),
                    status: document.getElementById('edit_afternoon_status').value,
                    type: 'afternoon'
                };
                fetch('../../controller/admin/employee_edit_attendance.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Attendance updated successfully!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating attendance');
                });
            });
        }
    });
    </script>

    <?php //require_once '../../includes/admin/footer.php'; ?>
</body>
</html>