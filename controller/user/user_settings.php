<?php
session_start();
require_once '../../db/connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Validate
if (empty($username) || empty($email)) {
    $_SESSION['settings_error'] = "Username and email are required.";
    header("Location: ../../views/user/settings.php");
    exit();
}

// Check if username or email already exists for another user
$stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
$stmt->bind_param("ssi", $username, $email, $user_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $_SESSION['settings_error'] = "Username or email already taken.";
    header("Location: ../../views/user/settings.php");
    exit();
}
$stmt->close();

// Update user info
if (!empty($password)) {
    if ($password !== $confirm_password) {
        $_SESSION['settings_error'] = "Passwords do not match.";
        header("Location: ../../views/user/settings.php");
        exit();
    }
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
    $stmt->bind_param("sssi", $username, $email, $hashed, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $username, $email, $user_id);
}

if ($stmt->execute()) {
    $_SESSION['settings_success'] = "Account updated successfully.";
} else {
    $_SESSION['settings_error'] = "Error updating account.";
}
$stmt->close();

header("Location: ../../views/user/settings.php");
exit();
?>