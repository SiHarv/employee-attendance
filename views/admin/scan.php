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
$recent_scans_query = "SELECT tl.*, u.username 
                      FROM time_log tl 
                      JOIN users u ON tl.employee_id = u.id 
                      WHERE DATE(tl.time_in) = ? 
                      ORDER BY tl.time_in DESC 
                      LIMIT 10";
$stmt = $conn->prepare($recent_scans_query);
$stmt->bind_param("s", $today);
$stmt->execute();
$recent_scans = $stmt->get_result();

// Get settings
$settings_query = "SELECT time_in, threshold_minute FROM settings WHERE id = 1";
$settings_result = $conn->query($settings_query);
$settings = $settings_result->fetch_assoc();

// Default settings if none are found
if (!$settings) {
    $settings = [
        'time_in' => '08:00:00',
        'threshold_minute' => 15
    ];
}

// Format time for display
$expected_time = date('h:i A', strtotime($settings['time_in']));
$late_time = date('h:i A', strtotime($settings['time_in']) + ($settings['threshold_minute'] * 60));
?>

<div class="main-content">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-lg border-0">
                    <div class="card-body">
                        <div class="alert alert-info mb-4">
                            <p class="mb-0">
                                <strong>Late After:</strong> <?php echo $late_time; ?> 
                                (<?php echo $settings['threshold_minute']; ?> min threshold)
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
                                </div>
                            </div>
                        </div>
                        <!-- Result Display -->
                        <div id="scan-result" class="mt-3 p-3 text-center" style="display:none;"></div>
                        <!-- Recent Scans -->
                        <div class="mt-4">
                            <h4 class="mb-3">Recent Scans Today</h4>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Time In</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recent-scans-list">
                                        <?php if ($recent_scans->num_rows > 0): ?>
                                            <?php while ($scan = $recent_scans->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($scan['username']); ?></td>
                                                    <td><?php echo date('h:i A', strtotime($scan['time_in'])); ?></td>
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
                                                <td colspan="3" class="text-center">No scans recorded today</td>
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

<!-- Instascan library -->
<script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startButton = document.getElementById('startButton');
    const stopButton = document.getElementById('stopButton');
    const cameraSelect = document.getElementById('cameraSelect');
    const scanResult = document.getElementById('scan-result');
    const preview = document.getElementById('preview');
    let scanner = null;

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
        fetch('../../controller/admin/process_scan.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ qrCode: qrCodeData })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showScanResult('success', data.message + (data.time ? '<br><b>Time In:</b> ' + data.time : ''));
                setTimeout(function() { window.location.reload(); }, 2000);
            } else {
                showScanResult('error', data.message);
            }
        })
        .catch(error => {
            showScanResult('error', 'Error processing QR code. Check console for details.');
            console.error('Error processing scan:', error);
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
                if (camera.name && camera.name.toLowerCase().includes('back')) {
                    option.selected = true;
                }
                cameraSelect.appendChild(option);
            });
            if (!cameraSelect.value) {
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
});
</script>
<?php require_once '../../includes/admin/footer.php'; ?>