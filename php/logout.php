<?php
require_once 'config.php';

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session variables
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Clear auth cookies
setcookie("user_email", "", time() - 3600, "/");
setcookie("user_password", "", time() - 3600, "/");

// Redirect to the home page
header("Location: " . HOME_PAGE);
exit;
?> 