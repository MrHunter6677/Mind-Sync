<?php
require_once 'config.php';
require_once 'auth_check.php';

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please log in to create a post.'
    ]);
    exit;
}

// Get POST data
$title = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';
$category = $_POST['category'] ?? '';

// Validate required fields
if (empty($title) || empty($content) || empty($category)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill in all required fields.'
    ]);
    exit;
}

// Validate category
$valid_categories = ['Mental Health', 'Wellness', 'Support', 'Resources', 'General'];
if (!in_array($category, $valid_categories)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid category selected.'
    ]);
    exit;
}

try {
    $pdo = getDbConnection();

    // Check if posts table exists, create if it doesn't
    $pdo->query("CREATE TABLE IF NOT EXISTS posts (
        post_id INT(11) NOT NULL AUTO_INCREMENT,
        user_id INT(11) NOT NULL,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        category ENUM('Mental Health', 'Wellness', 'Support', 'Resources', 'General') DEFAULT 'General',
        likes INT(11) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        status ENUM('active', 'hidden', 'reported') DEFAULT 'active',
        PRIMARY KEY (post_id),
        KEY user_id (user_id),
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Check if post_likes table exists, create if it doesn't
    $pdo->query("CREATE TABLE IF NOT EXISTS post_likes (
        like_id INT(11) NOT NULL AUTO_INCREMENT,
        post_id INT(11) NOT NULL,
        user_id INT(11) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (like_id),
        UNIQUE KEY post_user (post_id, user_id),
        FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Insert the post
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, content, category) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $title, $content, $category]);

    echo json_encode([
        'success' => true,
        'message' => 'Post created successfully!',
        'post_id' => $pdo->lastInsertId()
    ]);

} catch (Exception $e) {
    error_log("Error creating post: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while creating the post. Please try again.'
    ]);
}
?> 