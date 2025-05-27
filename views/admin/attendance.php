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
              FROM time_log t 
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h3">Attendance Records</h2>
                        
                        <div>
                            <a href="../../controller/admin/export_attendance.php?start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>&status=<?php echo $status; ?>&employee_id=<?php echo $employeeId; ?>" class="btn btn-success">
                                <i class="bi bi-file-earmark-excel me-1"></i> Export to Excel
                            </a>
                            <button type="button" class="btn btn-info" id="printBtn">
                                <i class="bi bi-printer me-1"></i> Print Report
                            </button>
                        </div>
                    </div>
                    
                    <!-- Report Statistics -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Records</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalRecords; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bi bi-clipboard-data" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card bg-success text-white shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                Present</div>
                                            <div class="h5 mb-0 font-weight-bold"><?php echo $presentCount; ?></div>
                                            <div class="mt-1 small">
                                                <?php echo $totalRecords > 0 ? round(($presentCount / $totalRecords) * 100) : 0; ?>% of total
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bi bi-check-circle-fill" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card bg-warning text-white shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                Late</div>
                                            <div class="h5 mb-0 font-weight-bold"><?php echo $lateCount; ?></div>
                                            <div class="mt-1 small">
                                                <?php echo $totalRecords > 0 ? round(($lateCount / $totalRecords) * 100) : 0; ?>% of total
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bi bi-clock-history" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card bg-info text-white shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                Avg. Hours</div>
                                            <div class="h5 mb-0 font-weight-bold"><?php echo $avgHours; ?></div>
                                            <div class="mt-1 small">
                                                Total: <?php echo round($totalHours, 1); ?> hours
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bi bi-clock-history" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
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

                    <!-- Report Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
                            <h6 class="m-0 font-weight-bold text-primary">
                                Attendance Records
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
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($result->num_rows == 0): ?>
                                            <tr>
                                                <td colspan="7" class="text-center">No records found</td>
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
                                                        <button class="btn btn-sm btn-info" onclick="viewDetails(<?php echo $row['id']; ?>)">
                                                            <i class="bi bi-eye me-1"></i>View
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
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Search functionality with jQuery
        $("#searchReport").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#reportTable tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
        
        // Print functionality
        $("#printBtn").click(function() {
            window.print();
        });
    });
    
    function viewDetails(id) {
        window.location.href = `attendance_details.php?id=${id}`;
    }

    function setDateRange(range) {
        const today = new Date();
        let startDate = today;
        let endDate = today;
        
        switch(range) {
            case 'today':
                // Keep as today
                break;
            case 'yesterday':
                startDate = new Date(today);
                endDate = new Date(today);
                startDate.setDate(today.getDate() - 1);
                endDate.setDate(today.getDate() - 1);
                break;
            case 'week':
                startDate = new Date(today);
                startDate.setDate(today.getDate() - today.getDay());
                break;
            case 'month':
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                break;
        }
        
        $('#start_date').val(formatDate(startDate));
        $('#end_date').val(formatDate(endDate));
        $('#filterForm').submit();
    }
    
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    </script>

    <?php require_once '../../includes/admin/footer.php'; ?>
</body>
</html>