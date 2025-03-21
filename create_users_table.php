<?php
// Database connection parameters
$host = 'localhost';
$dbname = 'mind_sync';
$username = 'root';
$password = '';

try {
    // Connect to MySQL
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // SQL to create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        user_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        user_name VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
        role ENUM('user', 'moderator', 'admin') DEFAULT 'user',
        profile_pic_type VARCHAR(50) NULL,
        profile_pic MEDIUMBLOB NULL,
        bio TEXT NULL,
        join_date DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    // Execute the query
    $pdo->exec($sql);
    echo "<p style='color:green'>✓ Users table created successfully!</p>";

    // Show table structure
    echo "<h3>Users Table Structure:</h3>";
    $stmt = $pdo->query("DESCRIBE users");
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
    echo "<p style='color:red'>Database Error: " . $e->getMessage() . "</p>";
}
?> 