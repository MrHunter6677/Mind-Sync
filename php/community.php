<?php
// Include database configuration
require_once 'config.php';
require_once 'auth_check.php';

// Check if the user is logged in
if (!is_logged_in()) {
    header("Location: " . LOGIN_PAGE);
    exit;
}

// Get user data
$userId = $_SESSION['user_id'];
$firstName = $_SESSION['first_name'] ?? '';
$lastName = $_SESSION['last_name'] ?? '';
$fullName = trim($firstName . ' ' . $lastName);

// Fetch posts from database
try {
    $pdo = getDbConnection();
    
    // First, check if the posts table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'posts'");
    if ($stmt->rowCount() == 0) {
        // Create posts table if it doesn't exist
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
        $pdo->exec($sql);
    }
    
    // Now fetch posts with user info and like status
    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            u.first_name,
            u.last_name,
            u.profile_pic,
            u.profile_pic_type,
            CASE WHEN pl.like_id IS NOT NULL THEN 1 ELSE 0 END as is_liked
        FROM posts p
        JOIN users u ON p.user_id = u.user_id
        LEFT JOIN post_likes pl ON p.post_id = pl.post_id AND pl.user_id = ?
        WHERE p.status = 'active'
        ORDER BY p.created_at DESC
    ");
    
    $stmt->execute([$userId]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error fetching posts: " . $e->getMessage());
    $posts = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community | Mind Sync</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/community.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #7B68EE;
            --secondary-color: #9370DB;
            --accent-color: #6A5ACD;
            --text-color: #333;
            --light-text: #666;
            --lighter-text: #999;
            --background-color: #f5f5f5;
            --white: #ffffff;
            --shadow: 0 2px 10px rgba(0,0,0,0.1);
            --hover-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }

        /* Remove the custom navbar styles to ensure header.php styles are used */
        /* 
        .navbar {
            background: var(--white);
            box-shadow: var(--shadow);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            height: 60px;
        }

        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 100%;
            padding: 0 2rem;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--primary-color);
        }

        .logo h1 {
            color: var(--primary-color);
            margin: 0;
            font-size: 1.5rem;
        }
        */

        /* Update FAB colors to match dashboard theme */
        .fab {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        /* Update button colors to match dashboard theme */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        /* Add community-specific active class to ensure correct navigation highlighting */
        .community-active {
            color: var(--primary-color) !important;
            font-weight: 600 !important;
        }

        /* Modal header and form styles to match dashboard */
        .modal-header h2 {
            color: var(--text-color);
            margin: 0;
            font-size: 1.5rem;
        }

        .close {
            color: var(--light-text);
            font-size: 28px;
            font-weight: 300;
        }

        .close:hover {
            color: var(--text-color);
        }

        .form-group label {
            color: var(--text-color);
        }
        
        /* Fix header spacing */
        body {
            padding-top: 70px; /* Match dashboard padding */
        }
        
        /* Fix community section spacing */
        .community-section {
            background-color: var(--background-color);
        }
        
        /* Fix FAB colors */
        .fab {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }
        
        /* Fix button primary colors */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }
        
        .btn-primary:hover {
            box-shadow: 0 4px 12px rgba(123, 104, 238, 0.2);
        }
        
        /* Post card styles to match dashboard theme */
        .post-card {
            border: 1px solid rgba(0,0,0,0.1);
        }

        .post-avatar {
            border-color: var(--primary-color);
        }

        /* Post action colors to match dashboard theme */
        .post-action.liked i {
            color: var(--primary-color);
        }

        .post-action.bookmarked i {
            color: var(--accent-color);
        }

        .text-danger {
            color: var(--primary-color) !important;
        }

        .text-primary {
            color: var(--accent-color) !important;
        }
        
        /* Community header styles to match dashboard theme */
        .community-header h1 {
            color: var(--text-color);
        }

        .community-header p {
            color: var(--light-text);
        }
        
        /* Post category styling to match dashboard theme */
        .post-category {
            background-color: rgba(123, 104, 238, 0.1);
            color: var(--primary-color);
        }
        
        /* Loading spinner to match dashboard theme */
        .loading-spinner {
            border-top: 4px solid var(--primary-color);
        }
        
        /* Post components to match dashboard theme */
        .post-author {
            color: var(--text-color);
        }
        
        .post-title {
            color: var(--text-color);
        }
        
        .post-content {
            color: var(--text-color);
        }
        
        /* Form elements to match dashboard theme */
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary-color);
        }
        
        /* No posts message styling to match dashboard theme */
        .no-posts i {
            color: var(--primary-color);
        }

        /* Add delete button styles */
        .post-action.delete-action {
            color: #dc3545;
        }

        .post-action.delete-action:hover {
            background-color: #fff5f5;
            color: #dc3545;
        }

        /* Confirmation Dialog */
        .confirm-dialog {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1002;
            align-items: center;
            justify-content: center;
        }

        .confirm-dialog-content {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
            text-align: center;
        }

        .confirm-dialog-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 1.5rem;
        }

        .confirm-dialog-buttons button {
            padding: 0.5rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-cancel {
            background: #e9ecef;
            color: var(--text-color);
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-cancel:hover {
            background: #dee2e6;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        /* Fix footer and dock overlap */
        footer {
            margin-bottom: 60px; /* Space for bottom dock */
            background-color: #fff;
            padding: 2rem 0;
            position: relative;
            z-index: 1;
        }

        /* Adjust FAB position to avoid dock overlap */
        .fab {
            bottom: 100px; /* Increased to avoid dock overlap */
        }

        /* Floating Action Button */
        .fab {
            position: fixed;
            bottom: 80px;
            right: 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
        }

        .fab:hover {
            transform: scale(1.1) rotate(90deg);
            box-shadow: 0 6px 16px rgba(0,0,0,0.2);
        }

        .fab i {
            font-size: 24px;
            transition: transform 0.3s ease;
        }

        /* Post Form Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1001;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 5% auto;
            padding: 2rem;
            width: 90%;
            max-width: 600px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-group select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%232c3e50' viewBox='0 0 16 16'%3E%3Cpath d='M8 11.5l-6-6h12z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 2.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
            justify-content: center;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.2);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* Post Cards */
        .posts-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .post-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .post-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        }

        .post-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .post-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            margin-right: 1rem;
            overflow: hidden;
            border: 2px solid var(--primary-color);
            padding: 2px;
        }

        .post-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .post-meta {
            flex-grow: 1;
        }

        .post-author {
            font-weight: 600;
            color: var(--text-color);
            margin: 0;
            font-size: 1.1rem;
        }

        .post-date {
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.25rem;
        }

        .post-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--text-color);
            margin: 1rem 0;
        }

        .post-content {
            line-height: 1.7;
            color: var(--text-color);
            margin-bottom: 1rem;
        }

        .post-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }

        .post-actions {
            display: flex;
            gap: 1rem;
        }

        .post-action {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
            cursor: pointer;
            transition: color 0.3s ease;
            padding: 0.5rem;
            border-radius: 6px;
        }

        .post-action:hover {
            color: var(--primary-color);
            background-color: #f8f9fa;
        }

        .post-category {
            background-color: rgba(123, 104, 238, 0.1);
            color: var(--primary-color);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Loading Animation */
        .loading {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1002;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Bottom dock styling - Exact match with dashboard */
        .bottom-dock {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--white);
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
            padding: 0;
            height: 80px;
            width: 100%;
        }

        .bottom-dock-nav {
            display: flex;
            justify-content: space-around;
            align-items: center;
            max-width: 600px;
            margin: 0 auto;
            height: 100%;
            padding: 0;
        }

        .dock-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: var(--light-text);
            transition: all 0.2s ease;
            padding: 10px;
            width: 20%;
            height: 100%;
            min-height: 80px;
        }

        .dock-item i {
            font-size: 24px;
            margin-bottom: 6px;
            transition: transform 0.2s ease;
        }

        .dock-item span {
            font-size: 13px;
            font-weight: 500;
            white-space: nowrap;
            font-family: 'Poppins', sans-serif;
        }

        .dock-item.active {
            color: var(--primary-color);
            background-color: rgba(123, 104, 238, 0.05);
        }

        .dock-item.active i {
            transform: scale(1.1);
        }

        .dock-item:hover {
            color: var(--primary-color);
            background-color: rgba(123, 104, 238, 0.05);
        }

        .dock-item:hover i {
            transform: translateY(-2px);
        }

        /* No posts message styling */
        .no-posts {
            text-align: center;
            padding: 40px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow);
        }

        .no-posts i {
            color: var(--primary-color);
            margin-bottom: 15px;
            opacity: 0.8;
        }

        .no-posts p {
            color: var(--light-text);
            font-size: 16px;
            margin: 0;
        }
        </style>
        
        <link rel="stylesheet" href="../css/header.css">
        <link rel="stylesheet" href="../css/dashboard.css">
        <link rel="stylesheet" href="../css/footer.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <div class="logo">
                    <h1><i class="fas fa-brain"></i> Mind Sync</h1>
                </div>
                <div class="auth-buttons">
                    <a href="logout.php" class="btn btn-primary"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
                <div class="mobile-menu">
                    <i class="fas fa-bars"></i>
                </div>
            </div>
        </nav>
    </header>

    <!-- Add script to ensure active state is set correctly -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set active state for community nav item
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                if (link.getAttribute('href').includes('community')) {
                    link.classList.add('active');
                } else if (link.classList.contains('active')) {
                    link.classList.remove('active');
                }
            });
        });
    </script>

    <section class="community-section">
        <div class="container">
            <div class="community-header">
                <h1><i class="fas fa-users"></i> Community Posts</h1>
                <p>Share your thoughts and connect with others</p>
            </div>
            
            <!-- Posts Container -->
            <div class="posts-container">
                <?php foreach ($posts as $post): ?>
                    <div class="post-card">
                        <div class="post-header">
                            <div class="post-avatar">
                                <?php if (!empty($post['profile_pic'])): ?>
                                    <img src="data:<?php echo htmlspecialchars($post['profile_pic_type']); ?>;base64,<?php echo base64_encode($post['profile_pic']); ?>" alt="Author">
                                <?php else: ?>
                                    <i class="fas fa-user-circle fa-2x"></i>
                                <?php endif; ?>
                            </div>
                            <div class="post-meta">
                                <p class="post-author"><?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?></p>
                                <p class="post-date"><?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?></p>
                            </div>
                        </div>
                        <h2 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                        <div class="post-content"><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
                        <div class="post-footer">
                            <span class="post-category"><?php echo htmlspecialchars($post['category']); ?></span>
                            <div class="post-actions">
                                <div class="post-action like-action <?php echo $post['is_liked'] ? 'liked' : ''; ?>" 
                                     data-post-id="<?php echo $post['post_id']; ?>">
                                    <i class="<?php echo $post['is_liked'] ? 'fas' : 'far'; ?> fa-heart <?php echo $post['is_liked'] ? 'text-danger' : ''; ?>"></i>
                                    <span class="likes-count"><?php echo $post['likes']; ?></span>
                                </div>
                                <div class="post-action bookmark-action" data-post-id="<?php echo $post['post_id']; ?>">
                                    <i class="far fa-bookmark"></i>
                                </div>
                                <?php if ($post['user_id'] == $userId): ?>
                                    <div class="post-action delete-action" data-post-id="<?php echo $post['post_id']; ?>" onclick="confirmDelete(<?php echo $post['post_id']; ?>)">
                                        <i class="fas fa-trash-alt"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($posts)): ?>
                    <div class="no-posts">
                        <i class="fas fa-comment-dots fa-3x"></i>
                        <p>No posts yet. Be the first to share!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Floating Action Button -->
    <div class="fab" onclick="openPostModal()">
        <i class="fas fa-plus"></i>
    </div>

    <!-- Post Modal -->
    <div id="postModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-pen"></i> Create New Post</h2>
                <span class="close" onclick="closePostModal()">&times;</span>
            </div>
            <form id="postForm" onsubmit="submitPost(event)">
                <div class="form-group">
                    <label for="post-title">Title</label>
                    <input type="text" id="post-title" name="title" required maxlength="255" placeholder="Enter a descriptive title">
                </div>
                
                <div class="form-group">
                    <label for="post-category">Category</label>
                    <select id="post-category" name="category" required>
                        <option value="">Select a category</option>
                        <option value="Mental Health">Mental Health</option>
                        <option value="Wellness">Wellness</option>
                        <option value="Support">Support</option>
                        <option value="Resources">Resources</option>
                        <option value="General">General</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="post-content">Content</label>
                    <textarea id="post-content" name="content" required rows="6" placeholder="Share your thoughts..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Share Post
                </button>
            </form>
        </div>
    </div>

    <!-- Loading Animation -->
    <div class="loading">
        <div class="loading-spinner"></div>
    </div>

    <!-- Confirmation Dialog -->
    <div id="deleteConfirmDialog" class="confirm-dialog">
        <div class="confirm-dialog-content">
            <h3>Delete Post</h3>
            <p>Are you sure you want to delete this post? This action cannot be undone.</p>
            <div class="confirm-dialog-buttons">
                <button class="btn-cancel" onclick="closeConfirmDialog()">Cancel</button>
                <button class="btn-delete" onclick="deletePost()">Delete</button>
            </div>
        </div>
    </div>

    <!-- Bottom Dock Navigation -->
    <div class="bottom-dock">
        <nav class="bottom-dock-nav">
            <a href="dashboard.php" class="dock-item">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="community.php" class="dock-item active">
                <i class="fas fa-users"></i>
                <span>Community</span>
            </a>
            <a href="guides.php" class="dock-item">
                <i class="fas fa-book-medical"></i>
                <span>Guides</span>
            </a>
            <a href="about-us.php" class="dock-item">
                <i class="fas fa-info-circle"></i>
                <span>About Us</span>
            </a>
            <a href="profile.php" class="dock-item">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        </nav>
    </div>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h2><i class="fas fa-brain"></i> Mind Sync</h2>
                    <p>A safe space for mental wellness discussions</p>
                </div>
                <div class="footer-links">
                    <div class="link-group">
                        <h3><i class="fas fa-sitemap"></i> Navigation</h3>
                        <a href="../index.html">Home</a>
                        <a href="../index.html#features">Features</a>
                        <a href="../index.html#topics">Forums</a>
                    </div>
                    <div class="link-group">
                        <h3><i class="fas fa-gavel"></i> Legal</h3>
                        <a href="../privacy-policy.html">Privacy Policy</a>
                        <a href="../terms-of-service.html">Terms of Service</a>
                        <a href="../community-guidelines.html">Community Guidelines</a>
                    </div>
                    <div class="link-group">
                        <h3><i class="fas fa-map-marker-alt"></i> Contact</h3>
                        <a href="tel:+919492935222">+91-9492935222</a>
                        <a href="mailto:helpdesk@rcee.ac.in">helpdesk@rcee.ac.in</a>
                        <p class="footer-address">Ramachandra College of Engineering<br>Eluru-534007, A.P., India</p>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <span class="year">2025</span> Mind Sync. All rights reserved.</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script src="../js/dashboard.js"></script>

    <script>
        function openPostModal() {
            document.getElementById('postModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closePostModal() {
            document.getElementById('postModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function showLoading() {
            document.querySelector('.loading').style.display = 'block';
        }

        function hideLoading() {
            document.querySelector('.loading').style.display = 'none';
        }

        function submitPost(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);

            showLoading();

            fetch('create_post.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    form.reset();
                    closePostModal();
                    location.reload();
                } else {
                    alert(data.message || 'Error creating post');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                alert('Error creating post');
            });
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('postModal');
            if (event.target == modal) {
                closePostModal();
            }
        }

        // Updated like and bookmark handling
        document.addEventListener('DOMContentLoaded', function() {
            // Like action
            document.querySelectorAll('.like-action').forEach(button => {
                button.addEventListener('click', function() {
                    const postId = this.dataset.postId;
                    const icon = this.querySelector('i');
                    const likesCount = this.querySelector('.likes-count');
                    const currentLikes = parseInt(likesCount.textContent);

                    fetch('handle_like.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            post_id: postId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            icon.classList.toggle('far');
                            icon.classList.toggle('fas');
                            icon.classList.toggle('text-danger');
                            this.classList.toggle('liked');
                            likesCount.textContent = data.likes;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                });
            });

            // Bookmark action
            document.querySelectorAll('.bookmark-action').forEach(button => {
                button.addEventListener('click', function() {
                    const postId = this.dataset.postId;
                    const icon = this.querySelector('i');

                    fetch('handle_bookmark.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            post_id: postId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            icon.classList.toggle('far');
                            icon.classList.toggle('fas');
                            icon.classList.toggle('text-primary');
                            this.classList.toggle('bookmarked');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                });
            });
        });

        let postToDelete = null;

        function confirmDelete(postId) {
            postToDelete = postId;
            document.getElementById('deleteConfirmDialog').style.display = 'flex';
        }

        function closeConfirmDialog() {
            document.getElementById('deleteConfirmDialog').style.display = 'none';
            postToDelete = null;
        }

        function deletePost() {
            if (!postToDelete) return;

            showLoading();
            fetch('delete_post.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    post_id: postToDelete
                })
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    const postElement = document.querySelector(`[data-post-id="${postToDelete}"]`).closest('.post-card');
                    postElement.remove();
                    closeConfirmDialog();
                } else {
                    alert(data.message || 'Error deleting post');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                alert('Error deleting post');
            });
        }

        // Close dialog when clicking outside
        document.getElementById('deleteConfirmDialog').addEventListener('click', function(event) {
            if (event.target === this) {
                closeConfirmDialog();
            }
        });
    </script>
</body>
</html> 