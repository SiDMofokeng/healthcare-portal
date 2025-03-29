document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');

    const profileTrigger = document.querySelector('.profile-trigger');
    const dropdownMenu = document.querySelector('.dropdown-menu');

    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);

    // Toggle with overlay
    document.querySelector('.sidebar-toggle').addEventListener('click', () => {
    sidebar.classList.toggle('mobile-open');
    overlay.classList.toggle('show');
    });

    // Close on overlay click
    overlay.addEventListener('click', () => {
    sidebar.classList.remove('mobile-open');
    overlay.classList.remove('show');
    });

    // Swipe to close (touch devices)
    let touchStartX = 0;
    sidebar.addEventListener('touchstart', (e) => {
    touchStartX = e.changedTouches[0].screenX;
    });

    sidebar.addEventListener('touchend', (e) => {
    if (touchStartX - e.changedTouches[0].screenX > 50) {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('show');
    }
    });
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('sidebar-active');
        });
    }
    
    // Initialize charts
    const initCharts = () => {
        // System Load Chart (for admin dashboard)
        const systemLoadCtx = document.getElementById('systemLoadChart');
        if (systemLoadCtx) {
            new Chart(systemLoadCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'System Load (%)',
                        data: [30, 45, 28, 60, 35, 55],
                        borderColor: '#4361ee',
                        backgroundColor: 'rgba(67, 97, 238, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        }
        
        // Health Chart (for patient dashboard)
        const healthCtx = document.getElementById('healthChart');
        if (healthCtx) {
            new Chart(healthCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Heart Rate',
                        data: [72, 75, 70, 68, 72, 71],
                        backgroundColor: 'rgba(244, 63, 94, 0.7)',
                        borderColor: 'rgba(244, 63, 94, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: 60,
                            max: 100
                        }
                    }
                }
            });
        }
    };
    
    initCharts();
    
    // Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Notification dropdown
    const notificationBtn = document.querySelector('.notifications');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function() {
            // This would toggle a notification dropdown in a real implementation
            alert('Notifications dropdown would appear here');
        });
    }
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let valid = true;
            const requiredFields = this.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.style.borderColor = '#f72585';
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'error-message';
                    errorMsg.textContent = 'This field is required';
                    errorMsg.style.color = '#f72585';
                    errorMsg.style.fontSize = '0.8rem';
                    errorMsg.style.marginTop = '5px';
                    field.parentNode.appendChild(errorMsg);
                    
                    // Remove error message after 3 seconds
                    setTimeout(() => {
                        if (errorMsg.parentNode) {
                            errorMsg.parentNode.removeChild(errorMsg);
                        }
                    }, 3000);
                }
            });
            
            if (!valid) {
                e.preventDefault();
                const errorAlert = document.createElement('div');
                errorAlert.className = 'alert alert-error';
                errorAlert.textContent = 'Please fill in all required fields';
                this.prepend(errorAlert);
                
                // Remove alert after 3 seconds
                setTimeout(() => {
                    if (errorAlert.parentNode) {
                        errorAlert.parentNode.removeChild(errorAlert);
                    }
                }, 3000);
            }
        });
    });
    
    // Dynamic card actions
    document.querySelectorAll('.card').forEach(card => {
        const header = card.querySelector('.card-header');
        const body = card.querySelector('.card-body');
        
        if (header && body) {
            header.addEventListener('click', function() {
                body.style.display = body.style.display === 'none' ? 'block' : 'none';
            });
        }
    });

    // Toggle sidebar
    document.querySelector('.sidebar-toggle').addEventListener('click', () => {
        document.querySelector('.sidebar').classList.toggle('mobile-open');
    });
    
    // Close when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.sidebar') && !e.target.closest('.sidebar-toggle')) {
        document.querySelector('.sidebar').classList.remove('mobile-open');
        }
    });

    // Real-time badge updater
    function updateNotificationBadge() {
        fetch('/api/notifications.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.notification-badge');
            const currentCount = parseInt(badge.textContent);
            
            // Only animate if count increased
            if (data.count > currentCount) {
            badge.classList.add('pulse');
            setTimeout(() => badge.classList.remove('pulse'), 1000);
            }
            
            badge.textContent = data.count;
        });
    }
    
    // Update every 30 seconds + on page load
    updateNotificationBadge();
    setInterval(updateNotificationBadge, 30000);

});
