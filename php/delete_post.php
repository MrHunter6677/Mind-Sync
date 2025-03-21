<?php
require_once 'config.php';
require_once 'auth_check.php';

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please log in to delete posts.'
    ]);
    exit;
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($data['post_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Post ID is required.'
    ]);
    exit;
}

$post_id = $data['post_id'];
$user_id = $_SESSION['user_id'];

try {
    $pdo = getDbConnection();

    // First check if the post exists and belongs to the user
    $stmt = $pdo->prepare("SELECT user_id FROM posts WHERE post_id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();

    if (!$post) {
        echo json_encode([
            'success' => false,
            'message' => 'Post not found.'
        ]);
        exit;
    }

    if ($post['user_id'] !== $user_id) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'You do not have permission to delete this post.'
        ]);
        exit;
    }

    // Start transaction
    $pdo->beginTransaction();

    // Delete likes first (due to foreign key constraint)
    $stmt = $pdo->prepare("DELETE FROM post_likes WHERE post_id = ?");
    $stmt->execute([$post_id]);

    // Delete the post
    $stmt = $pdo->prepare("DELETE FROM posts WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$post_id, $user_id]);

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Post deleted successfully!'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Error deleting post: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while deleting the post. Please try again.'
    ]);
}
?> 