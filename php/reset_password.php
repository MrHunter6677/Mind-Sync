<?php
// Include database configuration
require_once 'config.php';

// For debugging
error_log("reset_password.php accessed at " . date('Y-m-d H:i:s'));
error_log("POST data: " . print_r($_POST, true));

$response = [
    'success' => false,
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    error_log("Password reset attempt for email: $email");
    
    // Basic validation
    if (empty($email) || empty($new_password) || empty($confirm_password)) {
        $response['message'] = 'Please enter all required fields';
        error_log("Validation failed: Empty fields");
    } else if ($new_password !== $confirm_password) {
        $response['message'] = 'Passwords do not match';
        error_log("Validation failed: Passwords don't match");
    } else if (strlen($new_password) < 8) {
        $response['message'] = 'Password must be at least 8 characters';
        error_log("Validation failed: Password too short");
    } else {
        try {
            // Connect to database
            $pdo = getDBConnection();
            
            if (!$pdo) {
                throw new Exception("Database connection failed");
            }
            
            error_log("Database connection successful");
            
            // Check if user exists
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Hash new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update password
                $update_stmt = $pdo->prepare("UPDATE users SET password = :password WHERE email = :email");
                $update_stmt->bindParam(':password', $hashed_password);
                $update_stmt->bindParam(':email', $email);
                
                if ($update_stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Password reset successful. You can now login with your new password.';
                    $response['redirect'] = 'login.html';
                    error_log("Password reset successful for email: $email");
                } else {
                    $response['message'] = 'Failed to reset password. Please try again.';
                    error_log("Password reset failed for email: $email");
                }
            } else {
                $response['message'] = 'User not found. Please check your email.';
                error_log("Password reset failed: User not found for email: $email");
            }
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
            error_log("Exception: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
        }
    }
}

error_log("Response: " . print_r($response, true));

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?> 