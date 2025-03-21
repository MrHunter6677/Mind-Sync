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
    <title>Mental Health Guides | Mind Sync</title>
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
        
        /* Guides Section Styles */
        .guides-section {
            background-color: var(--background-color);
            padding: 2rem 0;
            min-height: calc(100vh - 250px);
        }
        
        .guides-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .guides-header h1 {
            color: var(--text-color);
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .guides-header h1 i {
            margin-right: 0.75rem;
            color: var(--primary-color);
        }
        
        .guides-header p {
            color: var(--light-text);
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .guides-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        /* Category Navigation */
        .guides-categories {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 2.5rem;
        }
        
        .category-pill {
            background: white;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            padding: 0.5rem 1.25rem;
            border-radius: 25px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .category-pill:hover, .category-pill.active {
            background: var(--primary-color);
            color: white;
        }
        
        /* Guide Cards */
        .guides-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .guide-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        
        .guide-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }
        
        .guide-image {
            height: 180px;
            overflow: hidden;
            position: relative;
        }
        
        .guide-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .guide-card:hover .guide-image img {
            transform: scale(1.05);
        }
        
        .guide-category {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(123, 104, 238, 0.85);
            color: white;
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .guide-content {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .guide-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.75rem;
            line-height: 1.3;
        }
        
        .guide-excerpt {
            color: var(--light-text);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1rem;
            flex-grow: 1;
        }
        
        .guide-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            font-size: 0.85rem;
            color: var(--lighter-text);
        }
        
        .guide-date {
            display: flex;
            align-items: center;
        }
        
        .guide-date i {
            margin-right: 0.4rem;
        }
        
        .guide-read-more {
            display: inline-flex;
            align-items: center;
            color: var(--primary-color);
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .guide-read-more i {
            margin-left: 0.3rem;
            transition: transform 0.3s ease;
        }
        
        .guide-read-more:hover {
            color: var(--accent-color);
        }
        
        .guide-read-more:hover i {
            transform: translateX(3px);
        }
        
        /* Featured Resources */
        .featured-resources {
            margin-bottom: 3rem;
        }
        
        .section-title {
            font-size: 1.5rem;
            color: var(--text-color);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 0.75rem;
            color: var(--primary-color);
        }
        
        .resources-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.25rem;
        }
        
        .resource-item {
            background: white;
            border-radius: 10px;
            padding: 1.25rem;
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }
        
        .resource-item:hover {
            transform: translateY(-3px);
        }
        
        .resource-item h3 {
            font-size: 1.1rem;
            margin-bottom: 0.75rem;
            color: var(--text-color);
        }
        
        .resource-item p {
            font-size: 0.9rem;
            color: var(--light-text);
            margin-bottom: 1rem;
            line-height: 1.5;
            flex-grow: 1;
        }
        
        .resource-link {
            display: inline-flex;
            align-items: center;
            color: var(--primary-color);
            font-weight: 500;
            font-size: 0.9rem;
            text-decoration: none;
            margin-top: auto;
        }
        
        .resource-link i {
            margin-left: 0.3rem;
            transition: transform 0.3s ease;
        }
        
        .resource-link:hover i {
            transform: translateX(3px);
        }
        
        /* Quick Help Box */
        .quick-help {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 3rem;
            box-shadow: var(--shadow);
        }
        
        .quick-help h2 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .crisis-resources {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .crisis-item {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 1rem;
            backdrop-filter: blur(5px);
        }
        
        .crisis-item h3 {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }
        
        .crisis-item h3 i {
            margin-right: 0.5rem;
        }
        
        .crisis-item p, .crisis-item a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
        }
        
        .crisis-item a {
            display: inline-block;
            margin-top: 0.5rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .crisis-item a:hover {
            color: white;
            text-decoration: underline;
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
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .guides-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .resources-grid {
                grid-template-columns: 1fr;
            }
            
            .guide-image {
                height: 160px;
            }
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

    <!-- Add script to ensure active state is set correctly -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set active state for guides nav item
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                if (link.getAttribute('href').includes('guides')) {
                    link.classList.add('active');
                } else if (link.classList.contains('active')) {
                    link.classList.remove('active');
                }
            });
        });
    </script>

    <section class="guides-section">
        <div class="guides-container">
            <div class="guides-header">
                <h1><i class="fas fa-book-open"></i> Mental Health Guides</h1>
                <p>Access expert resources, articles, and information to support your mental well-being journey</p>
            </div>
            
            <!-- Quick Help Box -->
            <div class="quick-help">
                <h2><i class="fas fa-hands-helping"></i> Need Immediate Help?</h2>
                <p>If you're experiencing a mental health crisis or need immediate support, please use these resources:</p>
                
                <div class="crisis-resources">
                    <div class="crisis-item">
                        <h3><i class="fas fa-phone"></i> Crisis Helpline</h3>
                        <p>Support: <strong>+91-9492935222</strong> (9AM-5PM)</p>
                        <a href="tel:+919492935222">Call Now</a>
                    </div>
                    
                    <div class="crisis-item">
                        <h3><i class="fas fa-comment-dots"></i> Email Support</h3>
                        <p>Email us at: <strong>helpdesk@rcee.ac.in</strong></p>
                        <a href="mailto:helpdesk@rcee.ac.in">Email Now</a>
                    </div>
                    
                    <div class="crisis-item">
                        <h3><i class="fas fa-hospital"></i> College Support</h3>
                        <p>Ramachandra College of Engineering, Eluru-534007</p>
                        <a href="tel:+919492936222">Call Alternate: +91-9492936222</a>
                    </div>
                </div>
            </div>
            
            <!-- Category Navigation -->
            <div class="guides-categories">
                <button class="category-pill active" data-category="all">All Guides</button>
                <button class="category-pill" data-category="anxiety">Anxiety</button>
                <button class="category-pill" data-category="depression">Depression</button>
                <button class="category-pill" data-category="mindfulness">Mindfulness</button>
                <button class="category-pill" data-category="stress">Stress Management</button>
                <button class="category-pill" data-category="self-care">Self-Care</button>
                <button class="category-pill" data-category="therapy">Therapy</button>
            </div>
            
            <!-- Guides Grid -->
            <div class="guides-grid">
                <!-- Anxiety Guide -->
                <div class="guide-card" data-category="anxiety">
                    <div class="guide-image">
                        <img src="https://images.unsplash.com/photo-1618616191524-a9721186cbe4?q=80&w=2069&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Understanding Anxiety">
                        <span class="guide-category">Anxiety</span>
                    </div>
                    <div class="guide-content">
                        <h3 class="guide-title">Understanding and Managing Anxiety Disorders</h3>
                        <p class="guide-excerpt">Learn about different types of anxiety disorders, their symptoms, and effective coping strategies to manage anxiety in your daily life.</p>
                        <div class="guide-meta">
                            <div class="guide-date"><i class="far fa-calendar"></i> Updated: June 2023</div>
                            <a href="guides/anxiety.php" class="guide-read-more">Read more <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                
                <!-- Depression Guide -->
                <div class="guide-card" data-category="depression">
                    <div class="guide-image">
                        <img src="https://plus.unsplash.com/premium_photo-1668062843172-0129f25a1276?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MXx8ZGVwcmVzc2lvbnxlbnwwfHwwfHx8MA%3D%3D" alt="Depression Guide">
                        <span class="guide-category">Depression</span>
                    </div>
                    <div class="guide-content">
                        <h3 class="guide-title">Recognizing and Treating Depression</h3>
                        <p class="guide-excerpt">Explore the symptoms, causes, and treatment options for depression, along with practical self-help techniques for managing depressive episodes.</p>
                        <div class="guide-meta">
                            <div class="guide-date"><i class="far fa-calendar"></i> Updated: July 2023</div>
                            <a href="guides/depression.php" class="guide-read-more">Read more <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                
                <!-- Mindfulness Guide -->
                <div class="guide-card" data-category="mindfulness">
                    <div class="guide-image">
                        <img src="https://images.unsplash.com/photo-1506126613408-eca07ce68773?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTB8fG1lZGl0YXRpb258ZW58MHx8MHx8fDA%3D&auto=format&fit=crop&w=600&q=60" alt="Mindfulness Practice">
                        <span class="guide-category">Mindfulness</span>
                    </div>
                    <div class="guide-content">
                        <h3 class="guide-title">Mindfulness Practices for Better Mental Health</h3>
                        <p class="guide-excerpt">Discover the benefits of mindfulness meditation and how to incorporate mindfulness techniques into your daily routine for reduced stress and anxiety.</p>
                        <div class="guide-meta">
                            <div class="guide-date"><i class="far fa-calendar"></i> Updated: August 2023</div>
                            <a href="guides/mindfulness.php" class="guide-read-more">Read more <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                
                <!-- Stress Management Guide -->
                <div class="guide-card" data-category="stress">
                    <div class="guide-image">
                        <img src="https://images.unsplash.com/photo-1465919292275-c60ba49da6ae?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8OHx8c3RyZXNzfGVufDB8fDB8fHww&auto=format&fit=crop&w=600&q=60" alt="Stress Management">
                        <span class="guide-category">Stress Management</span>
                    </div>
                    <div class="guide-content">
                        <h3 class="guide-title">Effective Strategies for Managing Stress</h3>
                        <p class="guide-excerpt">Learn practical techniques for recognizing stress triggers and developing healthy coping mechanisms to manage stress in your personal and professional life.</p>
                        <div class="guide-meta">
                            <div class="guide-date"><i class="far fa-calendar"></i> Updated: May 2023</div>
                            <a href="guides/stress-management.php" class="guide-read-more">Read more <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                
                <!-- Self-Care Guide -->
                <div class="guide-card" data-category="self-care">
                    <div class="guide-image">
                        <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8c2VsZiUyMGNhcmV8ZW58MHx8MHx8fDA%3D&auto=format&fit=crop&w=600&q=60" alt="Self-Care Practices">
                        <span class="guide-category">Self-Care</span>
                    </div>
                    <div class="guide-content">
                        <h3 class="guide-title">Building a Personalized Self-Care Routine</h3>
                        <p class="guide-excerpt">Discover the importance of self-care for mental wellness and learn how to design a self-care plan that addresses your unique emotional, physical, and social needs.</p>
                        <div class="guide-meta">
                            <div class="guide-date"><i class="far fa-calendar"></i> Updated: September 2023</div>
                            <a href="guides/self-care.php" class="guide-read-more">Read more <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                
                <!-- Therapy Guide -->
                <div class="guide-card" data-category="therapy">
                    <div class="guide-image">
                        <img src="https://images.unsplash.com/photo-1573497620053-ea5300f94f21?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NXx8dGhlcmFweXxlbnwwfHwwfHx8MA%3D%3D&auto=format&fit=crop&w=600&q=60" alt="Therapy Types">
                        <span class="guide-category">Therapy</span>
                    </div>
                    <div class="guide-content">
                        <h3 class="guide-title">Understanding Different Therapy Approaches</h3>
                        <p class="guide-excerpt">Explore various therapy types, including CBT, DBT, and psychodynamic therapy, to help you make informed decisions about which approach might best address your mental health needs.</p>
                        <div class="guide-meta">
                            <div class="guide-date"><i class="far fa-calendar"></i> Updated: July 2023</div>
                            <a href="guides/therapy-approaches.php" class="guide-read-more">Read more <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Featured Resources Section -->
            <div class="featured-resources">
                <h2 class="section-title"><i class="fas fa-bookmark"></i> Recommended Books & Resources</h2>
                
                <div class="resources-grid">
                    <div class="resource-item">
                        <h3>The Body Keeps the Score</h3>
                        <p>By Bessel van der Kolk, M.D. — Explores the connection between trauma and physical health.</p>
                        <a href="https://www.amazon.com/Body-Keeps-Score-Healing-Trauma/dp/0143127748" target="_blank" class="resource-link">View Book <i class="fas fa-external-link-alt"></i></a>
                    </div>
                    
                    <div class="resource-item">
                        <h3>Feeling Good: The New Mood Therapy</h3>
                        <p>By David D. Burns — A practical guide to CBT techniques for depression and anxiety.</p>
                        <a href="https://www.amazon.com/Feeling-Good-New-Mood-Therapy/dp/0380810336" target="_blank" class="resource-link">View Book <i class="fas fa-external-link-alt"></i></a>
                    </div>
                    
                    <div class="resource-item">
                        <h3>The Mindful Way Through Depression</h3>
                        <p>By Mark Williams, John Teasdale, Zindel Segal, and Jon Kabat-Zinn — Mindfulness approaches to manage depression.</p>
                        <a href="https://www.amazon.com/Mindful-Way-through-Depression-Unhappiness/dp/1593851286" target="_blank" class="resource-link">View Book <i class="fas fa-external-link-alt"></i></a>
                    </div>
                    
                    <div class="resource-item">
                        <h3>Maybe You Should Talk to Someone</h3>
                        <p>By Lori Gottlieb — A therapist's perspective on the therapeutic process and its benefits.</p>
                        <a href="https://www.amazon.com/Maybe-You-Should-Talk-Someone/dp/1328662055" target="_blank" class="resource-link">View Book <i class="fas fa-external-link-alt"></i></a>
                    </div>
                </div>
            </div>
            
            <!-- Academic Articles Section -->
            <div class="featured-resources">
                <h2 class="section-title"><i class="fas fa-graduation-cap"></i> Academic Articles & Research</h2>
                
                <div class="resources-grid">
                    <div class="resource-item">
                        <h3>Efficacy of Mindfulness-Based Interventions</h3>
                        <p>A meta-analysis published in the Journal of Clinical Psychology examining the effectiveness of mindfulness practices.</p>
                        <a href="https://pubmed.ncbi.nlm.nih.gov/" target="_blank" class="resource-link">View Research <i class="fas fa-external-link-alt"></i></a>
                    </div>
                    
                    <div class="resource-item">
                        <h3>Exercise as a Treatment for Depression</h3>
                        <p>Current research on the role of physical activity in managing depressive symptoms, published in the Journal of Psychiatric Research.</p>
                        <a href="https://pubmed.ncbi.nlm.nih.gov/" target="_blank" class="resource-link">View Research <i class="fas fa-external-link-alt"></i></a>
                    </div>
                    
                    <div class="resource-item">
                        <h3>Sleep and Mental Health</h3>
                        <p>Recent findings on the bidirectional relationship between sleep quality and mental health disorders in the Harvard Review of Psychiatry.</p>
                        <a href="https://pubmed.ncbi.nlm.nih.gov/" target="_blank" class="resource-link">View Research <i class="fas fa-external-link-alt"></i></a>
                    </div>
                    
                    <div class="resource-item">
                        <h3>Digital Interventions for Anxiety Disorders</h3>
                        <p>A systematic review of the effectiveness of app-based and online therapy tools for anxiety management.</p>
                        <a href="https://pubmed.ncbi.nlm.nih.gov/" target="_blank" class="resource-link">View Research <i class="fas fa-external-link-alt"></i></a>
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
            <a href="guides.php" class="dock-item active">
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

    <style>
    .footer-address {
        font-size: 0.85rem;
        color: rgba(255,255,255,0.7);
        margin-top: 5px;
        line-height: 1.4;
    }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Category filtering
            const categoryButtons = document.querySelectorAll('.category-pill');
            const guideCards = document.querySelectorAll('.guide-card');
            
            categoryButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const category = this.dataset.category;
                    
                    // Remove active class from all buttons
                    categoryButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    // Show/hide guide cards based on category
                    guideCards.forEach(card => {
                        if (category === 'all' || card.dataset.category === category) {
                            card.style.display = 'flex';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });
            
            // Update year in footer
            document.querySelector('.year').textContent = new Date().getFullYear();
        });
    </script>
</body>
</html> 