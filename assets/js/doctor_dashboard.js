document.addEventListener('DOMContentLoaded', function() {
    // Doctor-specific functionality
    const appointmentItems = document.querySelectorAll('.appointment-item');

    // New appointment button
    document.getElementById('new-appointment-btn').addEventListener('click', function() {
        const today = calendar.getDate();
        const dateStr = today.toISOString().split('T')[0];
        window.location.href = `new_appointment.php?date=${dateStr}`;
    });

    // Load appointment details into modal
    function loadAppointmentModal(appointmentId) {
        fetch(`../api/appointment_details.php?id=${appointmentId}`)
            .then(response => response.json())
            .then(data => {
                const modal = document.getElementById('appointment-modal');
                modal.querySelector('.modal-body').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Patient Information</h6>
                            <p><strong>Name:</strong> ${data.patient_name}</p>
                            <p><strong>Phone:</strong> ${data.patient_phone || 'N/A'}</p>
                            <p><strong>Email:</strong> ${data.patient_email || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Appointment Details</h6>
                            <p><strong>Date:</strong> ${data.appointment_date}</p>
                            <p><strong>Time:</strong> ${data.appointment_time}</p>
                            <p><strong>Status:</strong> <span class="badge badge-${getStatusClass(data.status)}">${data.status}</span></p>
                            <p><strong>Reason:</strong> ${data.reason || 'Not specified'}</p>
                        </div>
                    </div>
                    ${data.notes ? `<div class="mt-3"><h6>Notes</h6><p>${data.notes}</p></div>` : ''}
                `;
                
                // Initialize Bootstrap modal
                $('#appointment-modal').modal('show');
            })
            .catch(error => {
                console.error('Error loading appointment:', error);
                alert('Failed to load appointment details');
            });
    }

    // Update appointment time after drag-and-drop
    function updateAppointmentTime(event) {
        const start = event.start;
        const end = event.end || new Date(start.getTime() + 30 * 60000); // Default 30 min duration
        
        fetch('/api/update_appointment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: event.id,
                date: start.toISOString().split('T')[0],
                time: start.toTimeString().substring(0, 5),
                duration: (end - start) / 60000 // Duration in minutes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                calendar.refetchEvents();
                alert('Failed to update appointment: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error updating appointment:', error);
            calendar.refetchEvents();
            alert('Failed to update appointment');
        });
    }

    function getStatusClass(status) {
        const statusClasses = {
            'scheduled': 'primary',
            'completed': 'success',
            'cancelled': 'danger',
            'no_show': 'warning'
        };
        return statusClasses[status.toLowerCase()] || 'secondary';
    }

    // API Calls
    async function updateAppointment(id, data) {
    const response = await fetch(`/api/appointments.php?id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    return await response.json();
    }
    
    appointmentItems.forEach(item => {
        item.addEventListener('click', function(e) {
            if (!e.target.closest('.appointment-actions')) {
                alert('Appointment details would be shown here');
            }
        });
    });
    
    // Quick prescription form
    const quickPrescriptionForm = document.querySelector('.quick-form');
    if (quickPrescriptionForm) {
        quickPrescriptionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Prescription would be created and saved');
            this.reset();
        });
    }
    
    // Patient list interaction
    const patientItems = document.querySelectorAll('.patient-item');
    patientItems.forEach(item => {
        const moreBtn = item.querySelector('.btn-icon');
        moreBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            alert('Patient options menu would appear here');
        });
        
        item.addEventListener('click', function() {
            alert('Patient details would be shown here');
        });
    });

    // Highlight current page and parent menus
    document.querySelectorAll('.sidebar-nav a').forEach(link => {
        if (link.pathname === location.pathname) {
        link.classList.add('active');
        
        // Expand parent menu if exists
        const parentMenu = link.closest('.has-children');
        if (parentMenu) {
            parentMenu.classList.add('open');
            const collapseToggle = parentMenu.querySelector('.collapse-toggle');
            if (collapseToggle) {
            collapseToggle.setAttribute('aria-expanded', 'true');
            }
        }
        }
    });

    // Mobile menu toggle
    document.querySelector('.sidebar-toggle')?.addEventListener('click', () => {
        document.querySelector('.sidebar').classList.toggle('mobile-open');
    });

    // In doctor_dashboard.js
    function updateNotificationBadge() {
        fetch('/api/notifications.php')
        .then(res => res.json())
        .then(data => {
            document.querySelector('.notifications .badge').textContent = data.count || '0';
        });
    }
    
    // Update every 30 seconds
    updateNotificationBadge();
    setInterval(updateNotificationBadge, 30000);

    // Handle status changes
    document.querySelectorAll('.status-change').forEach(button => {
        button.addEventListener('click', function() {
            const appointmentId = this.closest('.appointment-item').dataset.appointmentId;
            const newStatus = this.dataset.status;
            
            fetch('/api/update_appointment_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: appointmentId,
                    status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI
                    const badge = this.closest('.appointment-status').querySelector('.status-badge');
                    badge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                    badge.className = `status-badge ${newStatus}`;
                    
                    // Show confirmation
                    showToast('Status updated successfully');
                }
            });
        });
    });

});