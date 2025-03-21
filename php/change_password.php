<?php
require_once 'config.php';
require_once 'auth_check.php';

// Ensure user is logged in
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to change your password'
    ]);
    exit;
}

$response = [
    'success' => false,
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $currentPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $userId = $_SESSION['user_id'];
    
    // Validate inputs
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $response['message'] = 'All fields are required';
    } else if ($newPassword !== $confirmPassword) {
        $response['message'] = 'New passwords do not match';
    } else if (strlen($newPassword) < 8) {
        $response['message'] = 'Password must be at least 8 characters long';
    } else {
        try {
            $pdo = getDbConnection();
            
            // Get current user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($currentPassword, $user['password'])) {
                // Current password is correct, update to new password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $userId]);
                
                if ($stmt->rowCount() > 0) {
                    // Update cookie if remember me is active
                    if (isset($_COOKIE['user_password'])) {
                        setcookie('user_password', password_hash($newPassword, PASSWORD_DEFAULT), time() + COOKIE_EXPIRY, '/');
                    }
                    
                    // Return success
                    $response['success'] = true;
                    $response['message'] = 'Password changed successfully';
                } else {
                    $response['message'] = 'No changes were made to your password';
                }
            } else {
                $response['message'] = 'Current password is incorrect';
            }
        } catch (Exception $e) {
            error_log("Password change error: " . $e->getMessage());
            $response['message'] = 'An error occurred while changing your password';
        }
    }
} else {
    $response['message'] = 'Invalid request method';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 