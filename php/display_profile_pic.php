<?php
require_once 'config.php';
require_once 'auth_check.php';

// Ensure user is logged in
if (!is_logged_in()) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

// Get user ID from session
$userId = $_SESSION['user_id'];

try {
    // Connect to the database
    $pdo = getDbConnection();
    
    // Get profile picture data
    $stmt = $pdo->prepare("SELECT profile_pic, profile_pic_type FROM users WHERE user_id = ? AND profile_pic IS NOT NULL");
    $stmt->bindParam(1, $userId, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user['profile_pic'] !== null && $user['profile_pic_type'] !== null) {
            // Set cache control headers
            header('Cache-Control: no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Set the content type header
            header('Content-Type: ' . $user['profile_pic_type']);
            
            // Convert to base64 and output as data URL
            $base64 = base64_encode($user['profile_pic']);
            echo $base64;
            exit;
        }
    }
    
    // If we get here, either no picture or error occurred
    // Redirect to default avatar
    header('Location: ../img/default-avatar.png');
    
} catch (Exception $e) {
    error_log("Error fetching profile picture: " . $e->getMessage());
    // Redirect to default image on error
    header('Location: ../img/default-avatar.png');
}
?> 