// Login Form JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // First check if user is already logged in
    fetch('./php/check_auth.php')
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
    const loginForm = document.getElementById('login-form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.querySelector('.password-toggle');
    const errorContainer = document.querySelector('.error-container');
    
    // Check for session error messages
    if (errorContainer) {
        const errorParam = new URLSearchParams(window.location.search).get('error');
        if (errorParam) {
            showMessage(decodeURIComponent(errorParam), 'error');
        }
    }
    
    // Toggle password visibility
    if (passwordToggle && passwordInput) {
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle icon
            this.innerHTML = type === 'password' ? 
                '<i class="fas fa-eye"></i>' : 
                '<i class="fas fa-eye-slash"></i>';
        });
    }
    
    // Form submission
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const email = emailInput.value.trim();
            const password = passwordInput.value;
            
            // Basic validation
            if (!email || !password) {
                showMessage('Please fill in all fields', 'error');
                return;
            }
            
            // Show loading indicator
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
            submitBtn.disabled = true;
            
            // Send AJAX request
            const formData = new FormData(this);
            
            fetch('./php/login.php', {
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
                    
                    // Redirect after a short delay
                    setTimeout(() => {
                        window.location.href = data.redirect || 'php/dashboard.php';
                    }, 1000);
                } else {
                    // Show error message
                    showMessage(data.message, 'error');
                    
                    // If user doesn't exist, show create account dialog
                    if (data.show_create_account) {
                        setTimeout(() => {
                            showCreateAccountDialog(email);
                        }, 1000);
                    }
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
        const messageDiv = document.createElement('div');
        messageDiv.className = `alert alert-${type}`;
        messageDiv.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            ${message}
        `;
        
        // Add message to container
        if (errorContainer) {
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
    }
    
    // Function to show create account dialog
    function showCreateAccountDialog(email) {
        const dialog = document.createElement('div');
        dialog.className = 'create-account-dialog';
        dialog.innerHTML = `
            <div class="dialog-content">
                <h3>Account Not Found</h3>
                <p>No account found with the email: <strong>${email}</strong></p>
                <p>Would you like to create a new account?</p>
                <div class="dialog-buttons">
                    <button class="btn btn-outline dialog-close">Cancel</button>
                    <a href="signup.html?email=${encodeURIComponent(email)}" class="btn btn-primary">Create Account</a>
                </div>
            </div>
        `;
        
        document.body.appendChild(dialog);
        
        // Add event listener to close button
        dialog.querySelector('.dialog-close').addEventListener('click', function() {
            dialog.classList.add('fade-out');
            setTimeout(() => {
                document.body.removeChild(dialog);
            }, 300);
        });
        
        // Add dialog show class after a small delay (for animation)
        setTimeout(() => {
            dialog.classList.add('show');
        }, 10);
    }
});
