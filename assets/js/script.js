// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    // Dropdown menus
    const dropdowns = document.querySelectorAll('.user-menu');
    
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                const menu = this.querySelector('.dropdown');
                menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
            }
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.user-menu')) {
            document.querySelectorAll('.dropdown').forEach(dropdown => {
                if (window.innerWidth <= 768) {
                    dropdown.style.display = 'none';
                }
            });
        }
    });
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let valid = true;
            const required = this.querySelectorAll('[required]');
            
            required.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.style.borderColor = '#e74c3c';
                } else {
                    field.style.borderColor = '';
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Please fill in all required fields');
            }
        });
    });
});