document.addEventListener('DOMContentLoaded', function() {
    // Patient-specific functionality
    const welcomeBtn = document.querySelector('.welcome-card .btn');
    if (welcomeBtn) {
        welcomeBtn.addEventListener('click', function() {
            window.location.href = 'book_appointment.php';
        });
    }
    
    // Appointment actions
    document.querySelectorAll('.appointment-actions .btn-icon').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const action = this.querySelector('i').className.includes('times') ? 'Cancel' : 'Reschedule';
            alert(`Appointment would be ${action.toLowerCase()}ed`);
        });
    });
    
    // Prescription actions
    document.querySelectorAll('.rx-actions .btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.textContent.trim();
            alert(`Prescription ${action} would be processed`);
        });
    });
    
    // Health metrics animation
    const metricValues = document.querySelectorAll('.metric-value');
    metricValues.forEach(value => {
        const targetNumber = parseInt(value.textContent);
        let currentNumber = 0;
        const increment = targetNumber / 20;
        
        const timer = setInterval(() => {
            currentNumber += increment;
            if (currentNumber >= targetNumber) {
                clearInterval(timer);
                value.textContent = targetNumber;
            } else {
                value.textContent = Math.floor(currentNumber);
            }
        }, 50);
    });
});