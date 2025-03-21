<?php
require_once 'config.php';
require_once 'auth_check.php';

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please log in to like posts.'
    ]);
    exit;
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($data['post_id']) || !isset($data['action'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields.'
    ]);
    exit;
}

$post_id = $data['post_id'];
$action = $data['action'];
$user_id = $_SESSION['user_id'];

try {
    $pdo = getDbConnection();

    // Start transaction
    $pdo->beginTransaction();

    if ($action === 'like') {
        // Try to insert like
        $stmt = $pdo->prepare("INSERT IGNORE INTO post_likes (post_id, user_id) VALUES (?, ?)");
        $stmt->execute([$post_id, $user_id]);

        // If a row was inserted (like was added)
        if ($stmt->rowCount() > 0) {
            // Increment likes count
            $stmt = $pdo->prepare("UPDATE posts SET likes = likes + 1 WHERE post_id = ?");
            $stmt->execute([$post_id]);
        }
    } else if ($action === 'unlike') {
        // Try to delete like
        $stmt = $pdo->prepare("DELETE FROM post_likes WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$post_id, $user_id]);

        // If a row was deleted (like was removed)
        if ($stmt->rowCount() > 0) {
            // Decrement likes count
            $stmt = $pdo->prepare("UPDATE posts SET likes = GREATEST(likes - 1, 0) WHERE post_id = ?");
            $stmt->execute([$post_id]);
        }
    }

    // Get updated likes count
    $stmt = $pdo->prepare("SELECT likes FROM posts WHERE post_id = ?");
    $stmt->execute([$post_id]);
    $likes = $stmt->fetchColumn();

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => $action === 'like' ? 'Post liked successfully!' : 'Post unliked successfully!',
        'likes' => $likes
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Error handling post like: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your request. Please try again.'
    ]);
} 