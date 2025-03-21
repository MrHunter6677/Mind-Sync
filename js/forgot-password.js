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
    const forgotForm = document.getElementById('forgot-password-form');
    const resetForm = document.getElementById('reset-form');
    const step1 = document.getElementById('step-1');
    const step2 = document.getElementById('step-2');
    const resetEmailInput = document.getElementById('reset-email');
    const newPasswordInput = document.getElementById('new-password');
    const confirmPasswordInput = document.getElementById('confirm-password');
    const passwordToggle = document.querySelector('.password-toggle');
    const passwordStrengthBar = document.querySelector('.strength-bar');
    const passwordStrengthText = document.querySelector('.strength-text span');
    const emailInput = document.getElementById('email');
    const errorContainer = document.querySelector('.error-container');
    
    // Check for any URL parameters (e.g., error messages)
    const urlParams = new URLSearchParams(window.location.search);
    const errorMsg = urlParams.get('error');
    
    if (errorMsg) {
        showMessage(decodeURIComponent(errorMsg), 'error');
    }
    
    // Password toggle visibility
    if (passwordToggle) {
        passwordToggle.addEventListener('click', function() {
            const type = newPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            newPasswordInput.setAttribute('type', type);
            
            // Toggle eye icon
            const eyeIcon = this.querySelector('i');
            eyeIcon.classList.toggle('fa-eye');
            eyeIcon.classList.toggle('fa-eye-slash');
        });
    }
    
    // Password strength checker
    if (newPasswordInput && passwordStrengthBar && passwordStrengthText) {
        newPasswordInput.addEventListener('input', function() {
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
    
    // Step 1: Check if email exists
    if (forgotForm) {
        forgotForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Basic validation
            if (!emailInput.value.trim()) {
                showMessage('Please enter your email address', 'error');
                return;
            }
            
            // Email format validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailInput.value.trim())) {
                showMessage('Please enter a valid email address', 'error');
                return;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending Reset Link...';
            submitBtn.disabled = true;
            
            // Create form data
            const formData = new FormData(this);
            
            // Send AJAX request
            fetch('php/forgot_password.php', {
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
                    
                    // Disable the form inputs
                    emailInput.disabled = true;
                    submitBtn.disabled = true;
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
    
    // Step 2: Reset password
    if (resetForm) {
        resetForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = resetEmailInput.value.trim();
            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            // Validate password
            if (!newPassword || !confirmPassword) {
                showMessage('Please enter both password fields', 'error');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                showMessage('Passwords do not match', 'error');
                return;
            }
            
            if (newPassword.length < 8) {
                showMessage('Password must be at least 8 characters', 'error');
                return;
            }
            
            // Create form data
            const formData = new FormData();
            formData.append('email', email);
            formData.append('new_password', newPassword);
            formData.append('confirm_password', confirmPassword);
            
            // Show loading state
            const resetBtn = document.getElementById('reset-btn');
            const originalBtnText = resetBtn.innerHTML;
            resetBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Resetting...';
            resetBtn.disabled = true;
            
            // Send request to reset password
            fetch('php/reset_password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Reset password response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Reset password response:', data);
                
                if (data.success) {
                    showMessage(data.message, 'success');
                    
                    // Redirect to login page after a delay
                    setTimeout(() => {
                        window.location.href = '../' + (data.redirect || 'login.html');
                    }, 2000);
                } else {
                    showMessage(data.message, 'error');
                    resetBtn.innerHTML = originalBtnText;
                    resetBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An error occurred. Please try again.', 'error');
                resetBtn.innerHTML = originalBtnText;
                resetBtn.disabled = false;
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
        
        // Auto remove after 5 seconds (unless it's success)
        if (type !== 'success') {
            setTimeout(() => {
                messageDiv.classList.add('fade-out');
                setTimeout(() => {
                    if (errorContainer.contains(messageDiv)) {
                        errorContainer.removeChild(messageDiv);
                    }
                }, 500);
            }, 5000);
        }
    }
}); 