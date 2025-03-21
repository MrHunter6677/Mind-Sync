<?php
// Include database configuration
require_once 'config.php';

// Check if the user is logged in via session
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    // User is not logged in via session, check for cookies
    if (isset($_COOKIE['user_email']) && isset($_COOKIE['user_password'])) {
        try {
            $pdo = getDbConnection();
            
            // Check if user exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$_COOKIE['user_email']]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Set session data
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['is_logged_in'] = true;
            } else {
                // No user found, redirect to login
                header("Location: " . LOGIN_PAGE);
                exit;
            }
        } catch (Exception $e) {
            // Database error, redirect to login
            error_log("Error in dashboard cookie login: " . $e->getMessage());
            header("Location: " . LOGIN_PAGE);
            exit;
        }
    } else {
        // No cookies found, redirect to login
        header("Location: " . LOGIN_PAGE);
        exit;
    }
}

// User is logged in, display dashboard
$userName = $_SESSION['user_name'] ?? $_SESSION['user_email'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Mind Sync</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="../css/dashboard.css">
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

        .dashboard {
            padding: 120px 0 70px;
            min-height: calc(100vh - 350px);
        }
        
        .dashboard-header {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .welcome-message {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .welcome-message i {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-right: 20px;
        }
        
        .welcome-message h1 {
            font-size: 1.8rem;
            margin: 0;
        }
        
        .dashboard-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .dashboard-card {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .dashboard-card h2 {
            font-size: 1.5rem;
            margin-top: 0;
            margin-bottom: 20px;
            text-align: left;
        }
        
        .logout-btn {
            margin-top: 20px;
        }

        /* Bottom dock styling - Exact match with community */
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

        /* Ensure content doesn't hide behind dock */
        body {
            padding-bottom: 80px;
            position: relative;
        }
    </style>
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

    <section class="dashboard">
        <div class="container">
            <div class="dashboard-header">
                <div class="welcome-message">
                    <i class="fas fa-user-circle"></i>
                    <div>
                        <h1>Welcome, <?php echo htmlspecialchars($userName); ?>!</h1>
                        <p>This is your personal dashboard where you can manage your account and activities.</p>
                    </div>
                </div>
                
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="message-alert success">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo $_SESSION['message']; ?></span>
                        <button class="close-btn"><i class="fas fa-times"></i></button>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>
            </div>
            
            <div class="dashboard-content">
                <div class="dashboard-card">
                    <div class="card-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3>My Discussions</h3>
                    <p>View and manage your forum discussions</p>
                    <a href="#" class="btn btn-outline">View Discussions</a>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-icon">
                        <i class="fas fa-bookmark"></i>
                    </div>
                    <h3>Saved Posts</h3>
                    <p>Access your bookmarked posts and resources</p>
                    <a href="#" class="btn btn-outline">View Bookmarks</a>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3>Notifications</h3>
                    <p>Check your recent notifications</p>
                    <a href="#" class="btn btn-outline">View Notifications</a>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <h3>My Network</h3>
                    <p>Manage your connections and followers</p>
                    <a href="#" class="btn btn-outline">View Network</a>
                </div>
            </div>
            
            <div class="logout-btn" style="text-align: center; margin-top: 30px;">
                <a href="logout.php" class="btn btn-outline"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </section>

    <section class="recent-activity">
        <div class="container">
            <h2>Recent Activity</h2>
            <div class="activity-list">
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-comment"></i>
                    </div>
                    <div class="activity-content">
                        <h4>You posted in "Coping with Anxiety"</h4>
                        <p>3 days ago</p>
                    </div>
                    <a href="#" class="activity-link">View</a>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="activity-content">
                        <h4>You liked a post in "Meditation Techniques"</h4>
                        <p>1 week ago</p>
                    </div>
                    <a href="#" class="activity-link">View</a>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="activity-content">
                        <h4>You joined the community</h4>
                        <p>2 weeks ago</p>
                    </div>
                    <a href="#" class="activity-link">View</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Bottom Dock Navigation -->
    <div class="bottom-dock">
        <nav class="bottom-dock-nav">
            <a href="dashboard.php" class="dock-item active">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="community.php" class="dock-item">
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

    <script src="../js/header.js"></script>
    <script src="../js/dashboard.js"></script>
</body>
</html> 