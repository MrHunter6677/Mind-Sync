<?php
require_once 'config.php';
require_once 'auth_check.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to like posts']);
    exit;
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);
$postId = $data['post_id'] ?? null;
$userId = $_SESSION['user_id'];

if (!$postId) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit;
}

try {
    $pdo = getDbConnection();
    
    // Check if user has already liked the post
    $stmt = $pdo->prepare("
        SELECT * FROM post_likes 
        WHERE post_id = ? AND user_id = ?
    ");
    $stmt->execute([$postId, $userId]);
    $existingLike = $stmt->fetch();

    $pdo->beginTransaction();

    if ($existingLike) {
        // Unlike the post
        $stmt = $pdo->prepare("
            DELETE FROM post_likes 
            WHERE post_id = ? AND user_id = ?
        ");
        $stmt->execute([$postId, $userId]);

        // Decrease like count
        $stmt = $pdo->prepare("
            UPDATE posts 
            SET likes = likes - 1 
            WHERE post_id = ?
        ");
        $stmt->execute([$postId]);
    } else {
        // Like the post
        $stmt = $pdo->prepare("
            INSERT INTO post_likes (post_id, user_id) 
            VALUES (?, ?)
        ");
        $stmt->execute([$postId, $userId]);

        // Increase like count
        $stmt = $pdo->prepare("
            UPDATE posts 
            SET likes = likes + 1 
            WHERE post_id = ?
        ");
        $stmt->execute([$postId]);
    }

    // Get updated like count
    $stmt = $pdo->prepare("
        SELECT likes FROM posts WHERE post_id = ?
    ");
    $stmt->execute([$postId]);
    $likes = $stmt->fetch(PDO::FETCH_COLUMN);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'likes' => $likes,
        'isLiked' => !$existingLike
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error handling like: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your request'
    ]);
}
?> 