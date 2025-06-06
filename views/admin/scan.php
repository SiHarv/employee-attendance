<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../../includes/admin/header.php';
require_once '../../includes/admin/sidebar.php';
require_once '../../db/connect.php';

// Get recent scans for the day
$today = date('Y-m-d');
$recent_scans_query = "SELECT tl.*, u.username, u.code, 
                      CASE 
                        WHEN tl.source = 'morning' THEN 'Morning'
                        WHEN tl.source = 'afternoon' THEN 'Afternoon'
                      END AS period_label
                      FROM (
                          SELECT *, 'morning' as source FROM morning_time_log
                          WHERE DATE(time_in) = CURDATE()
                          UNION ALL
                          SELECT *, 'afternoon' as source FROM afternoon_time_log
                          WHERE DATE(time_in) = CURDATE()
                      ) tl 
                      JOIN users u ON tl.employee_id = u.id 
                      ORDER BY tl.time_in DESC 
                      LIMIT 10";
$result = $conn->query($recent_scans_query);
$recent_scans = $result;

// Get settings
$settings_query = "SELECT set_am_time_in, set_am_time_out, set_pm_time_in, set_pm_time_out, threshold_minute FROM settings WHERE id = 1";
$settings_result = $conn->query($settings_query);
$settings = $settings_result->fetch_assoc();

if (!$settings) {
    $settings = [
        'set_am_time_in' => '08:00:00',
        'set_am_time_out' => '17:00:00',
        'set_pm_time_in' => '13:00:00',
        'set_pm_time_out' => '17:00:00',
        'threshold_minute' => 15
    ];
}
$set_am_time_out = $settings['set_am_time_out'];
$threshold_minute = $settings['threshold_minute'];

// Format times for display (Update the PHP time formatting)
$am_expected_time = date('h:i A', strtotime($settings['set_am_time_in']));
$am_late_time = date('h:i A', strtotime($settings['set_am_time_in']) + ($threshold_minute * 60));
$pm_expected_time = date('h:i A', strtotime($settings['set_pm_time_in']));
$pm_late_time = date('h:i A', strtotime($settings['set_pm_time_in']) + ($threshold_minute * 60));
?>

<div class="main-content">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-lg border-0">
                    <div class="card-body">
                        <div class="alert alert-info mb-4">
                            <p class="mb-2">
                                <strong>Morning Late After:</strong> <?php echo $am_late_time; ?> 
                                (<?php echo $threshold_minute; ?> min threshold)
                            </p>
                            <p class="mb-0">
                                <strong>Afternoon Late After:</strong> <?php echo $pm_late_time; ?> 
                                (<?php echo $threshold_minute; ?> min threshold)
                            </p>
                        </div>
                        <!-- Scanner -->
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <video id="preview" style="width:100%;max-width:500px;border:3px solid #007bff;border-radius:10px;"></video>
                                <div class="mt-3">
                                    <select id="cameraSelect" class="form-select mb-3" style="max-width:400px;margin:0 auto;"></select>
                                    <div class="btn-group">
                                        <button class="btn btn-primary" id="startButton">
                                            <i class="fas fa-play me-2"></i>Start Scanner
                                        </button>
                                        <button class="btn btn-danger" id="stopButton" style="display:none;">
                                            <i class="fas fa-stop me-2"></i>Stop Scanner
                                        </button>
                                    </div>
                                    <div class="mt-2">
                                        <button class="btn btn-outline-secondary" id="toggleModeBtn" type="button">
                                            Switch to Time Out
                                        </button>
                                        <span id="scanModeLabel" class="ms-2 badge bg-primary">Morning Time In</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Result Display -->
                        <div id="scan-result" class="mt-3 p-3 text-center" style="display:none;"></div>
                        <!-- Recent Scans -->
                        <div class="mt-4">
                            <h4 class="mb-3">Recent Scans Today <small><span id="last-update-time" class="text-muted"></span></small></h4>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Time In</th>
                                            <th>Time Out</th>
                                            <th>Period</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recent-scans-list">
                                        <?php if ($recent_scans && $recent_scans->num_rows > 0): ?>
                                            <?php while ($scan = $recent_scans->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($scan['username']); ?></td>
                                                    <td><?php 
                                                        $time_in = new DateTime($scan['time_in']);
                                                        echo $time_in->format('g:i A'); 
                                                    ?></td>
                                                    <td><?php 
                                                        if ($scan['time_out']) {
                                                            $time_out = new DateTime($scan['time_out']);
                                                            echo $time_out->format('g:i A');
                                                        } else {
                                                            echo '-';
                                                        }
                                                    ?></td>
                                                    <td>
                                                        <?php if ($scan['source'] === 'morning'): ?>
                                                            <span class="badge bg-primary">Morning</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning text-dark">Afternoon</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($scan['status'] === 'present'): ?>
                                                            <span class="badge bg-success">Present</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning text-dark">Late</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No scans recorded today</td>
                                            </tr>
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
</div>

<!-- Library scripts - must appear before our custom JS -->
<script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
<script src="../../assets/js/lib/sweetalert2.all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startButton = document.getElementById('startButton');
    const stopButton = document.getElementById('stopButton');
    const cameraSelect = document.getElementById('cameraSelect');
    const scanResult = document.getElementById('scan-result');
    const preview = document.getElementById('preview');
    let scanner = null;
    let scanMode = 'in'; // 'in' or 'out'
    let sessionMode = 'am'; // 'am' or 'pm'
    const scanModeLabel = document.getElementById('scanModeLabel');
    const toggleModeBtn = document.getElementById('toggleModeBtn');
    // Get PM time in/out from PHP
    const pmTimeInSetting = '<?php echo $settings['set_pm_time_in']; ?>';
    const pmTimeOutSetting = '<?php echo $settings['set_pm_time_out']; ?>';
    // Get AM time out from PHP
    const amTimeOutSetting = "<?php echo $set_am_time_out; ?>";

    // Remove live clock functions and references

    function getCurrentTimeStr() {
        const now = new Date();
        return now.getHours().toString().padStart(2, '0') + ':' +
               now.getMinutes().toString().padStart(2, '0') + ':' +
               now.getSeconds().toString().padStart(2, '0');
    }

    // Auto-check current session and mode from server
    function checkAutomaticModeSwitch() {
        $.ajax({
            url: '../../controller/admin/scan_auto_switch.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    // Update session mode (AM or PM)
                    let previousSessionMode = sessionMode;
                    let previousScanMode = scanMode;
                    
                    // Get new session and mode info
                    sessionMode = data.session;
                    
                    // Only auto-switch if recommended by server AND
                    // either the session changed or force_switch is true
                    if (data.auto_switch && (previousSessionMode !== sessionMode || data.force_switch)) {
                        scanMode = data.recommended_mode;
                        
                        // Log that auto-switching happened (for debugging)
                        console.log('Auto-switched to:', sessionMode, scanMode, 'at', data.formatted_time);
                    }
                    
                    // Update UI to reflect current mode
                    updateScanModeUI();
                    
                    // Removed time display update code
                    
                    // If the mode changed, we could also show a notification
                    if (previousSessionMode !== sessionMode || previousScanMode !== scanMode) {
                        let notificationText = '';
                        
                        if (previousSessionMode !== sessionMode) {
                            notificationText = 'Switched to ' + 
                                (sessionMode === 'am' ? 'morning' : 'afternoon') + ' session';
                        } else if (previousScanMode !== scanMode) {
                            notificationText = 'Switched to ' + 
                                (scanMode === 'in' ? 'Time In' : 'Time Out') + ' mode';
                        }
                        
                        // Show a subtle notification
                        if (notificationText && scanResult.style.display === 'none') {
                            showScanResult('info', 'Auto-switch: ' + notificationText);
                            setTimeout(function() { scanResult.style.display = 'none'; }, 2000);
                        }
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error checking automatic mode:', error);
            }
        });
    }
    
    // Function to update UI based on mode
    function updateScanModeUI() {
        if (scanMode === 'in') {
            if (sessionMode === 'am') {
                scanModeLabel.textContent = 'Morning Time In';
                scanModeLabel.className = 'ms-2 badge bg-primary';
                toggleModeBtn.textContent = 'Switch to Time Out';
            } else {
                scanModeLabel.textContent = 'Afternoon Time In';
                scanModeLabel.className = 'ms-2 badge bg-warning';
                toggleModeBtn.textContent = 'Switch to Afternoon Out';
            }
        } else {
            if (sessionMode === 'am') {
                scanModeLabel.textContent = 'Morning Time Out';
                scanModeLabel.className = 'ms-2 badge bg-danger';
                toggleModeBtn.textContent = 'Switch to Time In';
            } else {
                scanModeLabel.textContent = 'Afternoon Time Out';
                scanModeLabel.className = 'ms-2 badge bg-danger';
                toggleModeBtn.textContent = 'Switch to Afternoon In';
            }
        }
    }
    
    // Run mode check every 2 seconds
    checkAutomaticModeSwitch(); // Initial check
    setInterval(checkAutomaticModeSwitch, 2000);

    // Toggle button action
    toggleModeBtn.addEventListener('click', function() {
        scanMode = (scanMode === 'in') ? 'out' : 'in';
        updateScanModeUI();
    });

    function showScanResult(type, message) {
        scanResult.style.display = 'block';
        if (type === 'success') {
            scanResult.className = 'alert alert-success mt-3';
            scanResult.innerHTML = '<i class="fas fa-check-circle me-2"></i>' + message;
        } else if (type === 'error') {
            scanResult.className = 'alert alert-danger mt-3';
            scanResult.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>' + message;
        } else {
            scanResult.className = 'alert alert-info mt-3';
            scanResult.innerHTML = '<i class="fas fa-info-circle me-2"></i>' + message;
        }
        if (type !== 'error') {
            setTimeout(function() { scanResult.style.display = 'none'; }, 3000);
        }
    }

    function handleScan(qrCodeData) {
        showScanResult('info', 'Processing QR Code...');
        let url;
        if (sessionMode === 'am') {
            url = scanMode === 'in'
                ? '../../controller/admin/scan_morning_in.php'
                : '../../controller/admin/scan_morning_out.php';
        } else {
            url = scanMode === 'in'
                ? '../../controller/admin/scan_afternoon_in.php'
                : '../../controller/admin/scan_afternoon_out.php';
        }
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ qrCode: qrCodeData })
        })
        .then(async response => {
            let data;
            let rawText = await response.text();
            try {
                data = JSON.parse(rawText);
            } catch (e) {
                showScanResult('error', 'Server error: Invalid response. Please contact admin.');
                console.error('Raw response:', rawText);
                return;
            }
            if (data.success) {
                // Check if status is 'late' for time-in operations
                if (scanMode === 'in' && data.status === 'late') {
                    // Use SweetAlert2 to show a more noticeable notification for late employees
                    Swal.fire({
                        title: 'Employee Late!',
                        html: `<strong>${data.employeeName}</strong> is marked as <strong>LATE</strong>.<br>
                               <span class="text-danger">Time: ${data.time}</span>`,
                        icon: 'warning',
                        confirmButtonText: 'OK',
                        timer: 5000,
                        timerProgressBar: true,
                        background: '#ffe0e0',
                        iconColor: '#d33'
                    });
                } else {
                    // Use a simpler notification for on-time or time out events
                    showScanResult('success', data.message + (data.time ? '<br><b>Time:</b> ' + data.time : ''));
                }
                // Refresh scans table immediately
                refreshRecentScans();
            } else if (data.comebackTime) {
                showScanResult('info', data.message + '<br><b>Come back at:</b> ' + data.comebackTime);
                if (data.message) console.error('Scan error:', data.message);
            } else {
                showScanResult('error', data.message);
                if (data.message) console.error('Scan error:', data.message);
            }
        })
        .catch(error => {
            showScanResult('error', 'Error processing QR code. Check console for details.');
            console.error('Fetch error:', error);
        });
    }

    function startScanner() {
        if (scanner) {
            scanner.stop();
        }
        scanner = new Instascan.Scanner({ video: preview, mirror: false });
        scanner.addListener('scan', function(content) {
            handleScan(content);
        });
        const cameraId = cameraSelect.value;
        Instascan.Camera.getCameras().then(function(cameras) {
            if (cameras.length > 0) {
                const selectedCamera = cameras.find(cam => cam.id === cameraId) || cameras[0];
                scanner.start(selectedCamera);
                startButton.style.display = 'none';
                stopButton.style.display = 'inline-block';
            } else {
                showScanResult('error', 'No cameras found on your device');
                startButton.disabled = true;
            }
        }).catch(function(e) {
            showScanResult('error', 'Error accessing camera: ' + e);
            startButton.disabled = true;
        });
    }

    function stopScanner() {
        if (scanner) {
            scanner.stop();
        }
        startButton.style.display = 'inline-block';
        stopButton.style.display = 'none';
    }

    Instascan.Camera.getCameras().then(function(cameras) {
        if (cameras.length > 0) {
            cameraSelect.innerHTML = '';
            cameras.forEach(function(camera, idx) {
                const option = document.createElement('option');
                option.value = camera.id;
                option.text = camera.name || `Camera ${idx + 1}`;
                // Set Camera 3 as default if available
                if (idx === 3) {
                    option.selected = true;
                }
                cameraSelect.appendChild(option);
            });
            // If less than 3 cameras, select the first one
            if (cameras.length < 3) {
                cameraSelect.options[0].selected = true;
            }
            startButton.disabled = false;
            startButton.addEventListener('click', startScanner);
            stopButton.addEventListener('click', stopScanner);
            // Auto-start scanner
            setTimeout(() => { startScanner(); }, 1000);
        } else {
            showScanResult('error', 'No cameras found on your device');
            startButton.disabled = true;
        }
    }).catch(function(e) {
        showScanResult('error', 'Error accessing camera: ' + e);
        startButton.disabled = true;
    });
    
    // AJAX function to refresh the recent scans table
    function refreshRecentScans() {
        $.ajax({
            url: '../../controller/admin/get_recent_scans.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    // Update the table with new data
                    var tableBody = $('#recent-scans-list');
                    tableBody.empty();
                    
                    if (data.scans.length > 0) {
                        // Add each scan to the table
                        $.each(data.scans, function(index, scan) {
                            var timeIn = formatTime(scan.time_in);
                            var timeOut = formatTime(scan.time_out);
                            
                            var periodBadge = scan.source === 'morning' ? 
                                '<span class="badge bg-primary">Morning</span>' : 
                                '<span class="badge bg-warning text-dark">Afternoon</span>';
                            var statusBadge = scan.status === 'present' ? 
                                '<span class="badge bg-success">Present</span>' : 
                                '<span class="badge bg-warning text-dark">Late</span>';
                            
                            var row = '<tr>' + 
                                '<td>' + scan.username + '</td>' +
                                '<td>' + timeIn + '</td>' +
                                '<td>' + (scan.time_out ? timeOut : '-') + '</td>' +
                                '<td>' + periodBadge + '</td>' +
                                '<td>' + statusBadge + '</td>' +
                                '</tr>';
                                
                            tableBody.append(row);
                        });
                    } else {
                        // Show "No scans" message if no data
                        tableBody.append('<tr><td colspan="5" class="text-center">No scans recorded today</td></tr>');
                    }
                    
                    // Update last refresh time
                    //$('#last-update-time').text('(Last updated: ' + new Date().toLocaleTimeString() + ')');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching recent scans:', error);
            }
        });
    }
    
    // Updated formatTime function for AJAX refresh
    function formatTime(timeString) {
        if (!timeString) return '-';
        try {
            // Support both date-time and time-only formats
            const time = timeString.includes(' ') ? timeString.split(' ')[1] : timeString;
            const [hours, minutes] = time.split(':');
            
            // Ensure hours is treated as a number
            let hour = parseInt(hours, 10);
            let ampm = hour >= 12 ? 'PM' : 'AM';
            
            // Convert to 12-hour format
            hour = hour % 12;
            hour = hour ? hour : 12; // Convert 0 to 12
            
            // Format the final time string
            return `${hour}:${minutes.padStart(2, '0')} ${ampm}`;
        } catch (e) {
            console.error('Error formatting time:', timeString, e);
            return timeString;
        }
    }
    
    // Initial call to load recent scans
    refreshRecentScans();
    
    // Set interval to refresh recent scans every 2 seconds
    setInterval(refreshRecentScans, 2000);
});
</script>
<?php require_once '../../includes/admin/footer.php'; ?>