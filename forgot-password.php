<?php
require_once 'php/auth_check.php';

// Redirect if already logged in
redirect_if_logged_in();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Mind Sync</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header will be loaded dynamically using JavaScript -->
    <div id="header-placeholder"></div>

    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <!-- Step 1: Email verification -->
                <div id="step-1" class="step active">
                    <div class="form-header">
                        <h2><i class="fas fa-key"></i> Forgot Password</h2>
                        <p>Enter your email to reset your password</p>
                    </div>
                    
                    <div class="error-container"></div>
                    
                    <form id="forgot-password-form" class="auth-form">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" placeholder="Enter your email" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i> Find Account
                        </button>
                    </form>
                </div>
                
                <!-- Step 2: Reset Password -->
                <div id="step-2" class="step">
                    <div class="form-header">
                        <h2><i class="fas fa-lock-open"></i> Reset Password</h2>
                        <p>Create a new password for your account</p>
                    </div>
                    
                    <div class="error-container"></div>
                    
                    <form id="reset-password-form" class="auth-form">
                        <input type="hidden" id="reset-email" name="email">
                        
                        <div class="form-group">
                            <label for="new-password">New Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="new-password" name="new_password" placeholder="Create a new password" required>
                                <button type="button" class="password-toggle">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength">
                                <div class="strength-meter">
                                    <div class="meter-bar"></div>
                                </div>
                                <span class="strength-text">Password strength: Too weak</span>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm-new-password">Confirm New Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="confirm-new-password" name="confirm_new_password" placeholder="Confirm your new password" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Reset Password
                        </button>
                    </form>
                </div>
                
                <!-- Step 3: Success -->
                <div id="step-3" class="step">
                    <div class="form-header">
                        <div class="success-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h2>Password Reset Complete</h2>
                        <p>Your password has been successfully reset</p>
                    </div>
                    
                    <div class="button-group">
                        <a href="login.php" class="btn btn-primary btn-block">
                            <i class="fas fa-sign-in-alt"></i> Sign In Now
                        </a>
                    </div>
                </div>
                
                <div class="auth-footer">
                    <p>Remember your password? <a href="login.php">Sign In</a></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer will be loaded dynamically using JavaScript -->
    <div id="footer-placeholder"></div>

    <!-- Load header and footer templates, then load page scripts -->
    <script src="js/header.js"></script>
    <script src="js/footer.js"></script>
    <script src="js/forgot-password.js"></script>
</body>
</html> 