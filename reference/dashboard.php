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

require_once '../db/config.php';

// Get summary data
try {
    // Get total employees
    $stmt = $pdo->query("SELECT COUNT(*) FROM employees");
    $totalEmployees = $stmt->fetchColumn();
    
    // Get today's attendance count - updated to match employees.php logic
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT t.employee_id) 
        FROM time_logs t
        WHERE DATE(t.time_in) = ?
    ");
    $stmt->execute([$today]);
    $presentToday = $stmt->fetchColumn();
    
    // Calculate absent employees
    $absentToday = $totalEmployees - $presentToday;
    
    // Get late employees - updated to match employees.php logic
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT t.employee_id) 
        FROM time_logs t
        WHERE DATE(t.time_in) = ? 
        AND TIME(t.time_in) > '09:00:00'
    ");
    $stmt->execute([$today]);
    $lateToday = $stmt->fetchColumn();
    
    // Calculate present but not late (on-time employees)
    $onTimeToday = $presentToday - $lateToday;
    
    // Get today's attendance list - making sure it returns all employees including those without records
    $stmt = $pdo->prepare("
        SELECT 
            e.id, 
            e.name, 
            e.email, 
            t.time_in, 
            t.time_out,
            CASE
                WHEN t.time_out IS NULL OR t.time_out = '0000-00-00 00:00:00' THEN NULL
                ELSE ROUND(TIMESTAMPDIFF(MINUTE, t.time_in, t.time_out) / 60, 1)
            END as total_hours
        FROM 
            employees e
        LEFT JOIN 
            time_logs t ON e.id = t.employee_id AND DATE(t.time_in) = ?
        ORDER BY 
            e.name ASC
    ");
    $stmt->execute([$today]);
    $todayAttendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get weekly attendance summary - fixing to calculate correctly
    $weekStart = date('Y-m-d', strtotime('this week Monday'));
    $weekEnd = date('Y-m-d', strtotime('this week Sunday'));
    
    // Initialize whole-week array with zeros
    $weekDays = [];
    $current = new DateTime($weekStart);
    $end = new DateTime($weekEnd);
    $end->modify('+1 day'); // Include end date
    
    while ($current < $end) {
        $day = $current->format('Y-m-d');
        $weekDays[$day] = 0;
        $current->modify('+1 day');
    }
    
    // Get weekly data from database
    $stmt = $pdo->prepare("
        SELECT 
            DATE(time_in) as date,
            COUNT(DISTINCT employee_id) as present_count
        FROM 
            time_logs
        WHERE 
            DATE(time_in) BETWEEN ? AND ?
        GROUP BY 
            DATE(time_in)
        ORDER BY 
            date ASC
    ");
    $stmt->execute([$weekStart, $weekEnd]);
    $weeklyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Merge database data with initialized week array
    foreach ($weeklyData as $day) {
        if (isset($weekDays[$day['date']])) {
            $weekDays[$day['date']] = (int)$day['present_count'];
        }
    }
    
    // Format for chart
    $weeklyLabels = [];
    $weeklyValues = [];
    foreach ($weekDays as $date => $count) {
        $weeklyLabels[] = date('D', strtotime($date));
        $weeklyValues[] = $count;
    }
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Include header and sidebar
include_once '../includes/admin_header.php';
include_once '../includes/admin_sidebar.php';
?>

<style>
    body {
        padding-top: 0 !important;
        margin-top: 0 !important;
    }
    
    .content-wrapper {
        background-color: #f8f9fa;
        position: relative;
    }
    
    .card {
        margin-bottom: 20px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        border: none;
    }
</style>

<div class="content-wrapper">
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Dashboard</h1>
        
        <!-- Stats Cards -->
        <div class="row mb-4">
            <!-- Total Employees Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Employees</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalEmployees; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-people-fill" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Present Today Card - Updated to show on-time employees -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-success text-white shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">
                                    Present Today</div>
                                <div class="h5 mb-0 font-weight-bold"><?php echo $onTimeToday; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-check-circle-fill" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Absent Today Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-danger text-white shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">
                                    Absent Today</div>
                                <div class="h5 mb-0 font-weight-bold"><?php echo $absentToday; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-x-circle-fill" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Late Today Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-warning text-white shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">
                                    Late Today</div>
                                <div class="h5 mb-0 font-weight-bold"><?php echo $lateToday; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-clock-fill" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Auto-Absence System Notification -->
        <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
            <h5><i class="bi bi-info-circle-fill me-2"></i> Automatic Absence Marking System</h5>
            <p>The system automatically marks all employees as <strong>absent</strong> at 8:35 AM each working day. When employees check in, their status will update automatically.</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        
        <!-- Content Row -->
        <div class="row">
            <!-- Area Chart -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Weekly Attendance Overview</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="attendanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Today's Attendance -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Today's Attendance</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($todayAttendance)): ?>
                                        <tr>
                                            <td colspan="2" class="text-center">No attendance records for today</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($todayAttendance as $record): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($record['name']); ?></td>
                                                <td>
                                                    <?php if (!empty($record['time_in'])): ?>
                                                        <?php if (strtotime(date('Y-m-d') . ' 09:00:00') < strtotime($record['time_in'])): ?>
                                                            <span class="badge bg-warning text-dark">Late</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-success">Present</span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Absent</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Attendance Records -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Recent Attendance Records</h6>
                    </div>
                    <div class="card-body">
                        <?php
                        // Get recent attendance records with proper total hours calculation
                        $stmt = $pdo->query("
                            SELECT 
                                e.name,
                                t.time_in,
                                t.time_out,
                                CASE
                                    WHEN t.time_out IS NULL OR t.time_out = '0000-00-00 00:00:00' THEN NULL
                                    ELSE ROUND(TIMESTAMPDIFF(MINUTE, t.time_in, t.time_out) / 60, 1)
                                END as total_hours
                            FROM 
                                time_logs t
                            JOIN 
                                employees e ON t.employee_id = e.id
                            ORDER BY 
                                t.time_in DESC
                            LIMIT 10
                        ");
                        $recentAttendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Date</th>
                                        <th>Time In</th>
                                        <th>Time Out</th>
                                        <th>Hours</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recentAttendance)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No recent attendance records</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recentAttendance as $record): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($record['name']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($record['time_in'])); ?></td>
                                                <td><?php echo date('h:i A', strtotime($record['time_in'])); ?></td>
                                                <td>
                                                    <?php if (!empty($record['time_out']) && $record['time_out'] != '0000-00-00 00:00:00'): ?>
                                                        <?php echo date('h:i A', strtotime($record['time_out'])); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($record['total_hours'])): ?>
                                                        <?php echo number_format($record['total_hours'], 1); ?> hrs
                                                    <?php else: ?>
                                                        <span class="text-muted">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (strtotime(date('Y-m-d', strtotime($record['time_in'])) . ' 09:00:00') < strtotime($record['time_in'])): ?>
                                                        <span class="badge bg-warning text-dark">Late</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Present</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Weekly attendance chart
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    const attendanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($weeklyLabels); ?>,
            datasets: [{
                label: 'Present',
                data: <?php echo json_encode($weeklyValues); ?>,
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                borderColor: 'rgba(78, 115, 223, 1)',
                pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
                pointBorderWidth: 2,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>