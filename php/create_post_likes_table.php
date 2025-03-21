<?php
require_once 'config.php';

try {
    $pdo = getDbConnection();
    
    // SQL to create post_likes table
    $sql = "CREATE TABLE IF NOT EXISTS post_likes (
        like_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        UNIQUE KEY unique_like (post_id, user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    // Execute the query
    $pdo->exec($sql);
    echo "✓ Post likes table created successfully!";

} catch (PDOException $e) {
    echo "Error creating post likes table: " . $e->getMessage();
}
?> 