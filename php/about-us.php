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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Mind Sync</title>
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
        
        /* Fix header spacing */
        body {
            padding-top: 70px; /* Match dashboard padding */
            padding-bottom: 80px; /* Space for bottom dock */
        }
        
        /* About Us Section Styles */
        .about-section {
            background-color: var(--background-color);
            padding: 2rem 0;
        }
        
        .about-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .about-header h1 {
            color: var(--text-color);
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
        }
        
        .about-header p {
            color: var(--light-text);
            font-size: 1.1rem;
        }
        
        .about-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .about-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .about-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }
        
        .about-card h2 {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        
        .about-card h2 i {
            margin-right: 0.75rem;
        }
        
        .about-card p {
            color: var(--text-color);
            line-height: 1.7;
            margin-bottom: 1rem;
        }
        
        /* Mission & Vision */
        .mission-vision {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .mission, .vision {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }
        
        .mission h3, .vision h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        
        .mission h3 i, .vision h3 i {
            margin-right: 0.75rem;
        }
        
        /* Team Section */
        .team-section {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .team-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .team-member {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }
        
        .team-member:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }
        
        .member-image {
            height: 220px;
            overflow: hidden;
        }
        
        .member-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .team-member:hover .member-image img {
            transform: scale(1.05);
        }
        
        .member-info {
            padding: 1.2rem;
        }
        
        .member-name {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.3rem;
        }
        
        .member-role {
            color: var(--primary-color);
            font-size: 0.9rem;
            margin-bottom: 0.8rem;
        }
        
        .member-bio {
            color: var(--light-text);
            font-size: 0.85rem;
            line-height: 1.5;
        }
        
        .social-links {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
            gap: 0.75rem;
        }
        
        .social-link {
            color: var(--light-text);
            font-size: 1.1rem;
            transition: color 0.3s ease, transform 0.3s ease;
        }
        
        .social-link:hover {
            color: var(--primary-color);
            transform: translateY(-3px);
        }
        
        /* Contact Section */
        .contact-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }
        
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .contact-item {
            display: flex;
            align-items: flex-start;
        }
        
        .contact-icon {
            color: var(--primary-color);
            font-size: 1.25rem;
            margin-right: 0.75rem;
            margin-top: 0.25rem;
        }
        
        .contact-info h3 {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }
        
        .contact-info p, .contact-info a {
            color: var(--light-text);
            line-height: 1.6;
        }
        
        .contact-info a:hover {
            color: var(--primary-color);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .mission-vision {
                grid-template-columns: 1fr;
            }
            
            .team-container {
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            }
            
            .member-image {
                height: 180px;
            }
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
    </style>
</head>
<body>
    <?php include __DIR__ . '/../components/header.php'; ?>

    <!-- Add script to ensure active state is set correctly -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set active state for about-us nav item
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                if (link.getAttribute('href').includes('about-us')) {
                    link.classList.add('active');
                } else if (link.classList.contains('active')) {
                    link.classList.remove('active');
                }
            });
        });
    </script>

    <section class="about-section">
        <div class="about-container">
            <div class="about-header">
                <h1><i class="fas fa-brain"></i> About Mind Sync</h1>
                <p>Supporting mental wellness together</p>
            </div>
            
            <!-- About Us Content -->
            <div class="about-card">
                <h2><i class="fas fa-star"></i>Our Story</h2>
                <p>Mind Sync was created by BTech AIML students at Ramachandra College of Engineering with a simple but powerful vision: to make mental health support accessible to everyone. Developed in 2023, our platform connects individuals on their mental wellness journeys, providing resources, community support, and educational materials.</p>
                <p>Our team of dedicated students came together with a shared belief that mental health care should be destigmatized, accessible, and available to all. Mind Sync is the result of our collective effort and passion for using technology to help others in their mental wellness journey.</p>
            </div>
            
            <!-- Mission & Vision -->
            <div class="mission-vision">
                <div class="mission">
                    <h3><i class="fas fa-bullseye"></i>Our Mission</h3>
                    <p>To create a supportive digital community that empowers individuals to take charge of their mental health through connection, education, and accessible resources.</p>
                </div>
                <div class="vision">
                    <h3><i class="fas fa-eye"></i>Our Vision</h3>
                    <p>A world where mental wellness is prioritized, understood, and supported, enabling everyone to thrive regardless of their challenges.</p>
                </div>
            </div>
            
            <!-- Team Section -->
            <div class="team-section">
                <h2>Meet Our Team</h2>
                <p>The dedicated students behind Mind Sync</p>
                
                <div class="team-container">
                    <!-- Team Member 1 (Team Leader) -->
                    <div class="team-member">
                        <div class="member-image">
                            <img src="../img/team-member-5.jpg" alt="Andukuri Jaya Nagasai Bharath">
                        </div>
                        <div class="member-info">
                            <h3 class="member-name">Andukuri Jaya Nagasai Bharath</h3>
                            <p class="member-role">Team Leader & Backend Developer</p>
                            <p class="member-bio">BTech 2nd year AIML student at Ramachandra College of Engineering, leading the team with expertise in backend development and UI/UX design.</p>
                            <div class="social-links">
                                <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                                <a href="#" class="social-link"><i class="fab fa-github"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Team Member 2 (UI/UX) -->
                    <div class="team-member">
                        <div class="member-image">
                            <img src="../img/team-member-4.jpg" alt="Chintha Dinesh Babu">
                        </div>
                        <div class="member-info">
                            <h3 class="member-name">Chintha Dinesh Babu</h3>
                            <p class="member-role">UI/UX Designer</p>
                            <p class="member-bio">BTech 2nd year AIML student at Ramachandra College of Engineering, specializing in creating intuitive and accessible user interfaces.</p>
                            <div class="social-links">
                                <a href="#" class="social-link"><i class="fab fa-github"></i></a>
                                <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Team Member 3 (Content Specialist) -->
                    <div class="team-member">
                        <div class="member-image">
                            <img src="../img/team-member-1.jpg" alt="Kancharla Karthik Raj">
                        </div>
                        <div class="member-info">
                            <h3 class="member-name">Kancharla Karthik Raj</h3>
                            <p class="member-role">Content Specialist</p>
                            <p class="member-bio">BTech 2nd year AIML student at Ramachandra College of Engineering, focusing on creating meaningful mental health resources and educational content.</p>
                            <div class="social-links">
                                <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                                <a href="#" class="social-link"><i class="fab fa-github"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Team Member 4 (Content Specialist) -->
                    <div class="team-member">
                        <div class="member-image">
                            <img src="../img/team-member-2.jpg" alt="Garapati Vasanth Kumar">
                        </div>
                        <div class="member-info">
                            <h3 class="member-name">Garapati Vasanth Kumar</h3>
                            <p class="member-role">Content Specialist</p>
                            <p class="member-bio">BTech 2nd year AIML student at Ramachandra College of Engineering, creating engaging educational resources for mental wellness.</p>
                            <div class="social-links">
                                <a href="#" class="social-link"><i class="fab fa-github"></i></a>
                                <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Team Member 5 (AI Researcher) -->
                    <div class="team-member">
                        <div class="member-image">
                            <img src="../img/team-member-3.jpg" alt="Parvatham Vamsi Sai">
                        </div>
                        <div class="member-info">
                            <h3 class="member-name">Parvatham Vamsi Sai</h3>
                            <p class="member-role">AI Researcher</p>
                            <p class="member-bio">BTech 2nd year AIML student at Ramachandra College of Engineering, developing machine learning models for mental health applications.</p>
                            <div class="social-links">
                                <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                                <a href="#" class="social-link"><i class="fab fa-github"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Team Member 6 (AI Researcher) -->
                    <div class="team-member">
                        <div class="member-image">
                            <img src="../img/team-member-6.jpg" alt="Makutam Bhanodhay">
                        </div>
                        <div class="member-info">
                            <h3 class="member-name">Makutam Bhanodhay</h3>
                            <p class="member-role">AI Researcher</p>
                            <p class="member-bio">BTech 2nd year AIML student at Ramachandra College of Engineering, focusing on data analysis and AI implementations for mental health solutions.</p>
                            <div class="social-links">
                                <a href="#" class="social-link"><i class="fab fa-github"></i></a>
                                <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Section -->
            <div class="contact-section">
                <h2>Get in Touch</h2>
                <p>Have questions or want to learn more about Mind Sync? We'd love to hear from you.</p>
                
                <div class="contact-grid">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-info">
                            <h3>Email Us</h3>
                            <p><a href="mailto:helpdesk@rcee.ac.in">helpdesk@rcee.ac.in</a></p>
                            <p><a href="mailto:rce_elr@yahoo.com">rce_elr@yahoo.com</a></p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="contact-info">
                            <h3>Call Us</h3>
                            <p><a href="tel:+919492935222">+91-9492935222</a> (9AM-5PM)</p>
                            <p><a href="tel:+919492936222">+91-9492936222</a> (9AM-5PM)</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-info">
                            <h3>Visit Us</h3>
                            <p>Ramachandra College of Engineering (RCE),<br>
                            NH-16 Bypass Road, Vatluru (V),<br>
                            Eluru-534007,<br>
                            West Godavari Dt., A.P.,<br>
                            India.</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="contact-info">
                            <h3>Support Hours</h3>
                            <p>Monday - Friday: 9am - 5pm<br>Saturday: 10am - 1pm</p>
                        </div>
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
            <a href="about-us.php" class="dock-item active">
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
                        <a href="../index.html#testimonials">Testimonials</a>
                    </div>
                    <div class="link-group">
                        <h3><i class="fas fa-map-marker-alt"></i> Contact</h3>
                        <a href="tel:+919492935222">+91-9492935222</a>
                        <a href="mailto:helpdesk@rcee.ac.in">helpdesk@rcee.ac.in</a>
                        <p class="footer-address">Ramachandra College of Engineering<br>Eluru-534007, A.P., India</p>
                    </div>
                    <div class="link-group">
                        <h3><i class="fas fa-gavel"></i> Legal</h3>
                        <a href="../privacy-policy.html">Privacy Policy</a>
                        <a href="../terms-of-service.html">Terms of Service</a>
                        <a href="../community-guidelines.html">Community Guidelines</a>
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
</body>
</html> 