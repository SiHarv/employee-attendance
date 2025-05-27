<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}
require_once '../../db/connect.php';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <script src="../../assets/js/lib/sweetalert2.all.min.js"></script>
    <style>
        body { margin: 0 !important; }
        .main-content-user {
            margin-top: 0 !important;
            padding-top: 0.5rem;
        }
        @media (max-width: 768px) {
            .main-content-user {
                padding-top: 0.3rem;
            }
        }
    </style>
</head>
<body class="bg-light">
    <?php require_once '../../includes/user/header.php'; ?>
    <div class="container-fluid" style="min-height:100vh;">
        <div class="row">
            <div class="col-md-2 p-0">
                <?php require_once '../../includes/user/sidebar.php'; ?>
            </div>
            <div class="col-md-10 main-content-user">
                <div class="card shadow-sm rounded-4 mb-4 mt-3">
                    <div class="card-body">
                        <h2 class="h4 mb-3 text-center">Account Settings</h2>
                        <form method="POST" action="../../controller/user/user_settings.php" class="mx-auto" style="max-width:400px;" id="userSettingsForm">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Username</label>
                                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">New Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password">
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Update Account</button>
                            </div>
                        </form>
                        <div class="mt-4 text-center">
                            <a href="../../controller/user/user_logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../../assets/js/lib/bootstrap.bundle.min.js"></script>
    <script>
    // SweetAlert2 notification for PHP session messages
    <?php if (isset($_SESSION['settings_success'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '<?php echo addslashes($_SESSION['settings_success']); ?>',
            timer: 2000,
            showConfirmButton: false
        });
        <?php unset($_SESSION['settings_success']); ?>
    <?php elseif (isset($_SESSION['settings_error'])): ?>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?php echo addslashes($_SESSION['settings_error']); ?>',
            timer: 3000,
            showConfirmButton: false
        });
        <?php unset($_SESSION['settings_error']); ?>
    <?php endif; ?>
    </script>
</body>
</html>