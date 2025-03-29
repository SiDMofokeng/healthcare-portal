document.addEventListener('DOMContentLoaded', function() {
    // Load appointments when patient is selected
    document.querySelector('select[name="patient_id"]').addEventListener('change', function() {
        const patientId = this.value;
        const appointmentSelect = document.getElementById('appointmentSelect');
        
        if (!patientId) {
            appointmentSelect.innerHTML = '<option value="">None</option>';
            return;
        }
        
        fetch(`../api/get_patient_appointments.php?patient_id=${patientId}`)
            .then(response => response.json())
            .then(data => {
                let options = '<option value="">None</option>';
                data.forEach(appt => {
                    const date = new Date(appt.appointment_date);
                    const time = appt.appointment_time.substring(0, 5);
                    options += `<option value="${appt.id}">
                        ${date.toLocaleDateString()} at ${time} - ${appt.reason}
                    </option>`;
                });
                appointmentSelect.innerHTML = options;
            })
            .catch(error => {
                console.error('Error:', error);
                appointmentSelect.innerHTML = '<option value="">Error loading appointments</option>';
            });
    });

    // View prescription details
    document.querySelectorAll('.view-prescription').forEach(btn => {
        btn.addEventListener('click', function() {
            const prescriptionId = this.getAttribute('data-id');
            fetch(`../api/get_prescription.php?id=${prescriptionId}`)
                .then(response => response.json())
                .then(data => {
                    const modal = new bootstrap.Modal(document.getElementById('viewPrescriptionModal'));
                    const content = document.getElementById('prescriptionDetails');
                    
                    content.innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Patient Information</h6>
                                <p><strong>Name:</strong> ${data.patient_name}</p>
                                <p><strong>Date of Birth:</strong> ${data.dob || 'Not specified'}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Prescription Details</h6>
                                <p><strong>Date:</strong> ${new Date(data.prescription_date).toLocaleDateString()}</p>
                                <p><strong>Prescribing Doctor:</strong> Dr. ${data.doctor_name}</p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="prescription-content bg-light p-3 rounded">
                            <div class="text-center mb-4">
                                <h4 class="text-primary">PRESCRIPTION</h4>
                            </div>
                            
                            <div class="mb-3">
                                <h5><strong>${data.medication}</strong></h5>
                                <p><strong>Dosage:</strong> ${data.dosage}</p>
                                <p><strong>Instructions:</strong> ${data.instructions || 'None provided'}</p>
                                <p><strong>Refills:</strong> ${data.refills || '0'}</p>
                            </div>
                            
                            ${data.appointment_date ? `
                            <div class="mt-4">
                                <p class="text-muted"><small>
                                    <strong>Associated Appointment:</strong> 
                                    ${new Date(data.appointment_date).toLocaleDateString()} at 
                                    ${data.appointment_time.substring(0, 5)}
                                </small></p>
                            </div>
                            ` : ''}
                        </div>
                    `;
                    
                    modal.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load prescription details');
                });
        });
    });

    // Print prescription
    document.getElementById('printPrescriptionBtn').addEventListener('click', function() {
        const printContent = document.getElementById('prescriptionDetails').innerHTML;
        const originalContent = document.body.innerHTML;
        
        document.body.innerHTML = `
            <div class="container p-4">
                ${printContent}
                <div class="text-end mt-4">
                    <p>Prescribed by: _________________________</p>
                    <p>Date: ${new Date().toLocaleDateString()}</p>
                </div>
            </div>
        `;
        
        window.print();
        document.body.innerHTML = originalContent;
        window.location.reload();
    });

    // Form submission
    document.getElementById('prescriptionForm').addEventListener('submit', function(e) {
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
                    text: 'Prescription saved successfully',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', data.message || 'Failed to save prescription', 'error');
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