document.addEventListener('DOMContentLoaded', function() {
    // Admin-specific functionality
    const quickActionButtons = document.querySelectorAll('.quick-action');
    
    quickActionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const action = this.querySelector('span').textContent;
            alert(`Admin ${action} action would be performed here`);
        });
    });
    
    // System status animation
    const statusItems = document.querySelectorAll('.status-item');
    statusItems.forEach(item => {
        item.addEventListener('mouseover', function() {
            this.querySelector('i').style.transform = 'scale(1.2)';
        });
        
        item.addEventListener('mouseout', function() {
            this.querySelector('i').style.transform = 'scale(1)';
        });
    });
    
    // Real-time updates simulation
    if (document.getElementById('systemLoadChart')) {
        setInterval(() => {
            const chart = Chart.getChart('systemLoadChart');
            if (chart) {
                const newData = chart.data.datasets[0].data.map(value => {
                    const change = Math.random() * 10 - 5;
                    return Math.max(10, Math.min(90, value + change));
                });
                chart.data.datasets[0].data = newData;
                chart.update();
            }
        }, 3000);
    }
});