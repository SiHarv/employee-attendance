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

<div class="main-content">
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0">Attendance Settings</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($success_message)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo $success_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $error_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" class="needs-validation" novalidate>
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

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Save Settings</button>
                            </div>
                        </form>
                    </div>
                </div>
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
</script>

<?php require_once '../../includes/admin/footer.php'; ?>