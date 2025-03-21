<?php
// Include database configuration
require_once 'config.php';

// For debugging
error_log("Register.php accessed at " . date('Y-m-d H:i:s'));
error_log("POST data: " . print_r($_POST, true));

$response = [
    'success' => false,
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get form data
    $firstname = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
    $lastname = isset($_POST['lastname']) ? trim($_POST['lastname']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Log form data
    error_log("Form data processed: firstname=$firstname, lastname=$lastname, email=$email, username=$username");
    
    // Basic validation
    if (empty($firstname) || empty($lastname) || empty($email) || empty($username) || empty($password)) {
        $response['message'] = 'Please fill out all required fields';
        error_log("Validation failed: Empty fields");
    } else if ($password !== $confirm_password) {
        $response['message'] = 'Passwords do not match';
        error_log("Validation failed: Passwords don't match");
    } else if (strlen($password) < 8) {
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
            
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $response['message'] = 'Email already exists. Please use a different email or login.';
                error_log("Registration failed: Email already exists");
            } else {
                // Check if username already exists
                $stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_name = :username");
                $stmt->bindParam(':username', $username);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $response['message'] = 'Username already taken. Please choose another username.';
                    error_log("Registration failed: Username already taken");
                } else {
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Prepare insert statement
                    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, user_name, password, join_date, status, role) 
                                          VALUES (:firstname, :lastname, :email, :username, :password, NOW(), 'active', 'user')");
                    
                    // Bind parameters
                    $stmt->bindParam(':firstname', $firstname);
                    $stmt->bindParam(':lastname', $lastname);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':username', $username);
                    $stmt->bindParam(':password', $hashed_password);
                    
                    error_log("Attempting to insert new user");
                    
                    // Execute query
                    if ($stmt->execute()) {
                        $user_id = $pdo->lastInsertId();
                        
                        error_log("User inserted successfully with ID: $user_id");
                        
                        // Set session variables
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['user_name'] = $username;
                        $_SESSION['email'] = $email;
                        $_SESSION['first_name'] = $firstname;
                        $_SESSION['last_name'] = $lastname;
                        $_SESSION['role'] = 'user';
                        $_SESSION['logged_in'] = true;
                        
                        // Set cookies for email and password (hashed)
                        $hashedCookiePassword = password_hash($password, PASSWORD_DEFAULT);
                        setcookie('user_email', $email, time() + 30 * 24 * 60 * 60, '/'); // 30 days
                        setcookie('user_password', $hashedCookiePassword, time() + 30 * 24 * 60 * 60, '/'); // 30 days
                        
                        $response['success'] = true;
                        $response['message'] = 'Registration successful! Welcome to Mind Sync.';
                        $response['redirect'] = 'dashboard.php';
                        error_log("Registration successful for email: $email");
                    } else {
                        $response['message'] = 'Registration failed. Please try again.';
                        error_log("Registration failed: Insert query failed");
                    }
                }
            }
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
            error_log("Exception: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
        }
    }
}

error_log("Response: " . print_r($response, true));

// Always return JSON for form submissions
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
