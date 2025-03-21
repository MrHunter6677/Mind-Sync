document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.querySelector('.signup-form');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm-password');
    const passwordToggle = document.querySelector('.password-toggle');
    const passwordStrengthBar = document.querySelector('.strength-bar');
    const passwordStrengthText = document.querySelector('.strength-text span');
    
    // Check for session error messages on page load
    const urlParams = new URLSearchParams(window.location.search);
    const errorParam = urlParams.get('error');
    
    if (errorParam) {
        showMessage('error', decodeURIComponent(errorParam));
    }
    
    // Function to display messages
    function showMessage(type, text) {
        // Remove any existing message
        const existingMessage = document.querySelector('.message-alert');
        if (existingMessage) {
            existingMessage.remove();
        }
        
        // Create new message element
        const messageDiv = document.createElement('div');
        messageDiv.className = `message-alert ${type}`;
        
        const icon = document.createElement('i');
        icon.className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
        
        const messageText = document.createElement('span');
        messageText.textContent = text;
        
        const closeBtn = document.createElement('button');
        closeBtn.className = 'close-btn';
        closeBtn.innerHTML = '<i class="fas fa-times"></i>';
        closeBtn.addEventListener('click', function() {
            messageDiv.remove();
        });
        
        messageDiv.appendChild(icon);
        messageDiv.appendChild(messageText);
        messageDiv.appendChild(closeBtn);
        
        // Insert message after form header
        const signupHeader = document.querySelector('.signup-header');
        signupHeader.after(messageDiv);
        
        // Auto-close message after 5 seconds for success messages
        if (type === 'success') {
            setTimeout(() => {
                messageDiv.remove();
            }, 5000);
        }
    }
    
    // Password toggle visibility
    if (passwordToggle) {
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle eye icon
            const eyeIcon = this.querySelector('i');
            eyeIcon.classList.toggle('fa-eye');
            eyeIcon.classList.toggle('fa-eye-slash');
        });
    }
    
    // Password strength checker
    if (passwordInput && passwordStrengthBar && passwordStrengthText) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            let status = '';
            
            if (password.length >= 8) strength += 1;
            if (password.match(/[a-z]+/)) strength += 1;
            if (password.match(/[A-Z]+/)) strength += 1;
            if (password.match(/[0-9]+/)) strength += 1;
            if (password.match(/[^a-zA-Z0-9]+/)) strength += 1;
            
            switch (strength) {
                case 0:
                case 1:
                    status = 'Weak';
                    passwordStrengthBar.style.width = '20%';
                    passwordStrengthBar.style.backgroundColor = '#ff4d4d';
                    break;
                case 2:
                    status = 'Fair';
                    passwordStrengthBar.style.width = '40%';
                    passwordStrengthBar.style.backgroundColor = '#ffa64d';
                    break;
                case 3:
                    status = 'Good';
                    passwordStrengthBar.style.width = '60%';
                    passwordStrengthBar.style.backgroundColor = '#ffff4d';
                    break;
                case 4:
                    status = 'Strong';
                    passwordStrengthBar.style.width = '80%';
                    passwordStrengthBar.style.backgroundColor = '#4dff4d';
                    break;
                case 5:
                    status = 'Very Strong';
                    passwordStrengthBar.style.width = '100%';
                    passwordStrengthBar.style.backgroundColor = '#4d4dff';
                    break;
            }
            
            passwordStrengthText.textContent = status;
        });
    }
    
    // Form submission with AJAX
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Basic form validation
            const firstname = document.getElementById('firstname').value.trim();
            const lastname = document.getElementById('lastname').value.trim();
            const email = document.getElementById('email').value.trim();
            const username = document.getElementById('username').value.trim();
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const terms = document.getElementById('terms').checked;
            
            // Validate form data
            if (!firstname || !lastname || !email || !username || !password) {
                showMessage('error', 'Please fill out all required fields');
                return;
            }
            
            if (password !== confirmPassword) {
                showMessage('error', 'Passwords do not match');
                return;
            }
            
            if (password.length < 8) {
                showMessage('error', 'Password must be at least 8 characters');
                return;
            }
            
            if (!terms) {
                showMessage('error', 'You must agree to the Terms of Service');
                return;
            }
            
            // Create form data object
            const formData = new FormData(this);
            
            // Debug output
            console.log('Form data being submitted:');
            for (const pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
            
            // Show loading indicator
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
            submitBtn.disabled = true;
            
            // Send AJAX request
            fetch('php/register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    showMessage('success', data.message);
                    // Redirect after successful registration
                    setTimeout(() => {
                        window.location.href = 'php/' + data.redirect;
                    }, 1500);
                } else {
                    showMessage('error', data.message);
                    submitBtn.innerHTML = originalBtnText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('error', 'An unexpected error occurred. Please try again.');
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            });
        });
    }
});
