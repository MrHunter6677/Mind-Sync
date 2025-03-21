// Profile Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const tabs = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    const messageContainer = document.querySelector('.message-container');
    const updateProfileForm = document.getElementById('update-profile-form');
    const changePasswordForm = document.getElementById('change-password-form');
    const notificationSettingsForm = document.getElementById('notification-settings-form');
    const newPasswordInput = document.getElementById('new-password');
    const mobileMenuToggle = document.querySelector('.mobile-menu');
    const navMenu = document.querySelector('.nav-menu');
    const authButtons = document.querySelector('.auth-buttons');
    const profilePictureInput = document.getElementById('profile-picture');
    const profilePreview = document.getElementById('profile-preview');
    const removePictureBtn = document.getElementById('remove-picture');

    // Mobile Menu Toggle
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', () => {
            navMenu.classList.toggle('show');
            authButtons.classList.toggle('show');
        });
    }

    // Tab switching functionality
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs and panes
            tabs.forEach(t => t.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Get the tab pane id and activate it
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });

    // Profile picture preview on file selection
    if (profilePictureInput && profilePreview) {
        profilePictureInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                
                // Check file size (2MB max)
                if (file.size > 2 * 1024 * 1024) {
                    showMessage('File size exceeds 2MB limit. Please choose a smaller image.', 'error');
                    this.value = ''; // Clear the file input
                    return;
                }
                
                // Check file type
                const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    showMessage('Invalid file type. Please upload JPG, PNG, or GIF images only.', 'error');
                    this.value = ''; // Clear the file input
                    return;
                }
                
                // Create preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
                
                // Show remove button and add the has-image class to the profile preview
                if (removePictureBtn) {
                    removePictureBtn.style.display = 'inline-block';
                }
                profilePreview.parentElement.classList.add('has-image');
            }
        });
    }
    
    // Remove profile picture
    if (removePictureBtn && profilePreview) {
        removePictureBtn.addEventListener('click', function() {
            // Reset the file input
            if (profilePictureInput) {
                profilePictureInput.value = '';
            }
            
            // Update preview to default image
            profilePreview.src = '../img/default-avatar.png';
            
            // Set a flag to indicate picture removal
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'remove_picture';
            hiddenInput.value = '1';
            
            // Add to form if not already there
            const existingInput = document.querySelector('input[name="remove_picture"]');
            if (existingInput) {
                existingInput.value = '1';
            } else {
                updateProfileForm.appendChild(hiddenInput);
            }
            
            // Hide remove button and remove the has-image class
            this.style.display = 'none';
            profilePreview.parentElement.classList.remove('has-image');
            
            showMessage('Profile picture will be removed upon saving changes.', 'info');
        });
    }

    // Password strength meter
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            const strengthMeter = document.querySelector('.meter-bar');
            const strengthText = document.querySelector('.strength-text');
            
            // Calculate password strength
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

    // Update Profile Form Submission
    if (updateProfileForm) {
        updateProfileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            submitBtn.disabled = true;
            
            // Create form data
            const formData = new FormData(this);
            
            // Send AJAX request
            fetch('update_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Reset button
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
                
                // Show message
                showMessage(data.message, data.success ? 'success' : 'error');
                
                // If successful, update the profile header with new information
                if (data.success) {
                    // Update full name in profile header
                    if (data.first_name && data.last_name) {
                        const fullName = `${data.first_name} ${data.last_name}`;
                        document.querySelector('.profile-info h1').textContent = fullName;
                    }
                    
                    // Update email in profile header
                    if (data.email) {
                        const emailElement = document.querySelector('.profile-info p:nth-child(2)');
                        if (emailElement) {
                            emailElement.innerHTML = `<i class="fas fa-envelope"></i> ${data.email}`;
                        }
                    }
                    
                    // Update profile picture if provided in response
                    if (data.timestamp) {
                        // Force a refresh of the profile image by adding a timestamp parameter
                        const timestamp = data.timestamp;
                        
                        // Update avatar in header
                        const headerAvatar = document.querySelector('.profile-avatar');
                        const headerAvatarImg = headerAvatar.querySelector('img');
                        
                        if (headerAvatarImg) {
                            headerAvatarImg.src = `display_profile_pic.php?t=${timestamp}`;
                        } else {
                            headerAvatar.innerHTML = `<img src="display_profile_pic.php?t=${timestamp}" alt="Profile Picture">`;
                        }
                        
                        // Update preview image
                        profilePreview.src = `display_profile_pic.php?t=${timestamp}`;
                        
                        // Remove hidden input for picture removal if exists
                        const removeInput = document.querySelector('input[name="remove_picture"]');
                        if (removeInput) {
                            removeInput.remove();
                        }
                    } else if (data.picture_removed) {
                        // Update header avatar to show default icon
                        const headerAvatar = document.querySelector('.profile-avatar');
                        headerAvatar.innerHTML = '<i class="fas fa-user-circle"></i>';
                        
                        // Reset preview to default
                        profilePreview.src = '../img/default-avatar.png';
                        
                        // Hide remove button
                        if (removePictureBtn) {
                            removePictureBtn.style.display = 'none';
                        }
                        
                        // Remove hidden input for picture removal if exists
                        const removeInput = document.querySelector('input[name="remove_picture"]');
                        if (removeInput) {
                            removeInput.remove();
                        }
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

    // Change Password Form Submission
    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const currentPassword = document.getElementById('current-password').value;
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            
            // Basic validation
            if (!currentPassword || !newPassword || !confirmPassword) {
                showMessage('Please fill in all fields', 'error');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                showMessage('New passwords do not match', 'error');
                return;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Changing...';
            submitBtn.disabled = true;
            
            // Create form data
            const formData = new FormData(this);
            
            // Send AJAX request
            fetch('change_password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Reset button
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
                
                // Show message
                showMessage(data.message, data.success ? 'success' : 'error');
                
                // If successful, reset the form
                if (data.success) {
                    this.reset();
                    document.querySelector('.meter-bar').style.width = '0';
                    document.querySelector('.strength-text').textContent = 'Password strength: Too weak';
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

    // Notification Settings Form Submission
    if (notificationSettingsForm) {
        notificationSettingsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            submitBtn.disabled = true;
            
            // Create form data with checkbox values
            const formData = new FormData();
            formData.append('email_notifications', document.querySelector('input[name="email_notifications"]').checked ? '1' : '0');
            formData.append('reply_notifications', document.querySelector('input[name="reply_notifications"]').checked ? '1' : '0');
            formData.append('event_notifications', document.querySelector('input[name="event_notifications"]').checked ? '1' : '0');
            
            // Simulate success (replace with actual API call)
            setTimeout(() => {
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
                showMessage('Notification preferences updated successfully', 'success');
            }, 1000);
        });
    }

    // Function to show messages
    function showMessage(message, type = 'info') {
        if (!messageContainer) return;
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
            ${message}
        `;
        
        // Clear existing messages
        messageContainer.innerHTML = '';
        messageContainer.appendChild(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            alertDiv.style.opacity = '0';
            setTimeout(() => {
                if (messageContainer.contains(alertDiv)) {
                    messageContainer.removeChild(alertDiv);
                }
            }, 300);
        }, 5000);
    }
}); 