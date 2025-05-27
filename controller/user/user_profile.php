<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../db/connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$query = "SELECT id, username, email, code, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}

// Generate QR code URL (Google Chart API)
$qr_data = $user['code'] ? $user['code'] : $user['username'];
$qr_url = "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" . urlencode($qr_data) . "&chld=L|1";

return [
    'user' => $user,
    'qr_url' => $qr_url,
    'qr_data' => $qr_data
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/user.css">
    <title>User Profile</title>
</head>
<body>
    <?php require_once '../../includes/user/header.php'; ?>
    <?php require_once '../../includes/user/sidebar.php'; ?>

    <div class="content">
        <h1>User Profile</h1>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($user['status']); ?></p>
        <a href="edit_profile.php">Edit Profile</a>

        <div class="qr-code">
            <h2>Your QR Code:</h2>
            <img src="<?php echo $qr_url; ?>" alt="QR Code">
        </div>
    </div>

    <script src="../../assets/js/user/profile.js"></script>
</body>
</html>