<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Define constant to allow access to include files
define('ADMIN_ACCESS', true);

// Get filter parameters
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '2025-05-01'; // Changed from today to May 1, 2025
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');       // Keep today as end date
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$employeeId = isset($_GET['employee_id']) ? intval($_GET['employee_id']) : null;

// Include necessary files
require_once '../db/config.php';

// Direct SQL Queries - No external controller
try {
    // Get employees for dropdown
    $employees = [];
    $employeesStmt = $pdo->query("SELECT id, name FROM employees ORDER BY name ASC");
    if ($employeesStmt) {
        $employees = $employeesStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get report data - now from time_logs only
    $reportData = [];
    
    // Modify the SQL query to include absences in the time_logs table
    $sql = "
        SELECT 
            t.id, 
            t.employee_id, 
            e.name, 
            e.qr_code, 
            e.email,
            DATE(t.time_in) as date, 
            CASE 
                WHEN t.status = 'absent' THEN 'absent'
                WHEN TIME(t.time_in) > '09:00:00' THEN 'late'
                ELSE 'present'
            END as status,
            t.time_in,
            t.time_out
        FROM 
            time_logs t
        JOIN 
            employees e ON t.employee_id = e.id
        WHERE 
            DATE(t.time_in) BETWEEN ? AND ?
    ";
    
    $params = [$startDate, $endDate];
    
    if ($status && $status != 'all') {
        if ($status == 'present') {
            $sql .= " AND TIME(t.time_in) <= '09:00:00'";
        } else if ($status == 'late') {
            $sql .= " AND TIME(t.time_in) > '09:00:00'";
        }
    }
    
    if ($employeeId) {
        $sql .= " AND t.employee_id = ?";
        $params[] = $employeeId;
    }
    
    // Change the ORDER BY clause to sort by date in ascending order
    $sql .= " ORDER BY t.time_in ASC, e.name ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $reportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate statistics
    $totalRecords = count($reportData);
    $presentCount = 0;
    $absentCount = 0;
    $lateCount = 0;
    $totalHours = 0;
    
    // Process each record and calculate total hours
    foreach ($reportData as &$record) {
        // Calculate total hours from time_in and time_out
        if (!empty($record['time_in']) && !empty($record['time_out']) && $record['time_out'] != '0000-00-00 00:00:00') {
            $timeIn = strtotime($record['time_in']);
            $timeOut = strtotime($record['time_out']);
            
            if ($timeOut > $timeIn) {
                $hours = round(($timeOut - $timeIn) / 3600, 1); // Changed from 2 to 1 decimal place
                $record['total_hours'] = $hours;
                $totalHours += $hours;
            }
        } else {
            $record['total_hours'] = null;
        }
        
        // Count by status
        if ($record['status'] == 'present') {
            $presentCount++;
        } elseif ($record['status'] == 'late') {
            $lateCount++;
        }
    }
    
    // Calculate any absent employees for the date range
    $absentSql = "
        SELECT COUNT(DISTINCT e.id) as absent_count
        FROM employees e
        LEFT JOIN time_logs t ON e.id = t.employee_id AND DATE(t.time_in) BETWEEN ? AND ?
        WHERE t.id IS NULL
    ";
    
    $absentStmt = $pdo->prepare($absentSql);
    $absentStmt->execute([$startDate, $endDate]);
    $absentCount = $absentStmt->fetchColumn();
    
    $avgHours = ($presentCount + $lateCount) > 0 ? round($totalHours / ($presentCount + $lateCount), 1) : 0;
    
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}

// Include admin header and sidebar
include_once '../includes/admin_header.php';
include_once '../includes/admin_sidebar.php';
?>

<style>
    /* Base styles */
    body {
        padding-top: 0 !important;
        margin-top: 0 !important;
    }
    
    .content-wrapper {
        background-color: #f8f9fa;
        position: relative;
    }
    
    /* Critical Modal Fixes */
    .modal {
        background: rgba(0, 0, 0, 0.5);
    }
    
    .modal-dialog {
        margin: 100px auto !important;
        max-width: 800px;
        width: 95%;
    }
    
    .modal-content {
        position: relative;
        z-index: 2000;
        pointer-events: auto !important;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.5);
    }
    
    /* Remove default backdrop */
    .modal-backdrop {
        display: none !important;
    }
    
    /* Make sure cards are visible with proper spacing */
    .card {
        margin-bottom: 20px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        border: none;
    }
    
    /* Add scroll-to-top button */
    .scroll-to-top {
        position: fixed;
        right: 15px;
        bottom: 15px;
        z-index: 100;
        background: #4e73df;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.3s;
    }
    
    .scroll-to-top.show {
        opacity: 1;
    }
</style>

<div class="content-wrapper">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3">Attendance Reports</h2>
            
            <div>
                <a href="generate_report.php?start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>&status=<?php echo $status; ?>&employee_id=<?php echo $employeeId; ?>" class="btn btn-success" target="_blank">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export to CSV
                </a>
                <button type="button" class="btn btn-info" id="printBtn">
                    <i class="bi bi-printer me-1"></i> Print Report
                </button>
            </div>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <h5>Error</h5>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>
        
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
                <div class="card bg-danger text-white shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">
                                    Absent</div>
                                <div class="h5 mb-0 font-weight-bold"><?php echo $absentCount; ?></div>
                                <div class="mt-1 small">
                                    <?php echo $totalRecords > 0 ? round(($absentCount / $totalRecords) * 100) : 0; ?>% of total
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-x-circle-fill" style="font-size: 2rem;"></i>
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
                                    Total: <?php echo $totalHours; ?> hours
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
                <h6 class="m-0 font-weight-bold text-primary">Filter Reports</h6>
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
                                <a href="reports.php" class="btn btn-outline-secondary">
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
                    <?php if(!empty($reportData)): ?>
                        <span class="text-muted ms-2">(<?php echo count($reportData); ?> records)</span>
                    <?php endif; ?>
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
                                <th>QR Code</th>
                                <th>Date</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Total Hours</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reportData)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No records found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reportData as $report): ?>
                                    <tr class="<?php echo getStatusClass($report['status']); ?>">
                                        <td><?php echo htmlspecialchars($report['name']); ?></td>
                                        <td><span class="badge bg-dark"><?php echo htmlspecialchars($report['qr_code']); ?></span></td>
                                        <td><?php echo date('M d, Y', strtotime($report['date'])); ?></td>
                                        <td>
                                            <?php if ($report['status'] == 'absent'): ?>
                                                <span class="text-muted">N/A</span>
                                            <?php elseif (!empty($report['time_in'])): ?>
                                                <span class="text-success"><?php echo date('h:i A', strtotime($report['time_in'])); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($report['status'] == 'absent'): ?>
                                                <span class="text-muted">N/A</span>
                                            <?php elseif (!empty($report['time_out']) && $report['time_out'] != '0000-00-00 00:00:00'): ?>
                                                <span class="text-danger"><?php echo date('h:i A', strtotime($report['time_out'])); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($report['status'] == 'absent'): ?>
                                                <span class="text-muted">0.0 hrs</span>
                                            <?php elseif (!empty($report['time_in']) && !empty($report['time_out']) && $report['time_out'] != '0000-00-00 00:00:00' && isset($report['total_hours'])): ?>
                                                <?php echo number_format($report['total_hours'], 1); ?> hrs
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo getStatusBadge($report['status']); ?>">
                                                <?php echo ucfirst(htmlspecialchars($report['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="javascript:void(0);" onclick="viewEmployeeDetails(<?php echo $report['employee_id']; ?>)" class="btn btn-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4 mb-4">
            <p>End of report</p>
        </div>
    </div>
    
    <!-- Employee Details Modal -->
    <div class="modal fade" id="employeeDetailsModal" data-bs-backdrop="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Employee Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="employeeDetailsContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p>Loading employee details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="mt-5 py-3 text-center text-muted">
        <p>&copy; <?php echo date('Y'); ?> Employee Time Tracking System</p>
    </footer>
</div>

<!-- Add a scroll-to-top button -->
<a href="#" class="scroll-to-top" id="scrollTop">
    <i class="bi bi-arrow-up"></i>
</a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Search functionality
    document.getElementById('searchReport').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('#reportTable tbody tr');
        
        tableRows.forEach(row => {
            const name = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
            const qrCode = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const date = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            const status = row.querySelector('td:nth-child(7)').textContent.toLowerCase();
            
            if (name.includes(searchValue) || qrCode.includes(searchValue) || 
                date.includes(searchValue) || status.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // Date range presets
    function setDateRange(range) {
        const today = new Date();
        let startDate = new Date();
        let endDate = new Date();
        
        switch (range) {
            case 'today':
                // Already set
                break;
            case 'yesterday':
                startDate.setDate(today.getDate() - 1);
                endDate.setDate(today.getDate() - 1);
                break;
            case 'week':
                startDate.setDate(today.getDate() - today.getDay()); // Start of week (Sunday)
                break;
            case 'month':
                startDate.setDate(1); // First day of month
                break;
            default:
                return;
        }
        
        document.getElementById('start_date').value = formatDate(startDate);
        document.getElementById('end_date').value = formatDate(endDate);
        document.getElementById('filterForm').submit();
    }
    
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    // View employee details
    function viewEmployeeDetails(employeeId) {
        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('employeeDetailsModal'));
        modal.show();
        
        // Load employee details via AJAX
        $.ajax({
            url: '../controller/get_employee_details.php',
            type: 'GET',  // Changed from POST to GET to avoid modifying data
            data: { 
                employee_id: employeeId,
                view_only: true  // Add parameter to indicate read-only operation
            },
            success: function(response) {
                $('#employeeDetailsContent').html(response);
            },
            error: function() {
                $('#employeeDetailsContent').html('<div class="alert alert-danger">Error loading employee details</div>');
            }
        });
    }
    
    // Print report
    document.getElementById('printBtn').addEventListener('click', function() {
        const printContents = document.getElementById('reportTableContainer').innerHTML;
        const originalContents = document.body.innerHTML;
        
        document.body.innerHTML = `
            <div style="padding: 20px;">
                <h2 style="text-align: center;">Employee Time Tracking Report</h2>
                <p style="text-align: center;">
                    <strong>Period:</strong> ${document.getElementById('start_date').value} to ${document.getElementById('end_date').value}
                </p>
                ${printContents}
            </div>
        `;
        
        window.print();
        document.body.innerHTML = originalContents;
        location.reload();
    });
    
    // Ensure content is visible by scrolling to top on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Scroll to top of page
        window.scrollTo(0, 0);
        
        // Insert the notification at the top of the container
        const container = document.querySelector('.container-fluid');
        if (container && container.firstChild) {
            container.insertBefore(notification, container.firstChild);
            
            // Auto-dismiss after 3 seconds
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 500);
            }, 3000);
        }
        
        // Show/hide scroll-to-top button
        const scrollTopBtn = document.getElementById('scrollTop');
        window.addEventListener('scroll', function() {
            if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                scrollTopBtn.classList.add('show');
            } else {
                scrollTopBtn.classList.remove('show');
            }
        });
        
        // Scroll to top when button is clicked
        scrollTopBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({top: 0, behavior: 'smooth'});
        });
    });
</script>

<?php
// Helper functions
function getStatusClass($status) {
    switch ($status) {
        case 'absent': return 'table-danger';
        case 'late': return 'table-warning';
        case 'present': return 'table-success';
        default: return '';
    }
}

function getStatusBadge($status) {
    switch ($status) {
        case 'absent': return 'bg-danger';
        case 'late': return 'bg-warning text-dark';
        case 'present': return 'bg-success';
        default: return 'bg-secondary';
    }
}
?>