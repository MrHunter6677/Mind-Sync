<?php
require_once 'config.php';

try {
    $pdo = getDbConnection();
    
    // SQL to create posts table
    $sql = "CREATE TABLE IF NOT EXISTS posts (
        post_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        category ENUM('Mental Health', 'Wellness', 'Support', 'Resources', 'General') DEFAULT 'General',
        likes INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        status ENUM('active', 'hidden', 'reported') DEFAULT 'active',
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    // Execute the query
    $pdo->exec($sql);
    echo "✓ Posts table created successfully!";

    // Show table structure
    echo "<h3>Posts Table Structure:</h3>";
    $stmt = $pdo->query("DESCRIBE posts");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";

} catch (PDOException $e) {
    echo "Error creating posts table: " . $e->getMessage();
}
?> 