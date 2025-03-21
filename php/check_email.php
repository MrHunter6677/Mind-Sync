<?php
// Include database configuration
require_once 'config.php';

// For debugging
error_log("check_email.php accessed at " . date('Y-m-d H:i:s'));
error_log("POST data: " . print_r($_POST, true));

$response = [
    'success' => false,
    'message' => '',
    'user_exists' => false
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    error_log("Checking email: $email");
    
    if (empty($email)) {
        $response['message'] = 'Please enter an email address';
        error_log("Validation failed: Empty email");
    } else {
        try {
            // Connect to database
            $pdo = getDBConnection();
            
            if (!$pdo) {
                throw new Exception("Database connection failed");
            }
            
            error_log("Database connection successful");
            
            // Check if user exists
            $stmt = $pdo->prepare("SELECT user_id, user_name, first_name FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $response['success'] = true;
                $response['message'] = 'User found, please set a new password';
                $response['user_exists'] = true;
                $response['user_name'] = $user['user_name'];
                $response['first_name'] = $user['first_name'];
                error_log("User found for email: $email");
            } else {
                $response['message'] = 'No account exists with this email. Please check your email or create a new account.';
                error_log("No user found for email: $email");
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