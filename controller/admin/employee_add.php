<?php
require_once '../../db/connect.php';
session_start();

// Set proper headers for JSON response
header('Content-Type: application/json');

// Check if admin is logged in - MODIFIED: Check for any admin ID in session
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['id'])) {
    // Try to get any possible admin session variable
    $adminSessionVars = ['admin_id', 'id', 'admin_logged_in', 'user_id'];
    $authenticated = false;
    
    foreach ($adminSessionVars as $var) {
        if (isset($_SESSION[$var])) {
            $authenticated = true;
            break;
        }
    }
    
    if (!$authenticated) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Not authenticated. Please log in again.',
            'debug' => [
                'session' => $_SESSION,
                'request_method' => $_SERVER['REQUEST_METHOD']
            ]
        ]);
        exit;
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $code = trim($_POST['code'] ?? '');
    
    try {
        // Validate required fields
        if (empty($username) || empty($email) || empty($password)) {
            throw new Exception('All fields are required');
        }
        
        // Generate unique code if none provided
        if (empty($code)) {
            // Generate a unique employee code (EMP + random alphanumeric)
            do {
                $code = 'EMP' . strtoupper(bin2hex(random_bytes(4)));
                $check = $conn->query("SELECT id FROM users WHERE code = '$code'");
            } while ($check && $check->num_rows > 0);
        }
        
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Check if email or username already exists
        $check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($check_query);
        
        if (!$stmt) {
            throw new Exception("Database prepare error: " . $conn->error);
        }
        
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception('Username or email already exists');
        }
        
        // Insert new user with QR code
        $insert_query = "INSERT INTO users (username, password, email, code, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($insert_query);
        
        if (!$stmt) {
            throw new Exception("Database prepare error: " . $conn->error);
        }
        
        $stmt->bind_param("ssss", $username, $hashed_password, $email, $code);
        
        if (!$stmt->execute()) {
            throw new Exception("Database error: " . $stmt->error);
        }
        
        $user_id = $stmt->insert_id;
        $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($code);
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Employee added successfully',
            'user_id' => $user_id,
            'code' => $code,
            'qr_url' => $qr_url
        ]);
        exit;
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error', 
            'message' => $e->getMessage(),
            'debug' => [
                'request_method' => $_SERVER['REQUEST_METHOD'],
                'post_data' => $_POST,
                'session' => isset($_SESSION['admin_id']) ? 'admin_id exists' : 'no admin_id',
                'exception' => [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ]
        ]);
        exit;
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method',
        'debug' => [
            'request_method' => $_SERVER['REQUEST_METHOD']
        ]
    ]);
    exit;
}
