<?php
require_once 'config.php';
require_once 'auth_check.php';

header('Content-Type: application/json');

// Check if user is logged in from session
if (is_logged_in()) {
    // User is logged in via session
    echo json_encode([
        'logged_in' => true,
        'user_name' => $_SESSION['user_name'] ?? $_SESSION['user_email'] ?? 'User',
        'user_id' => $_SESSION['user_id'] ?? null,
        'redirect' => DASHBOARD_PAGE
    ]);
    exit;
}

// Check if user has valid cookies
if (isset($_COOKIE['user_email']) && isset($_COOKIE['user_password'])) {
    try {
        $pdo = getDbConnection();
        
        // Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$_COOKIE['user_email']]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Set session data (login the user)
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['is_logged_in'] = true;
            
            // Return logged in status
            echo json_encode([
                'logged_in' => true,
                'user_name' => $user['first_name'] . ' ' . $user['last_name'],
                'user_id' => $user['user_id'],
                'redirect' => DASHBOARD_PAGE
            ]);
            exit;
        }
    } catch (Exception $e) {
        error_log("Error in check_auth cookie login: " . $e->getMessage());
    }
}

// User is not logged in
echo json_encode([
    'logged_in' => false
]);
?> 