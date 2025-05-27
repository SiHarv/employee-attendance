<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/db/connect.php';

// Check database connection
if (!$conn instanceof mysqli) {
    die("Database connection not established. Please check your configuration.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Attendance System</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <script src="assets/js/lib/sweetalert2.all.min.js"></script>
</head>
<body class="bg-light">
    <div class="container min-vh-100 d-flex flex-column justify-content-center align-items-center">
        <div class="w-100" style="max-width: 400px;">
            <div class="card shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h1 class="h4 text-center mb-4">Employee Attendance System</h1>
                    <form method="POST" action="controller/user/user_login.php" autocomplete="off">
                        <h2 class="h6 text-center mb-3 text-secondary">User Login</h2>
                        <div class="mb-3">
                            <input type="text" name="username" class="form-control form-control-lg" placeholder="Username" required autocomplete="username">
                        </div>
                        <div class="mb-3">
                            <input type="password" name="password" class="form-control form-control-lg" placeholder="Password" required autocomplete="current-password">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-2">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/lib/bootstrap.bundle.min.js"></script>
    <script>
    <?php if (isset($_SESSION['user_error'])): ?>
        Swal.fire({
            icon: 'error',
            title: 'Login Failed',
            text: '<?php echo addslashes($_SESSION['user_error']); ?>',
            timer: 2500,
            showConfirmButton: false
        });
        <?php unset($_SESSION['user_error']); ?>
    <?php endif; ?>
    </script>
</body>
</html>