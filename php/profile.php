<?php
require_once 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: " . LOGIN_PAGE);
    exit;
}

// Get user data
$userId = $_SESSION['user_id'];
$firstName = $_SESSION['first_name'] ?? '';
$lastName = $_SESSION['last_name'] ?? '';
$userEmail = $_SESSION['user_email'] ?? '';
$fullName = trim($firstName . ' ' . $lastName);

// Get additional user data from database if needed
try {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    // Additional user data can be extracted here
    $joinDate = isset($user['join_date']) ? date('F j, Y', strtotime($user['join_date'])) : 'Unknown';
    $bio = $user['bio'] ?? '';
    $hasProfilePic = !empty($user['profile_pic']);
    
} catch (Exception $e) {
    error_log("Error fetching user profile data: " . $e->getMessage());
    // Set default values
    $joinDate = 'Unknown';
    $bio = '';
    $hasProfilePic = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Mind Sync</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="../css/profile.css">
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

    <section class="profile-section">
        <div class="container">
            <div class="message-container"></div>
            
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php if($hasProfilePic): ?>
                        <img src="data:<?php echo htmlspecialchars($user['profile_pic_type']); ?>;base64,<?php echo base64_encode($user['profile_pic']); ?>" alt="Profile Picture">
                    <?php else: ?>
                        <i class="fas fa-user-circle"></i>
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($fullName); ?></h1>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($userEmail); ?></p>
                    <p><i class="fas fa-calendar-alt"></i> Member since: <?php echo $joinDate; ?></p>
                </div>
            </div>
            
            <div class="profile-content">
                <div class="profile-tabs">
                    <button class="tab-btn active" data-tab="profile-details">Profile Details</button>
                    <button class="tab-btn" data-tab="security">Security</button>
                    <button class="tab-btn" data-tab="notifications">Notifications</button>
                </div>
                
                <div class="tab-content">
                    <!-- Profile Details Tab -->
                    <div id="profile-details" class="tab-pane active">
                        <h2>Profile Details</h2>
                        <form id="update-profile-form" enctype="multipart/form-data">
                            <div class="form-group profile-picture-upload">
                                <label>Profile Picture</label>
                                <div class="picture-container">
                                    <div class="current-picture">
                                        <?php if($hasProfilePic): ?>
                                            <img src="data:<?php echo htmlspecialchars($user['profile_pic_type']); ?>;base64,<?php echo base64_encode($user['profile_pic']); ?>" alt="Current Profile Picture" id="profile-preview">
                                        <?php else: ?>
                                            <img src="../img/default-avatar.png" alt="Default Profile Picture" id="profile-preview">
                                        <?php endif; ?>
                                    </div>
                                    <div class="upload-controls">
                                        <label for="profile-picture" class="btn btn-outline">
                                            <i class="fas fa-camera"></i> Choose Photo
                                        </label>
                                        <input type="file" id="profile-picture" name="profile_picture" accept="image/*" class="hidden">
                                        <p class="upload-help">Maximum file size: 2MB. Supported formats: JPG, PNG, GIF.</p>
                                        <button type="button" id="remove-picture" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="first-name">First Name</label>
                                <input type="text" id="first-name" name="first_name" value="<?php echo htmlspecialchars($firstName); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last-name">Last Name</label>
                                <input type="text" id="last-name" name="last_name" value="<?php echo htmlspecialchars($lastName); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="profile-email">Email</label>
                                <input type="email" id="profile-email" name="email" value="<?php echo htmlspecialchars($userEmail); ?>" readonly class="readonly-field">
                                <small class="field-note">Email address cannot be changed for security reasons.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="profile-bio">Bio</label>
                                <textarea id="profile-bio" name="bio" rows="4" placeholder="Tell us about yourself"><?php echo htmlspecialchars($bio); ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </form>
                    </div>
                    
                    <!-- Security Tab -->
                    <div id="security" class="tab-pane">
                        <h2>Security</h2>
                        <form id="change-password-form">
                            <div class="form-group">
                                <label for="current-password">Current Password</label>
                                <input type="password" id="current-password" name="current_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new-password">New Password</label>
                                <input type="password" id="new-password" name="new_password" required>
                                <div class="password-strength">
                                    <div class="strength-meter">
                                        <div class="meter-bar"></div>
                                    </div>
                                    <span class="strength-text">Password strength: Too weak</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm-password">Confirm New Password</label>
                                <input type="password" id="confirm-password" name="confirm_password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </form>
                    </div>
                    
                    <!-- Notifications Tab -->
                    <div id="notifications" class="tab-pane">
                        <h2>Notification Preferences</h2>
                        <form id="notification-settings-form">
                            <div class="form-group checkbox-group">
                                <label class="checkbox-container">
                                    <input type="checkbox" name="email_notifications" checked>
                                    <span class="checkmark"></span>
                                    Email Notifications
                                </label>
                                <p class="checkbox-help">Receive email updates about forum activity and responses</p>
                            </div>
                            
                            <div class="form-group checkbox-group">
                                <label class="checkbox-container">
                                    <input type="checkbox" name="reply_notifications" checked>
                                    <span class="checkmark"></span>
                                    Reply Notifications
                                </label>
                                <p class="checkbox-help">Get notified when someone replies to your posts or comments</p>
                            </div>
                            
                            <div class="form-group checkbox-group">
                                <label class="checkbox-container">
                                    <input type="checkbox" name="event_notifications">
                                    <span class="checkmark"></span>
                                    Event Notifications
                                </label>
                                <p class="checkbox-help">Receive updates about upcoming events and workshops</p>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-bell"></i> Update Preferences
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bottom Dock Navigation -->
    <div class="bottom-dock">
        <nav class="bottom-dock-nav">
            <a href="dashboard.php" class="dock-item">
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
            <a href="profile.php" class="dock-item active">
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

    <script src="../js/profile.js"></script>
</body>
</html> 