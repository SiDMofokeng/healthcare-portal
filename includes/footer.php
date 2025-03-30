</main>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <?php if (isset($custom_js)): ?>
        <script src="../assets/js/<?= $custom_js ?>"></script>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('profileForm');
            const msg = document.getElementById('successMessage');
            
            if (form && msg) {
                form.addEventListener('submit', function(e) {
                    // 1. Prevent normal form submission
                    e.preventDefault();
                    
                    // 2. Show message immediately
                    msg.textContent = 'Profile updated successfully!';
                    msg.style.display = 'block';
                    
                    // 3. Submit form data in background
                    fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form)
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Update failed');
                        // Keep message visible
                    })
                    .catch(error => {
                        msg.textContent = 'Error: ' + error.message;
                        msg.className = 'alert alert-danger mt-3';
                    });
                    
                    // 4. Hide after 3 seconds
                    setTimeout(() => {
                        msg.style.opacity = '0';
                        setTimeout(() => msg.style.display = 'none', 500);
                    }, 3000);
                });
            }
        });
    </script>

</body>
</html>