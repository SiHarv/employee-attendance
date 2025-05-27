<?php
// Start the session if not already started
session_start();

// Clear all session variables
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();

// Add debug logging to confirm session has been destroyed
$log_message = "Session destroyed at " . date('Y-m-d H:i:s');
error_log($log_message);

// Redirect to the login page
header("Location: ../../views/admin/login.php?logout=success");
exit();
