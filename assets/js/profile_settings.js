document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    });

// For the profile form (AJAX version)
document.getElementById('profileForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

    try {
        const formData = new FormData(form);
        
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData
        });

        // First check if the response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error(`Expected JSON but got: ${text.substring(0, 100)}...`);
        }

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || `Server returned ${response.status}`);
        }

        if (data.success) {
            showAlert(data.message || 'Profile updated successfully!', 'success');
            
            // Update UI if photo was changed
            if (data.photoUpdated) {
                const avatarImg = document.querySelector('.avatar img');
                if (avatarImg) {
                    avatarImg.src = `${avatarImg.src.split('?')[0]}?t=${new Date().getTime()}`;
                }
            }
        } else {
            throw new Error(data.message || 'Update failed');
        }
    } catch (error) {
        console.error('Update error:', error);
        
        let errorMessage = error.message;
        // Handle common error cases
        if (error.message.includes('Expected JSON')) {
            errorMessage = 'Server returned an invalid response. Please check console for details.';
        }
        
        showAlert(`Error: ${errorMessage}`, 'danger');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Improved alert function
function showAlert(message, type) {
    // Remove existing alerts
    document.querySelectorAll('.alert-dismissible').forEach(el => el.remove());
    
    const alertHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    const container = document.querySelector('.container-fluid.py-4');
    if (container) {
        container.insertAdjacentHTML('afterbegin', alertHTML);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) {
                bootstrap.Alert.getInstance(alert)?.close();
            }
        }, 5000);
    }
}

// For the password form (AJAX version)
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Updating...';
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Password changed successfully!', 'success');
            this.reset();
        } else {
            showAlert(data.message || 'Failed to change password', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Network error - please try again', 'danger');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Helper function to show alerts
function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to the top of the container
    const container = document.querySelector('.container-fluid.py-4');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alertDiv);
        bsAlert.close();
    }, 5000);
}

    // Email form submission
    document.getElementById('emailForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Updating...';
        
        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Email changed successfully',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', data.message || 'Failed to change email', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Network error - please try again', 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });

    // Preferences form submission
    document.getElementById('preferencesForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
        
        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Preferences updated successfully',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('Error', data.message || 'Failed to update preferences', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Network error - please try again', 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
});