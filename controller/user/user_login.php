<?php
session_start();
include_once '../../db/connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate input
    if (empty($username) || empty($password)) {
        $_SESSION['user_error'] = "Username and password are required.";
        header("Location: ../../index.php");
        exit;
    }

    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: ../../views/user/dashboard.php");
            exit;
        } else {
            $_SESSION['user_error'] = "Invalid password.";
            header("Location: ../../index.php");
            exit;
        }
    } else {
        $_SESSION['user_error'] = "User not found.";
        header("Location: ../../index.php");
        exit;
    }
}
?>