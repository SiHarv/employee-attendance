<?php
require_once '../db/config.php';

// Check if session exists
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo '<div class="alert alert-danger">Authentication required.</div>';
    exit();
}

// Check if employee ID is provided
if (!isset($_GET['employee_id']) || !is_numeric($_GET['employee_id'])) {
    echo '<div class="alert alert-danger">Invalid employee ID.</div>';
    exit();
}

// Ensure this is a read-only view operation
$viewOnly = isset($_GET['view_only']) && $_GET['view_only'] === 'true';

$employeeId = (int)$_GET['employee_id'];

try {
    // Get employee details - read-only operation
    $stmt = $pdo->prepare("
        SELECT id, name, email, qr_code, created_at
        FROM employees
        WHERE id = ?
    ");
    $stmt->execute([$employeeId]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$employee) {
        echo '<div class="alert alert-danger">Employee not found.</div>';
        exit();
    }
    
    // Get recent attendance for this employee (last 7 days) - using time_logs table instead of attendance_reports
    $stmt = $pdo->prepare("
        SELECT 
            DATE(time_in) as report_date,
            CASE 
                WHEN TIME(time_in) > '09:00:00' THEN 'late'
                ELSE 'present'
            END as status,
            CASE
                WHEN time_out IS NULL OR time_out = '0000-00-00 00:00:00' THEN NULL
                ELSE ROUND(TIMESTAMPDIFF(MINUTE, time_in, time_out) / 60, 1)
            END as total_hours,
            time_in,
            time_out
        FROM 
            time_logs
        WHERE 
            employee_id = ?
        ORDER BY 
            time_in DESC
        LIMIT 7
    ");
    $stmt->execute([$employeeId]);
    $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate statistics from time_logs instead of attendance_reports
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(CASE WHEN TIME(time_in) <= '09:00:00' THEN 1 END) as present_count,
            COUNT(CASE WHEN TIME(time_in) > '09:00:00' THEN 1 END) as late_count,
            AVG(CASE 
                WHEN time_out IS NOT NULL AND time_out != '0000-00-00 00:00:00' 
                THEN ROUND(TIMESTAMPDIFF(MINUTE, time_in, time_out) / 60, 1)
                ELSE NULL
            END) as avg_hours
        FROM time_logs
        WHERE employee_id = ?
    ");
    $stmt->execute([$employeeId]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Calculate absent days based on employee registration date - improved version
    $registrationDate = new DateTime($employee['created_at']);
    $today = new DateTime();
    
    // Ensure we don't count future days
    if ($today > $registrationDate) {
        // Get all dates the employee was present since registration
        $stmt = $pdo->prepare("
            SELECT DISTINCT DATE(time_in) as present_date
            FROM time_logs
            WHERE employee_id = ? AND DATE(time_in) BETWEEN ? AND ?
        ");
        $stmt->execute([$employeeId, $registrationDate->format('Y-m-d'), $today->format('Y-m-d')]);
        $presentDates = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Count working days (Monday to Friday) since employee registration
        $workingDays = 0;
        $currentDate = clone $registrationDate;
        $dayNames = [];
        
        while ($currentDate <= $today) {
            $dayOfWeek = $currentDate->format('N'); // 1 (Monday) to 7 (Sunday)
            $currentDateStr = $currentDate->format('Y-m-d');
            
            // Only count Monday through Friday (1-5) as working days
            // Also don't count today if it's not past work hours yet
            if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                // Don't count future dates
                if ($currentDate < $today) {
                    $workingDays++;
                    $dayNames[] = $currentDateStr; // For debugging
                }
            }
            
            $currentDate->modify('+1 day');
        }
        
        // Create arrays of working days and present days for easier diff calculation
        $workingDaysArray = $dayNames;
        $presentDaysArray = [];
        foreach ($presentDates as $presentDate) {
            $presentDaysArray[] = $presentDate;
        }
        
        // Calculate absences (working days that user wasn't present)
        $absentDays = array_diff($workingDaysArray, $presentDaysArray);
        $absentCount = count($absentDays);

    } else {
        // Employee was registered today or in the future
        $absentCount = 0;
    }
    
    // Generate QR Code image URL
    $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($employee['qr_code']);
    
    // Output HTML for modal
    ?>
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-md-4 text-center mb-3">
                <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code" class="img-fluid mb-2" style="max-width: 150px;">
                <div class="d-grid gap-2">
                    <a href="<?php echo $qrCodeUrl; ?>" download="qrcode-<?php echo $employee['qr_code']; ?>.png" class="btn btn-sm btn-outline-primary">Download QR</a>
                </div>
            </div>
            <div class="col-md-8">
                <h4><?php echo htmlspecialchars($employee['name']); ?></h4>
                <p class="text-muted">Employee ID: <?php echo $employee['id']; ?></p>
                
                <table class="table table-sm">
                    <tr>
                        <th>QR Code:</th>
                        <td><span class="badge bg-dark"><?php echo htmlspecialchars($employee['qr_code']); ?></span></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?php echo htmlspecialchars($employee['email']); ?></td>
                    </tr>
                    <tr>
                        <th>Registered On:</th>
                        <td><?php echo date('M d, Y', strtotime($employee['created_at'])); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <h5>Attendance Statistics</h5>
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <div class="card bg-success text-white text-center">
                            <div class="card-body p-2">
                                <h5 class="card-title mb-0">Present</h5>
                                <p class="card-text h2"><?php echo $stats['present_count'] ?? 0; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="card bg-danger text-white text-center">
                            <div class="card-body p-2">
                                <h5 class="card-title mb-0">Absent</h5>
                                <p class="card-text h2"><?php echo $absentCount ?? 0; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="card bg-warning text-dark text-center">
                            <div class="card-body p-2">
                                <h5 class="card-title mb-0">Late</h5>
                                <p class="card-text h2"><?php echo $stats['late_count'] ?? 0; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="card bg-info text-white text-center">
                            <div class="card-body p-2">
                                <h5 class="card-title mb-0">Avg Hours</h5>
                                <p class="card-text h2"><?php echo number_format($stats['avg_hours'] ?? 0, 1); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <h5>Recent Attendance</h5>
                <?php if (empty($attendance)): ?>
                    <p class="text-center text-muted">No recent attendance records.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendance as $record): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y (D)', strtotime($record['report_date'])); ?></td>
                                        <td>
                                            <?php if ($record['status'] == 'present'): ?>
                                                <span class="badge bg-success">Present</span>
                                            <?php elseif ($record['status'] == 'absent'): ?>
                                                <span class="badge bg-danger">Absent</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Late</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo !empty($record['time_in']) ? date('h:i A', strtotime($record['time_in'])) : 'N/A'; ?>
                                        </td>
                                        <td>
                                            <?php echo (!empty($record['time_out']) && $record['time_out'] != '0000-00-00 00:00:00') ? date('h:i A', strtotime($record['time_out'])) : 'N/A'; ?>
                                        </td>
                                        <td>
                                            <?php echo !empty($record['total_hours']) ? number_format($record['total_hours'], 1) . ' hrs' : 'N/A'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error retrieving employee details: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
