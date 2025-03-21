<?php
require_once 'config.php';
require_once 'auth_check.php';

// Ensure user is logged in
if (!is_logged_in()) {
    die("Not logged in");
}

try {
    $pdo = getDbConnection();
    
    // Get user ID from session
    $userId = $_SESSION['user_id'];
    
    // First, let's check what we have in the database
    $stmt = $pdo->prepare("SELECT profile_pic, profile_pic_type FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h2>Debug Information:</h2>";
    echo "<pre>";
    echo "User ID: " . $userId . "\n";
    echo "Profile Picture Type: " . ($user['profile_pic_type'] ?? 'NULL') . "\n";
    echo "Profile Picture Length: " . (strlen($user['profile_pic'] ?? '') ?? 'NULL') . " bytes\n";
    echo "First 100 bytes of profile picture (base64): " . base64_encode(substr($user['profile_pic'] ?? '', 0, 100)) . "\n";
    echo "</pre>";
    
    if ($user && $user['profile_pic'] && $user['profile_pic_type']) {
        echo "<h3>Testing Image Display:</h3>";
        echo "<img src='data:" . $user['profile_pic_type'] . ";base64," . base64_encode($user['profile_pic']) . "' style='max-width: 200px;'>";
    } else {
        echo "<p>No profile picture found in database.</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<pre>";
    echo "Error Message: " . $e->getMessage() . "\n";
    echo "Stack Trace:\n" . $e->getTraceAsString();
    echo "</pre>";
}
?> 