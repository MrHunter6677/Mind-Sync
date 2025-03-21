// Community Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const postModal = document.getElementById('postModal');
    const postForm = document.getElementById('postForm');
    const loadingIndicator = document.querySelector('.loading');

    // Open post modal
    window.openPostModal = function() {
        if (postModal) {
            postModal.style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        }
    };

    // Close post modal
    window.closePostModal = function() {
        if (postModal) {
            postModal.style.display = 'none';
            document.body.style.overflow = ''; // Restore scrolling
            // Reset form
            if (postForm) {
                postForm.reset();
            }
        }
    };

    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === postModal) {
            closePostModal();
        }
    });

    // Submit post form
    window.submitPost = function(e) {
        e.preventDefault();

        // Get form data
        const formData = new FormData(postForm);
        const title = formData.get('title').trim();
        const content = formData.get('content').trim();
        const category = formData.get('category');

        // Validate form data
        if (!title || !content || !category) {
            showMessage('Please fill in all fields', 'error');
            return;
        }

        // Show loading indicator
        if (loadingIndicator) {
            loadingIndicator.style.display = 'flex';
        }

        // Send AJAX request
        fetch('../php/create_post.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                closePostModal();
                // Reload the page to show the new post
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showMessage(data.message || 'Error creating post', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred. Please try again.', 'error');
        })
        .finally(() => {
            if (loadingIndicator) {
                loadingIndicator.style.display = 'none';
            }
        });
    };

    // Like post functionality
    document.querySelectorAll('.like-action').forEach(likeBtn => {
        likeBtn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const isLiked = this.classList.contains('liked');
            const likesCount = this.querySelector('.likes-count');
            const heartIcon = this.querySelector('i');

            fetch('../php/like_post.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    post_id: postId,
                    action: isLiked ? 'unlike' : 'like'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Toggle liked state
                    this.classList.toggle('liked');
                    // Update heart icon
                    heartIcon.className = this.classList.contains('liked') ? 'fas fa-heart' : 'far fa-heart';
                    // Update likes count
                    if (likesCount) {
                        likesCount.textContent = data.likes;
                    }
                } else {
                    showMessage(data.message || 'Error updating like', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An error occurred. Please try again.', 'error');
            });
        });
    });

    // Delete post functionality
    window.confirmDelete = function(postId) {
        if (confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
            fetch('../php/delete_post.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ post_id: postId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    // Remove the post card from the DOM
                    const postCard = document.querySelector(`.post-card[data-post-id="${postId}"]`);
                    if (postCard) {
                        postCard.remove();
                    }
                    // If no posts left, show the no-posts message
                    const postsContainer = document.querySelector('.posts-container');
                    if (postsContainer && !postsContainer.querySelector('.post-card')) {
                        postsContainer.innerHTML = `
                            <div class="no-posts">
                                <i class="fas fa-comment-dots fa-3x"></i>
                                <p>No posts yet. Be the first to share!</p>
                            </div>
                        `;
                    }
                } else {
                    showMessage(data.message || 'Error deleting post', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An error occurred. Please try again.', 'error');
            });
        }
    };

    // Function to show messages
    function showMessage(message, type = 'info') {
        const messageContainer = document.createElement('div');
        messageContainer.className = `alert alert-${type}`;
        messageContainer.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            ${message}
        `;

        // Add message to container
        const container = document.querySelector('.container');
        if (container) {
            // Remove any existing messages
            const existingMessage = container.querySelector('.alert');
            if (existingMessage) {
                existingMessage.remove();
            }

            // Insert new message at the top of the container
            container.insertBefore(messageContainer, container.firstChild);

            // Auto remove after 5 seconds
            setTimeout(() => {
                messageContainer.style.opacity = '0';
                setTimeout(() => {
                    if (container.contains(messageContainer)) {
                        container.removeChild(messageContainer);
                    }
                }, 300);
            }, 5000);
        }
    }
}); 