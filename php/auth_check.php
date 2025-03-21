<?php
require_once 'config.php';

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
}

// If user tries to access login/signup while logged in, redirect to dashboard
function redirect_if_logged_in() {
    if (is_logged_in()) {
        header("Location: " . DASHBOARD_PAGE);
        exit;
    }
}

// Function to get user data from session
function get_user_data() {
    if (is_logged_in()) {
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'name' => $_SESSION['user_name'] ?? null
        ];
    }
    return null;
}
?> 