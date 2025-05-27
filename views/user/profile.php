<?php
session_start();
require_once '../../includes/user/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <style>
        .profile-container {
            max-width: 420px;
            margin: 2rem auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.08);
            padding: 2rem 1.5rem 1.5rem 1.5rem;
        }
        .qr-section {
            text-align: center;
            margin: 1.5rem 0 0.5rem 0;
        }
        .qr-section img {
            border: 6px solid #f8f9fa;
            border-radius: 12px;
            background: #fff;
        }
        .qr-section .btn {
            margin-top: 1rem;
        }
    </style>
</head>
<body class="bg-light">
<div class="container-fluid" style="min-height:100vh;">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once '../../includes/user/sidebar.php'; ?>
        </div>
        <div class="col-md-10 py-4">
            <?php
            $data = include '../../controller/user/user_profile.php';
            $user = $data['user'];
            $qr_data = $data['qr_data'];
            $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qr_data);
            ?>
            <div class="profile-container">
                <h2 class="h4 mb-3 text-center">User Profile</h2>
                <div class="mb-3">
                    <strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?>
                </div>
                <div class="mb-3">
                    <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?>
                </div>
                <div class="mb-3">
                    <strong>Account Created:</strong> <?php echo htmlspecialchars($user['created_at']); ?>
                </div>
                <div class="qr-section">
                    <img id="qrImage" src="<?php echo $qr_url; ?>" alt="QR Code" width="200" height="200">
                    <br>
                    <a id="downloadQrBtn" href="<?php echo $qr_url; ?>" download="qr_code_<?php echo $user['username']; ?>.png" class="btn btn-success btn-sm mt-2">
                        <i class="bi bi-download"></i> Download QR Code
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../../assets/js/lib/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</body>
</html>