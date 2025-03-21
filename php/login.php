<?php
// Include database configuration
require_once 'config.php';

// For debugging
error_log("Login.php accessed at " . date('Y-m-d H:i:s'));
error_log("POST data: " . print_r($_POST, true));

$response = [
    'success' => false,
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    error_log("Login attempt for email: $email");
    
    if (empty($email) || empty($password)) {
        $response['message'] = 'Please enter both email and password';
        error_log("Login validation failed: Empty fields");
    } else {
        try {
            // Connect to database
            $pdo = getDBConnection();
            
            if (!$pdo) {
                throw new Exception("Database connection failed");
            }
            
            error_log("Database connection successful");
            
            // Prepare query to check if user exists
            $stmt = $pdo->prepare("SELECT user_id, email, password, first_name, last_name, role FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            // Check if user exists
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password (using password_verify for hashed passwords)
                if (password_verify($password, $user['password'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['is_logged_in'] = true;
                    
                    // Set cookies for email and password (hashed)
                    $hashedCookiePassword = password_hash($password, PASSWORD_DEFAULT);
                    setcookie('user_email', $email, time() + 30 * 24 * 60 * 60, '/'); // 30 days
                    setcookie('user_password', $hashedCookiePassword, time() + 30 * 24 * 60 * 60, '/'); // 30 days
                    
                    $response['success'] = true;
                    $response['message'] = 'Login successful!';
                    $response['redirect'] = DASHBOARD_PAGE;
                    error_log("Login successful for email: $email");
                } else {
                    $response['message'] = 'Invalid password. Please try again.';
                    error_log("Login failed: Invalid password for email: $email");
                }
            } else {
                $response['message'] = 'User not found. Please create an account first.';
                $response['user_not_found'] = true;
                error_log("Login failed: User not found for email: $email");
            }
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
            error_log("Login exception: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
        }
    }
}

error_log("Login response: " . print_r($response, true));

// Always return JSON for form submissions
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>

