<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

// Fetch patients from database
$patients = [];
try {
    $stmt = $pdo->prepare("
        SELECT 
            p.user_id,
            u.first_name,
            u.last_name,
            u.email,
            p.date_of_birth,
            p.gender,
            p.blood_type,
            p.insurance_provider,
            COUNT(a.id) as appointment_count
        FROM patients p
        JOIN users u ON p.user_id = u.id
        LEFT JOIN appointments a ON p.user_id = a.patient_id
        GROUP BY p.user_id
        ORDER BY u.last_name, u.first_name
    ");
    $stmt->execute();
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log error
    error_log("Database error in patients page: " . $e->getMessage());
}

$page_title = 'Patients Management';
include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg">
                <div class="card-header p-3" style="background: linear-gradient(195deg, #49a3f1 0%, #1A73E8 100%);">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-2 mb-md-0">
                            <h5 class="mb-0 text-white font-weight-bold">
                                <i class="fas fa-procedures me-2 text-white"></i>
                                Patients Management
                            </h5>
                            <p class="text-sm text-white opacity-8 mb-0">View and manage all patients</p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-white btn-sm" data-bs-toggle="modal" data-bs-target="#newPatientModal">
                                <i class="fas fa-plus me-1"></i> New Patient
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="card-body px-0 pt-0">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Patient</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Details</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Medical Info</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Appointments</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($patients as $patient): ?>
<tr>
    <td>
        <div class="d-flex px-2 py-1">
            <div>
                <img src="../assets/images/profiles/<?= isset($patient['user_id']) ? $patient['user_id'] : 'default' ?>.jpg" 
                     class="avatar avatar-sm me-3" 
                     alt="<?= htmlspecialchars(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')) ?>"
                     onerror="this.onerror=null;this.src='../assets/images/default-avatar.jpg'">
            </div>
            <div class="d-flex flex-column justify-content-center">
                <h6 class="mb-0 text-sm"><?= htmlspecialchars(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')) ?></h6>
                <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($patient['email'] ?? '') ?></p>
            </div>
        </div>
    </td>
    <td>
        <p class="text-xs font-weight-bold mb-0">
            DOB: <?= !empty($patient['date_of_birth']) ? date('M j, Y', strtotime($patient['date_of_birth'])) : 'N/A' ?>
        </p>
        <p class="text-xs text-secondary mb-0">
            Gender: <?= !empty($patient['gender']) ? ucfirst($patient['gender']) : 'N/A' ?>
        </p>
    </td>
    <td>
        <p class="text-xs font-weight-bold mb-0">
            Blood Type: <?= !empty($patient['blood_type']) ? $patient['blood_type'] : 'N/A' ?>
        </p>
        <p class="text-xs text-secondary mb-0">
            Insurance: <?= !empty($patient['insurance_provider']) ? $patient['insurance_provider'] : 'N/A' ?>
        </p>
    </td>
    <td>
        <span class="badge bg-gradient-primary"><?= $patient['appointment_count'] ?? 0 ?> visits</span>
    </td>
    <td class="align-middle text-end">
        <div class="d-flex justify-content-end gap-1">
            <button class="btn btn-sm btn-outline-primary mb-0 view-patient" 
                    data-id="<?= $patient['user_id'] ?? '' ?>">
                <i class="fas fa-eye"></i>
            </button>
            <button class="btn btn-sm btn-outline-info mb-0 edit-patient" 
                    data-id="<?= $patient['user_id'] ?? '' ?>">
                <i class="fas fa-edit"></i>
            </button>
        </div>
    </td>
</tr>
<?php endforeach; ?>
                                
                                <?php if (empty($patients)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="fas fa-user-slash fa-2x text-gray-300 mb-3"></i>
                                        <p class="text-muted">No patients found</p>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Patient Modal (placeholder) -->
<div class="modal fade" id="viewPatientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Patient Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="patientDetails">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View patient details
    document.querySelectorAll('.view-patient').forEach(btn => {
        btn.addEventListener('click', function() {
            const patientId = this.getAttribute('data-id');
            fetch(`/api/get_patient.php?id=${patientId}`)
                .then(response => response.json())
                .then(data => {
                    // Populate modal with patient data
                    const modal = new bootstrap.Modal(document.getElementById('viewPatientModal'));
                    document.getElementById('patientDetails').innerHTML = `
                        <div class="row">
                            <div class="col-md-4 text-center mb-3">
                                <img src="../assets/images/profiles/${data.user_id}.jpg" 
                                     class="avatar avatar-xxl rounded-circle mb-2"
                                     onerror="this.onerror=null;this.src='../assets/images/default-avatar.jpg'">
                                <h4>${data.first_name} ${data.last_name}</h4>
                                <p class="text-muted">${data.email}</p>
                            </div>
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Patient Information</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Date of Birth:</strong> ${data.date_of_birth ? new Date(data.date_of_birth).toLocaleDateString() : 'N/A'}</p>
                                                <p><strong>Gender:</strong> ${data.gender ? data.gender.charAt(0).toUpperCase() + data.gender.slice(1) : 'N/A'}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Blood Type:</strong> ${data.blood_type || 'N/A'}</p>
                                                <p><strong>Insurance:</strong> ${data.insurance_provider || 'N/A'}</p>
                                            </div>
                                        </div>
                                        <hr>
                                        <h5 class="card-title">Medical History</h5>
                                        <p>${data.medical_history || 'No medical history recorded.'}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    modal.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load patient details');
                });
        });
    });

    // Edit patient (placeholder)
    document.querySelectorAll('.edit-patient').forEach(btn => {
        btn.addEventListener('click', function() {
            const patientId = this.getAttribute('data-id');
            alert('Edit patient with ID: ' + patientId);
            // Implement edit functionality as needed
        });
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>