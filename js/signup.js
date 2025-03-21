// Signup Form JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // First check if user is already logged in
    fetch('php/check_auth.php')
        .then(response => response.json())
        .then(data => {
            if (data.logged_in && data.redirect) {
                window.location.href = data.redirect;
                return;
            }
        })
        .catch(error => {
            console.error('Error checking auth status:', error);
        });
        
    // DOM Elements
    const signupForm = document.getElementById('signup-form');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const passwordToggle = document.querySelector('.password-toggle');
    const errorContainer = document.querySelector('.error-container');
    
    // Password Strength Meter Elements
    const strengthMeter = document.querySelector('.meter-bar');
    const strengthText = document.querySelector('.strength-text');
    
    // Toggle password visibility
    if (passwordToggle && passwordInput) {
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle eye icon
            this.innerHTML = type === 'password' ? 
                '<i class="fas fa-eye"></i>' : 
                '<i class="fas fa-eye-slash"></i>';
        });
    }
    
    // Password Strength Checker
    if (passwordInput && strengthMeter && strengthText) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength += 25;
            
            // Contains lowercase
            if (/[a-z]/.test(password)) strength += 25;
            
            // Contains uppercase
            if (/[A-Z]/.test(password)) strength += 25;
            
            // Contains numbers or special characters
            if (/[0-9!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 25;
            
            // Update strength meter
            strengthMeter.style.width = strength + '%';
            
            // Update color based on strength
            if (strength < 25) {
                strengthMeter.style.backgroundColor = '#ff4d4d'; // Red
                strengthText.textContent = 'Password strength: Too weak';
            } else if (strength < 50) {
                strengthMeter.style.backgroundColor = '#ffaa00'; // Orange
                strengthText.textContent = 'Password strength: Weak';
            } else if (strength < 75) {
                strengthMeter.style.backgroundColor = '#ffff00'; // Yellow
                strengthText.textContent = 'Password strength: Medium';
            } else if (strength < 100) {
                strengthMeter.style.backgroundColor = '#00cc00'; // Green
                strengthText.textContent = 'Password strength: Strong';
            } else {
                strengthMeter.style.backgroundColor = '#00cc00'; // Green
                strengthText.textContent = 'Password strength: Very strong';
            }
        });
    }
    
    // Form submission
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Basic validation
            if (!nameInput.value.trim() || !emailInput.value.trim() || !passwordInput.value || !confirmPasswordInput.value) {
                showMessage('Please fill in all fields', 'error');
                return;
            }
            
            if (passwordInput.value !== confirmPasswordInput.value) {
                showMessage('Passwords do not match', 'error');
                return;
            }
            
            // Password strength validation
            if (passwordInput.value.length < 8) {
                showMessage('Password must be at least 8 characters long', 'error');
                return;
            }
            
            // Check terms checkbox
            const termsCheckbox = document.querySelector('input[name="terms"]');
            if (termsCheckbox && !termsCheckbox.checked) {
                showMessage('You must agree to the Terms of Service and Privacy Policy', 'error');
                return;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
            submitBtn.disabled = true;
            
            // Create form data
            const formData = new FormData(this);
            
            // Send AJAX request
            fetch('php/register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Reset button
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
                
                if (data.success) {
                    // Show success message
                    showMessage(data.message, 'success');
                    
                    // Redirect to dashboard after a short delay
                    setTimeout(() => {
                        window.location.href = data.redirect || 'php/dashboard.php';
                    }, 1500);
                } else {
                    // Show error message
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
                showMessage('An error occurred. Please try again.', 'error');
            });
        });
    }
    
    // Function to show messages
    function showMessage(message, type = 'info') {
        if (!errorContainer) return;
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `alert alert-${type}`;
        messageDiv.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            ${message}
        `;
        
        // Clear existing messages
        errorContainer.innerHTML = '';
        errorContainer.appendChild(messageDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            messageDiv.classList.add('fade-out');
            setTimeout(() => {
                if (errorContainer.contains(messageDiv)) {
                    errorContainer.removeChild(messageDiv);
                }
            }, 500);
        }, 5000);
    }
}); 