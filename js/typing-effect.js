// Typing Effect - Mind Sync
document.addEventListener('DOMContentLoaded', function() {
    console.log("Typing effect initializing...");
    
    // Quotes to cycle through
    const texts = [
        "Welcome to Mind Sync",
        "Your mental health matters",
        "Find support and understanding",
        "You are not alone in this journey",
        "Healing happens in community",
        "Share your story, find your strength"
    ];

    // Get DOM elements
    const typingText = document.querySelector('.typing-text');
    const cursor = document.querySelector('.typing-cursor');

    if (!typingText || !cursor) {
        console.error("Typing elements not found:", { 
            typingText: !!typingText, 
            cursor: !!cursor 
        });
        return;
    }

    let textIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    let isWaiting = false;

    function type() {
        // Debug logs
        console.log(`Current state: textIndex=${textIndex}, charIndex=${charIndex}, isDeleting=${isDeleting}`);
        
        // Get current text
        const currentText = texts[textIndex];
        
        // Apply directly to DOM
        typingText.textContent = currentText.substring(0, charIndex);
        
        // Debug the content
        console.log("Element content:", typingText.textContent);
        console.log("Element visibility:", window.getComputedStyle(typingText).visibility);
        console.log("Element color:", window.getComputedStyle(typingText).color);
        
        // Typing speed
        let speed = isDeleting ? 50 : 100;

        // Handle typing logic
        if (!isDeleting && charIndex < currentText.length) {
            // Typing forward
            charIndex++;
        } else if (!isDeleting && charIndex === currentText.length && !isWaiting) {
            // Reached end of text, wait before deleting
            isWaiting = true;
            setTimeout(() => {
                isWaiting = false;
                isDeleting = true;
            }, 2000);
            return setTimeout(type, 50);
        } else if (isDeleting && charIndex > 0) {
            // Deleting
            charIndex--;
        } else if (isDeleting && charIndex === 0) {
            // Move to next text
            isDeleting = false;
            textIndex = (textIndex + 1) % texts.length;
        }

        setTimeout(type, speed);
    }

    // Start typing effect with delay
    console.log("Starting typing effect...");
    setTimeout(type, 1000);
}); 