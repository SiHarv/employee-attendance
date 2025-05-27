<?php
require_once '../../includes/user/header.php';
require_once '../../includes/user/sidebar.php';

// Fetch user profile data from the database
session_start();
$user_id = $_SESSION['user_id']; // Assuming user ID is stored in session
include '../../db/connect.php';

$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

?>

<div class="profile-container">
    <h2>User Profile</h2>
    <div class="profile-info">
        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($user['status']); ?></p>
        <p><strong>Attendance:</strong> <?php echo htmlspecialchars($user['attendance_status']); ?></p>
    </div>
    <a href="edit_profile.php" class="btn">Edit Profile</a>
</div>

<?php
require_once '../../includes/user/footer.php';
?>