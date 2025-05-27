<?php
require_once '../db/connect.php';

// Admin credentials
$username = 'admin';
$password = 'admin';
$email = 'admin@example.com';

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// First, check if admin already exists
$check_query = "SELECT id FROM admin WHERE username = ? OR email = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("ss", $username, $email);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    // Update existing admin
    $query = "UPDATE admin SET password = ? WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $hashed_password, $username);
} else {
    // Insert new admin
    $query = "INSERT INTO admin (username, password, email) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $username, $hashed_password, $email);
}

if ($stmt->execute()) {
    echo "Admin account created/updated successfully!";
} else {
    echo "Error: " . $stmt->error;
}