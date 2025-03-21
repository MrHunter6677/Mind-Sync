<?php
// Load configuration from JSON file
$config_path = __DIR__ . '/../config.json';
$config = null;

if (file_exists($config_path)) {
    $config_json = file_get_contents($config_path);
    $config = json_decode($config_json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Error parsing configuration file: " . json_last_error_msg());
    }
} else {
    die("Configuration file not found!");
}

// Database configuration constants
define('DB_HOST', $config['database']['host']);
define('DB_USER', $config['database']['username']);
define('DB_PASS', $config['database']['password']);
define('DB_NAME', $config['database']['dbname']);

// Application URLs
define('BASE_URL', $config['app']['base_url']);
define('HOME_PAGE', $config['app']['home_page']);
define('LOGIN_PAGE', $config['app']['login_page']);
define('REGISTER_PAGE', $config['app']['register_page']);
define('DASHBOARD_PAGE', $config['app']['dashboard_page']);

// Security settings
define('COOKIE_EXPIRY', $config['security']['cookie_expiry']);
define('SESSION_LIFETIME', $config['security']['session_lifetime']);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to get database connection
function getDbConnection() {
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Helper function to display messages
function display_message($message, $type = 'success') {
    return "<div class='alert alert-$type'>$message</div>";
}
?> 