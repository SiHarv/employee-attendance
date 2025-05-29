<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <script src="../../assets/js/lib/jquery-3.7.1.min.js"></script>
    <script src="../../assets/js/admin/dashboard.js" defer></script>
    <script src="../../assets/js/lib/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Dashboard specific styles */
        .card-fixed-height {
            height: 400px;
            overflow: hidden;
        }
        
        .activity-feed {
            max-height: 320px;
            overflow-y: auto;
            padding-right: 5px;
        }
        
        .chart-container {
            position: relative;
            height: 320px;
            width: 100%;
        }
        
        /* Additional styles */
        .stat-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border-radius: 10px;
            border: none;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 1.8rem;
        }
        
        .activity-item {
            border-left: 3px solid transparent;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            transition: all 0.2s;
        }
        
        .activity-item:hover {
            background-color: rgba(0,0,0,0.02);
        }
        
        .activity-item.present {
            border-left-color: #28a745;
        }
        
        .activity-item.late {
            border-left-color: #ffc107;
        }
        
        .activity-item.absent {
            border-left-color: #dc3545;
        }
        
        .welcome-section {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .current-time {
            font-size: 0.9rem;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <?php
    require_once '../../includes/admin/header.php';
    require_once '../../includes/admin/sidebar.php';
    require_once '../../db/connect.php';

    // Get current date in server's timezone
    $today = date('Y-m-d');

    // Get all non-admin users (employees)
    $total_employees_query = "SELECT COUNT(*) as total FROM users WHERE id != 1";
    $total_employees = $conn->query($total_employees_query)->fetch_assoc()['total'];

    // Count distinct employees present today
    $present_today_query = "SELECT COUNT(DISTINCT employee_id) as total FROM morning_time_log WHERE DATE(time_in) = CURDATE() AND status = 'present'";
    $present_today = $conn->query($present_today_query)->fetch_assoc()['total'];

    // Count distinct employees late today
    $late_today_query = "SELECT COUNT(DISTINCT employee_id) as total FROM morning_time_log WHERE DATE(time_in) = CURDATE() AND status = 'late'";
    $late_today = $conn->query($late_today_query)->fetch_assoc()['total'];

    // Count distinct employees who attended today (present or late)
    $attended_today_query = "SELECT COUNT(DISTINCT employee_id) as count FROM morning_time_log WHERE DATE(time_in) = CURDATE()";
    $attended_today = $conn->query($attended_today_query)->fetch_assoc()['count'];

    // Calculate absent employees today
    $absent_today = $total_employees - $attended_today;

    // Get weekly attendance data for chart - last 7 days including today
    $week_days = [];
    $present_counts = [];
    $late_counts = [];
    $absent_counts = [];
    
    // Show data for the past 7 days, through today
    for ($i = 6; $i >= 0; $i--) {
        $current_date = date('Y-m-d', strtotime("-$i days"));
        $week_days[] = date('D', strtotime($current_date));
        
        // Count employees present on this specific day - using DISTINCT to get accurate counts
        $present_count_query = "SELECT COUNT(DISTINCT employee_id) as count FROM morning_time_log 
                               WHERE DATE(time_in) = '$current_date' 
                               AND status = 'present'";
        $present_count = $conn->query($present_count_query)->fetch_assoc()['count'];
        $present_counts[] = $present_count;
        
        // Count employees late on this specific day - using DISTINCT to get accurate counts
        $late_count_query = "SELECT COUNT(DISTINCT employee_id) as count FROM morning_time_log 
                            WHERE DATE(time_in) = '$current_date' 
                            AND status = 'late'";
        $late_count = $conn->query($late_count_query)->fetch_assoc()['count'];
        $late_counts[] = $late_count;
        
        // Count employees who attended on this specific day - using DISTINCT to get accurate counts
        $attended_query = "SELECT COUNT(DISTINCT employee_id) as count FROM morning_time_log 
                           WHERE DATE(time_in) = '$current_date'";
        $attended_count = $conn->query($attended_query)->fetch_assoc()['count'];
        
        // Calculate absent as total employees minus those who attended
        $absent_counts[] = $total_employees - $attended_count;
    }
    
    // Debug queries to see the actual SQL for verification
    echo "<!-- Debug queries:
    Present: $present_today_query
    Late: $late_today_query
    Attended: $attended_today_query
    -->";
    
    // Get today's activity only for the Recent Activity list
    $recent_activity_query = "SELECT t.*, u.username 
                             FROM morning_time_log t 
                             JOIN users u ON t.employee_id = u.id 
                             WHERE DATE(t.time_in) = '$today'
                             ORDER BY t.time_in DESC 
                             LIMIT 10";
    $recent_activity_result = $conn->query($recent_activity_query);
    ?>

    <div class="main-content">
        <div class="container-fluid mt-4">
            <div class="welcome-section mb-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h2 class="mb-1">Welcome, Admin!</h2>
                        <p class="mb-0">Here's what's happening with your employees today (<?php echo date('F j, Y', strtotime($today)); ?>).</p>
                        <span class="current-time" id="currentDateTime">
                            <?php echo date('l, F j, Y'); ?> | <span id="liveClock"></span>
                        </span>
                    </div>
                    <div class="col-md-6 text-end">
                        <button class="btn btn-light" onclick="window.location.href='scan.php'">
                            <i class="fas fa-qrcode me-2"></i>Open Scanner
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-primary-subtle text-primary me-3">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-0">Total Employees</h6>
                                    <h2 class="mb-0 fw-bold"><?php echo $total_employees; ?></h2>
                                </div>
                            </div>
                            <hr>
                            <div class="text-end">
                                <a href="employees.php" class="btn btn-sm btn-light">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-success-subtle text-success me-3">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-0">Present Today</h6>
                                    <h2 class="mb-0 fw-bold"><?php echo $present_today; ?></h2>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-success">
                                    <i class="fas fa-check me-1"></i>On Time
                                </span>
                                <span class="fw-bold">
                                    <?php echo $total_employees > 0 ? round(($present_today / $total_employees) * 100) : 0; ?>%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-warning-subtle text-warning me-3">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-0">Late Today</h6>
                                    <h2 class="mb-0 fw-bold"><?php echo $late_today; ?></h2>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Late Arrivals
                                </span>
                                <span class="fw-bold">
                                    <?php echo $total_employees > 0 ? round(($late_today / $total_employees) * 100) : 0; ?>%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-danger-subtle text-danger me-3">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-0">Absent Today</h6>
                                    <h2 class="mb-0 fw-bold"><?php echo $absent_today; ?></h2>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-danger">
                                    <i class="fas fa-calendar-times me-1"></i>Not Present
                                </span>
                                <span class="fw-bold">
                                    <?php echo $total_employees > 0 ? round(($absent_today / $total_employees) * 100) : 0; ?>%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Chart and Recent Activity -->
            <div class="row g-4">
                <div class="col-md-8">
                    <div class="card shadow border-0 card-fixed-height">
                        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 fw-bold">Weekly Attendance Overview</h5>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary active">Week</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary">Month</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="attendanceChart"></canvas>
                            </div>
                        </div>
                        <div class="card-footer bg-white py-3 border-0 text-center">
                            <a href="attendance.php" class="text-decoration-none">
                                <i class="fas fa-chart-line me-1"></i>View Detailed Reports
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow border-0 card-fixed-height">
                        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 fw-bold">Today's Activity</h5>
                            <button type="button" class="btn btn-sm btn-light" id="refreshActivity">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="activity-feed px-3 py-2">
                                <?php 
                                if ($recent_activity_result && $recent_activity_result->num_rows > 0) {
                                    while ($activity = $recent_activity_result->fetch_assoc()) {
                                        $time_ago = time_elapsed_string(strtotime($activity['time_in']));
                                        $status_class = ($activity['status'] == 'present') ? 'present' : 'late';
                                        $status_icon = ($activity['status'] == 'present') ? 'check-circle' : 'clock';
                                        $status_color = ($activity['status'] == 'present') ? 'text-success' : 'text-warning';
                                        
                                        echo '<div class="activity-item ' . $status_class . ' mb-2 bg-light rounded">';
                                        echo '  <div class="d-flex justify-content-between align-items-center mb-2">';
                                        echo '    <h6 class="mb-0 fw-bold">' . htmlspecialchars($activity['username']) . '</h6>';
                                        echo '    <small class="text-muted">' . $time_ago . '</small>';
                                        echo '  </div>';
                                        echo '  <div class="d-flex align-items-center">';
                                        echo '    <span class="' . $status_color . ' me-2"><i class="fas fa-' . $status_icon . '"></i></span>';
                                        echo '    <span>' . ucfirst($activity['status']) . ': ' . date('h:i A', strtotime($activity['time_in'])) . '</span>';
                                        echo '  </div>';
                                        echo '</div>';
                                    }
                                } else {
                                    echo '<div class="text-center my-4 text-muted">';
                                    echo '<i class="far fa-calendar-times mb-3" style="font-size: 3rem;"></i>';
                                    echo '<p>No activity recorded today</p>';
                                    echo '</div>';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="card-footer bg-white py-3 text-center border-0">
                            <a href="attendance.php" class="text-decoration-none">
                                <i class="fas fa-list me-1"></i>View All Activity
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions Row -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow border-0">
                        <div class="card-header bg-white py-3 border-0">
                            <h5 class="card-title mb-0 fw-bold">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <a href="employees.php" class="card text-center p-3 h-100 text-decoration-none">
                                        <div class="mb-3">
                                            <i class="fas fa-user-plus fa-2x text-primary"></i>
                                        </div>
                                        <h6 class="mb-0">Add Employee</h6>
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="scan.php" class="card text-center p-3 h-100 text-decoration-none">
                                        <div class="mb-3">
                                            <i class="fas fa-qrcode fa-2x text-success"></i>
                                        </div>
                                        <h6 class="mb-0">Scan QR Code</h6>
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="attendance.php" class="card text-center p-3 h-100 text-decoration-none">
                                        <div class="mb-3">
                                            <i class="fas fa-clipboard-list fa-2x text-info"></i>
                                        </div>
                                        <h6 class="mb-0">Attendance Reports</h6>
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="settings.php" class="card text-center p-3 h-100 text-decoration-none">
                                        <div class="mb-3">
                                            <i class="fas fa-cog fa-2x text-secondary"></i>
                                        </div>
                                        <h6 class="mb-0">Settings</h6>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Live clock function
    function updateClock() {
        const now = new Date();
        const hours = now.getHours();
        const minutes = now.getMinutes();
        const seconds = now.getSeconds();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        
        const formattedHours = hours % 12 || 12;
        const formattedMinutes = minutes < 10 ? '0' + minutes : minutes;
        const formattedSeconds = seconds < 10 ? '0' + seconds : seconds;
        
        document.getElementById('liveClock').textContent = 
            `${formattedHours}:${formattedMinutes}:${formattedSeconds} ${ampm}`;
    }
    
    // Start the clock when the document is ready
    document.addEventListener('DOMContentLoaded', function() {
        updateClock();
        setInterval(updateClock, 1000);
        
        // Refresh activity button
        document.getElementById('refreshActivity').addEventListener('click', function() {
            this.querySelector('i').classList.add('fa-spin');
            setTimeout(() => {
                this.querySelector('i').classList.remove('fa-spin');
                window.location.reload();
            }, 1000);
        });
    });

    // Initialize attendance chart with actual data
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($week_days); ?>,
                datasets: [
                    {
                        label: 'Present',
                        data: <?php echo json_encode($present_counts); ?>,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2,
                        pointBackgroundColor: '#28a745'
                    },
                    {
                        label: 'Late',
                        data: <?php echo json_encode($late_counts); ?>,
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2,
                        pointBackgroundColor: '#ffc107'
                    },
                    {
                        label: 'Absent',
                        data: <?php echo json_encode($absent_counts); ?>,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2,
                        pointBackgroundColor: '#dc3545'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(255, 255, 255, 0.8)',
                        titleColor: '#000',
                        bodyColor: '#000',
                        borderColor: '#ddd',
                        borderWidth: 1
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    });
    </script>

<?php
// Helper function to format time ago
function time_elapsed_string($timestamp) {
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $mins = round($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = round($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 172800) {
        return 'yesterday';
    } else {
        return date('M j', $timestamp);
    }
}
?>
</body>
</html>