<?php
// Start the session
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../../includes/admin/header.php';
require_once '../../includes/admin/sidebar.php';
require_once '../../db/connect.php';

// Fetch current settings
$query = "SELECT * FROM settings WHERE id = 1";
$result = $conn->query($query);
$settings = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $time_in = $_POST['time_in'];
    $threshold_minute = $_POST['threshold_minute'];
    $time_out = $_POST['time_out'];

    $stmt = $conn->prepare("UPDATE settings SET time_in = ?, threshold_minute = ?, time_out = ? WHERE id = 1");
    $stmt->bind_param("sis", $time_in, $threshold_minute, $time_out);
    
    if ($stmt->execute()) {
        $success_message = "Settings updated successfully!";
    } else {
        $error_message = "Error updating settings: " . $conn->error;
    }
}
?>
    <script src="../../assets/js/lib/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="../../assets/js/lib/sweetalert2.all.min.js"></script>

<div class="main-content">
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <!-- Tabs for Settings and File Exports -->
                <ul class="nav nav-tabs mb-3" id="settingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance-settings" type="button" role="tab" aria-controls="attendance-settings" aria-selected="true">
                            Attendance Settings
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="exports-tab" data-bs-toggle="tab" data-bs-target="#file-exports" type="button" role="tab" aria-controls="file-exports" aria-selected="false">
                            File Exports
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="settingsTabsContent">
                    <!-- Attendance Settings Tab -->
                    <div class="tab-pane fade show active" id="attendance-settings" role="tabpanel" aria-labelledby="attendance-tab">
                        <div class="card shadow border-0">
                            <div class="card-header bg-primary text-white">
                                <h3 class="card-title mb-0">Attendance Settings</h3>
                            </div>
                            <div class="card-body p-4">
                                <div id="settings-message"></div>
                                <form id="attendanceSettingsForm" class="needs-validation" novalidate>
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Time In</label>
                                        <input type="time" class="form-control form-control-lg" name="time_in" 
                                            value="<?php echo $settings['time_in']; ?>" required>
                                        <small class="text-muted">Set the standard time in for employees</small>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Late Threshold (minutes)</label>
                                        <input type="number" class="form-control form-control-lg" name="threshold_minute" 
                                            value="<?php echo $settings['threshold_minute']; ?>" required>
                                        <small class="text-muted">Minutes after Time In before marking as Late</small>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Time Out</label>
                                        <input type="time" class="form-control form-control-lg" name="time_out" 
                                            value="<?php echo $settings['time_out']; ?>" required>
                                        <small class="text-muted">Set the standard time out for employees</small>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">QR Pin</label>
                                        <input type="password" class="form-control form-control-lg" name="qr_pin"
                                            value="<?php echo isset($settings['qr_pin']) ? htmlspecialchars($settings['qr_pin']) : ''; ?>" autocomplete="off">
                                        <small class="text-muted">Set or change the QR Pin (hidden)</small>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-lg">Save Settings</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- File Exports Tab -->
                    <div class="tab-pane fade" id="file-exports" role="tabpanel" aria-labelledby="exports-tab">
                        <div class="card shadow border-0">
                            <div class="card-header bg-success text-white">
                                <h3 class="card-title mb-0">File Exports</h3>
                            </div>
                            <div class="card-body p-4">
                                <form id="exportForm" class="mb-3">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Export File Name</label>
                                        <input type="text" class="form-control" id="exportFileName" value="attendance_report" required>
                                        <small class="text-muted">Set the file name for the exported file (without extension)</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Date Range</label>
                                        <div class="row">
                                            <div class="col">
                                                <input type="date" class="form-control" id="exportDateFrom" placeholder="From">
                                            </div>
                                            <div class="col">
                                                <input type="date" class="form-control" id="exportDateTo" placeholder="To">
                                            </div>
                                        </div>
                                        <small class="text-muted">Select the date range for the report (optional)</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">File Format</label>
                                        <select class="form-select" id="exportFileFormat">
                                            <option value="excel" selected>Excel (.xlsx)</option>
                                            <option value="csv">CSV (.csv)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Columns to Include</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="employee_id" id="colEmployeeId" checked>
                                            <label class="form-check-label" for="colEmployeeId">Employee ID</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="name" id="colName" checked>
                                            <label class="form-check-label" for="colName">Name</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="date" id="colDate" checked>
                                            <label class="form-check-label" for="colDate">Date</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="time_in" id="colTimeIn" checked>
                                            <label class="form-check-label" for="colTimeIn">Time In</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="time_out" id="colTimeOut" checked>
                                            <label class="form-check-label" for="colTimeOut">Time Out</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="status" id="colStatus" checked>
                                            <label class="form-check-label" for="colStatus">Status</label>
                                        </div>
                                    </div>
                                </form>
                                <div class="d-flex flex-column flex-md-row gap-3">
                                    <a href="../../controller/admin/export_attendance.php" id="exportExcelBtn" class="btn btn-success">
                                        <i class="bi bi-file-earmark-excel me-1"></i> Export Attendance to Excel
                                    </a>
                                    <button type="button" class="btn btn-info" onclick="window.print();">
                                        <i class="bi bi-printer me-1"></i> Print Attendance Report
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Tabs -->
            </div>
        </div>
    </div>
</div>

<script>
// Add client-side validation if needed
document.querySelector('form').addEventListener('submit', function(e) {
    const timeIn = document.querySelector('input[name="time_in"]').value;
    const timeOut = document.querySelector('input[name="time_out"]').value;
    const threshold = document.querySelector('input[name="threshold_minute"]').value;

    if (parseInt(threshold) < 0) {
        e.preventDefault();
        alert('Threshold minutes cannot be negative');
    }
});

// Handle export file name and settings
document.getElementById('exportExcelBtn').addEventListener('click', function(e) {
    const fileName = document.getElementById('exportFileName').value.trim();
    const dateFrom = document.getElementById('exportDateFrom').value;
    const dateTo = document.getElementById('exportDateTo').value;
    const fileFormat = document.getElementById('exportFileFormat').value;
    const columns = [];
    if (document.getElementById('colEmployeeId').checked) columns.push('employee_id');
    if (document.getElementById('colName').checked) columns.push('name');
    if (document.getElementById('colDate').checked) columns.push('date');
    if (document.getElementById('colTimeIn').checked) columns.push('time_in');
    if (document.getElementById('colTimeOut').checked) columns.push('time_out');
    if (document.getElementById('colStatus').checked) columns.push('status');

    if (!fileName) {
        alert('Please enter a file name for export.');
        e.preventDefault();
        return;
    }
    if (columns.length === 0) {
        alert('Please select at least one column to include.');
        e.preventDefault();
        return;
    }

    // Always reset href before setting
    this.setAttribute('href', '#');

    // Build query string
    let params = 'filename=' + encodeURIComponent(fileName) +
        '&format=' + encodeURIComponent(fileFormat) +
        '&columns=' + encodeURIComponent(columns.join(','));
    if (dateFrom) params += '&date_from=' + encodeURIComponent(dateFrom);
    if (dateTo) params += '&date_to=' + encodeURIComponent(dateTo);

    // Use correct relative path from settings.php to export_attendance.php
    this.setAttribute('href', '../../controller/admin/export_attendance.php?' + params);
});

// AJAX for Attendance Settings form
document.getElementById('attendanceSettingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    fetch('../../controller/admin/admin_settings.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        if (data.includes('successfully')) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: data,
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data,
                timer: 3000,
                showConfirmButton: false
            });
        }
    });
});
</script>
<?php require_once '../../includes/admin/footer.php'; ?>