// Header and Navigation JavaScript

// DOM Elements
const mobileMenuToggle = document.querySelector('.mobile-menu');
const navMenu = document.querySelector('.nav-menu');
const authButtons = document.querySelector('.auth-buttons');

// Mobile Menu Toggle
if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener('click', () => {
        navMenu.classList.toggle('show');
        authButtons.classList.toggle('show');
    });
}

// Smooth Scrolling for Internal Links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
            window.scrollTo({
                top: targetElement.offsetTop - 80,
                behavior: 'smooth'
            });
        }
    });
});

// Add Active Class to Navigation Links Based on Scroll Position
window.addEventListener('scroll', () => {
    const scrollPosition = window.scrollY;
    
    // Get all sections with IDs
    document.querySelectorAll('section[id]').forEach(section => {
        const sectionTop = section.offsetTop - 100;
        const sectionHeight = section.offsetHeight;
        const sectionId = section.getAttribute('id');
        
        if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
            document.querySelectorAll('.nav-menu a').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${sectionId}`) {
                    link.classList.add('active');
                }
            });
        }
    });
});

// Header JavaScript
document.addEventListener('DOMContentLoaded', () => {
    // Mobile menu toggle
    const mobileMenuButton = document.querySelector('.mobile-menu');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileMenuButton) {
        mobileMenuButton.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            mobileMenuButton.classList.toggle('active');
        });
    }

    // Handle scroll effects for navbar
    const navbar = document.querySelector('.navbar');
    let lastScrollTop = 0;
    
    window.addEventListener('scroll', () => {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop > 100) {
            navbar.classList.add('scrolled');
            
            if (scrollTop > lastScrollTop) {
                navbar.classList.add('scroll-down');
                navbar.classList.remove('scroll-up');
            } else {
                navbar.classList.add('scroll-up');
                navbar.classList.remove('scroll-down');
            }
        } else {
            navbar.classList.remove('scrolled');
        }
        
        lastScrollTop = scrollTop;
    });
    
    // Active link highlight
    const navLinks = document.querySelectorAll('.nav-menu a');
    const currentPage = window.location.pathname.split('/').pop();
    
    // Get the href attribute of each link and compare it with current URL
    navLinks.forEach(link => {
        const linkHref = link.getAttribute('href');
        
        // For home page with empty or index.html
        if ((currentPage === '' || currentPage === 'index.html') && (linkHref === '#home' || linkHref === 'index.html')) {
            link.classList.add('active');
        } 
        // For other pages
        else if (linkHref && linkHref.includes(currentPage)) {
            link.classList.add('active');
        }
    });
}); 