// Mind Sync JavaScript

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

// Testimonial Slider with FontAwesome Icons
const testimonials = [
    {
        content: "Finding Mind Sync was a turning point for me. The support here helped me through my darkest days.",
        author: "Alex, Member since 2022",
        icon: "fa-heart"
    },
    {
        content: "I was hesitant to join at first, but the community here is so welcoming and non-judgmental.",
        author: "Jamie, Member since 2023",
        icon: "fa-hands-helping"
    },
    {
        content: "The resources and discussions have given me tools to better manage my anxiety on a daily basis.",
        author: "Taylor, Member since 2022",
        icon: "fa-brain"
    }
];

const testimonialContainer = document.querySelector('.testimonial-slider');
let currentTestimonialIndex = 0;

// Function to display a testimonial
function displayTestimonial(index) {
    if (!testimonialContainer) return;
    
    const testimonial = testimonials[index];
    testimonialContainer.innerHTML = `
        <div class="testimonial">
            <div class="testimonial-icon">
                <i class="fas ${testimonial.icon}"></i>
            </div>
            <p>"${testimonial.content}"</p>
            <div class="user">- ${testimonial.author}</div>
        </div>
    `;
}

// Auto-rotate testimonials
if (testimonialContainer && testimonials.length > 1) {
    setInterval(() => {
        currentTestimonialIndex = (currentTestimonialIndex + 1) % testimonials.length;
        displayTestimonial(currentTestimonialIndex);
    }, 5000);
    
    // Display first testimonial
    displayTestimonial(currentTestimonialIndex);
}

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

// Add animation effects to icons on scroll
const animateOnScroll = () => {
    const elements = document.querySelectorAll('.card i, .feature-icon, .testimonial-icon');
    
    elements.forEach(element => {
        const elementPosition = element.getBoundingClientRect().top;
        const screenPosition = window.innerHeight / 1.3;
        
        if (elementPosition < screenPosition) {
            element.classList.add('animate');
        }
    });
};

window.addEventListener('scroll', animateOnScroll);

// Initialize animations on page load
document.addEventListener('DOMContentLoaded', () => {
    // Start scroll animations
    animateOnScroll();
});
