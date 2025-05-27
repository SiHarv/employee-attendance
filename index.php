<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

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
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Employee Attendance System</title>
</head>
<body>
    <div class="container">
        <h1>Employee Attendance System</h1>
        
        <!-- User Login Form -->
        <form method="POST" action="controller/user/user_login.php">
            <h2>User Login</h2>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
            <?php if (isset($_SESSION['user_error'])): ?>
                <p class='error'><?php echo $_SESSION['user_error']; unset($_SESSION['user_error']); ?></p>
            <?php endif; ?>
        </form>

        <!-- Admin Login Form -->
        <form method="POST" action="controller/admin/admin_login.php">
            <h2>Admin Login</h2>
            <input type="text" name="username" placeholder="Admin Username" required>
            <input type="password" name="password" placeholder="Admin Password" required>
            <button type="submit">Login as Admin</button>
            <?php if (isset($_SESSION['admin_error'])): ?>
                <p class='error'><?php echo $_SESSION['admin_error']; unset($_SESSION['admin_error']); ?></p>
            <?php endif; ?>
        </form>

        <button onclick="window.location.href='views/user/attendance.php'">Scan QR</button>
    </div>
</body>
</html>