<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../../db/connect.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<div class="alert alert-danger">No employee ID provided</div>';
    exit;
}

$employee_id = intval($_GET['id']);

// Get employee details
$query = "SELECT u.*, 
         (SELECT COUNT(*) FROM time_log WHERE employee_id = u.id) AS attendance_count,
         (SELECT COUNT(*) FROM time_log WHERE employee_id = u.id AND status = 'present') AS present_count,
         (SELECT COUNT(*) FROM time_log WHERE employee_id = u.id AND status = 'late') AS late_count,
         (SELECT status FROM time_log WHERE employee_id = u.id AND DATE(time_in) = CURDATE() LIMIT 1) AS today_status
         FROM users u WHERE u.id = ?";

$stmt1 = $conn->prepare($query);
if (!$stmt1) {
    echo '<div class="alert alert-danger">Database error: ' . htmlspecialchars($conn->error) . '</div>';
    exit;
}
$stmt1->bind_param("i", $employee_id);
$stmt1->execute();
$result = $stmt1->get_result();

if ($result->num_rows === 0) {
    echo '<div class="alert alert-danger">Employee not found</div>';
    exit;
}

$employee = $result->fetch_assoc();
$stmt1->close();

// Get recent attendance records
$attendance_query = "SELECT time_in, status FROM time_log 
                    WHERE employee_id = ? 
                    ORDER BY time_in DESC LIMIT 7";
$stmt2 = $conn->prepare($attendance_query);
if (!$stmt2) {
    echo '<div class="alert alert-danger">Database error: ' . htmlspecialchars($conn->error) . '</div>';
    exit;
}
$stmt2->bind_param("i", $employee_id);
$stmt2->execute();
$attendance_result = $stmt2->get_result();

// Calculate absent days (simplified calculation)
$registration_date = new DateTime($employee['created_at']);
$today = new DateTime();
$interval = $registration_date->diff($today);
$total_days = $interval->days;
$working_days = round($total_days * 5/7); // Approximate working days (excluding weekends)
$absent_count = $working_days - ($employee['present_count'] + $employee['late_count']);
if ($absent_count < 0) $absent_count = 0;

// Generate QR code URL
$qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($employee['code']);
?>

<div class="container-fluid p-0">
    <div class="row">
        <div class="col-md-4 text-center">
            <?php if (!empty($employee['code'])): ?>
                <div class="mb-3">
                    <img src="<?php echo $qr_code_url; ?>" 
                         class="img-fluid border p-2" alt="Employee QR Code">
                    <p class="small text-muted mt-2">Employee QR Code</p>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">No QR code available</div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-8">
            <h4><?php echo htmlspecialchars($employee['username']); ?></h4>
            <p class="text-muted mb-3"><?php echo htmlspecialchars($employee['email']); ?></p>
            
            <!-- Attendance Statistics -->
            <div class="row">
                <div class="col-md-3 mb-2">
                    <div class="card bg-success text-white text-center">
                        <div class="card-body p-2">
                            <h5 class="card-title mb-0">Present</h5>
                            <p class="card-text h3"><?php echo $employee['present_count']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="card bg-danger text-white text-center">
                        <div class="card-body p-2">
                            <h5 class="card-title mb-0">Absent</h5>
                            <p class="card-text h3"><?php echo $absent_count; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="card bg-warning text-dark text-center">
                        <div class="card-body p-2">
                            <h5 class="card-title mb-0">Late</h5>
                            <p class="card-text h3"><?php echo $employee['late_count']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="card bg-info text-white text-center">
                        <div class="card-body p-2">
                            <h5 class="card-title mb-0">Total</h5>
                            <p class="card-text h3"><?php echo $employee['attendance_count']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Additional Information -->
            <div class="mt-3 mb-3">
                <table class="table table-sm">
                    <tr>
                        <th>Employee ID:</th>
                        <td><?php echo $employee['id']; ?></td>
                    </tr>
                    <tr>
                        <th>QR Code:</th>
                        <td><span class="badge bg-dark"><?php echo htmlspecialchars($employee['code'] ?? 'Not available'); ?></span></td>
                    </tr>
                    <tr>
                        <th>Today's Status:</th>
                        <td>
                            <?php if ($employee['today_status']): ?>
                                <span class="badge <?php echo ($employee['today_status'] === 'present') ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                    <?php echo ucfirst($employee['today_status']); ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Not logged</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Registered:</th>
                        <td><?php echo date('M d, Y', strtotime($employee['created_at'])); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Recent Attendance History -->
    <div class="row mt-2">
        <div class="col-12">
            <h5 class="border-bottom pb-2">Recent Attendance</h5>
            <?php if ($attendance_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time In</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $attendance_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('M d, Y (D)', strtotime($row['time_in'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($row['time_in'])); ?></td>
                                    <td>
                                        <span class="badge <?php echo ($row['status'] === 'present') ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center text-muted">No attendance records found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
