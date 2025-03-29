<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$page_title = 'Prescription Management';
$custom_js = 'prescriptions.js';

try {
    // Get all prescriptions for this doctor
    $prescriptions_stmt = $pdo->prepare(
        "SELECT p.*, 
         u.first_name, u.last_name, 
         a.appointment_date, a.appointment_time,
         d.specialization
         FROM prescriptions p
         JOIN users u ON p.patient_id = u.id
         LEFT JOIN appointments a ON p.appointment_id = a.id
         JOIN doctors d ON p.doctor_id = d.user_id
         WHERE p.doctor_id = ?
         ORDER BY p.prescription_date DESC, u.last_name"
    );
    $prescriptions_stmt->execute([$_SESSION['user_id']]);
    $prescriptions = $prescriptions_stmt->fetchAll();

    // Get patients for dropdown
    $patients_stmt = $pdo->prepare(
        "SELECT u.id, u.first_name, u.last_name 
         FROM users u
         JOIN patients p ON u.id = p.user_id
         WHERE u.role = 'patient'
         ORDER BY u.last_name"
    );
    $patients_stmt->execute();
    $patients = $patients_stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Database error in prescriptions: " . $e->getMessage());
    $prescriptions = [];
    $patients = [];
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                <!-- Card Header -->
                <div class="card-header p-3" style="background: linear-gradient(195deg, #f8f9fa 0%, #e9ecef 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 text-dark font-weight-bold">
                                <i class="fas fa-prescription me-2 text-primary"></i>
                                Prescription Management
                            </h5>
                            <p class="text-sm text-muted mb-0">Manage and review patient prescriptions</p>
                        </div>
                        <button class="btn btn-sm btn-primary px-3" data-bs-toggle="modal" data-bs-target="#newPrescriptionModal">
                            <i class="fas fa-plus me-1"></i> New Prescription
                        </button>
                    </div>
                </div>

                <!-- Prescriptions Table -->
                <div class="card-body px-0 pt-0">
                    <?php if (count($prescriptions) > 0): ?>
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Patient</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Medication</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Dosage</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Linked Appointment</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($prescriptions as $rx): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="avatar avatar-sm me-3 bg-gradient-primary">
                                                    <span class="text-white"><?= strtoupper(substr($rx['first_name'], 0, 1)) ?></span>
                                                </div>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm"><?= htmlspecialchars($rx['first_name'].' '.$rx['last_name']) ?></h6>
                                                    <p class="text-xs text-secondary mb-0"><?= $rx['specialization'] ?? 'General' ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-sm font-weight-bold mb-0"><?= htmlspecialchars($rx['medication']) ?></p>
                                        </td>
                                        <td>
                                            <p class="text-sm mb-0"><?= htmlspecialchars($rx['dosage']) ?></p>
                                        </td>
                                        <td>
                                            <p class="text-sm mb-0"><?= date('M j, Y', strtotime($rx['prescription_date'])) ?></p>
                                        </td>
                                        <td>
                                            <?php if ($rx['appointment_date']): ?>
                                                <p class="text-sm mb-0">
                                                    <?= date('M j, Y', strtotime($rx['appointment_date'])) ?>
                                                    <span class="text-xs text-muted"><?= date('g:i a', strtotime($rx['appointment_time'])) ?></span>
                                                </p>
                                            <?php else: ?>
                                                <span class="badge badge-sm bg-secondary">No appointment</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-primary px-3 view-prescription" 
                                                    data-id="<?= $rx['id'] ?>">
                                                <i class="fas fa-eye me-1"></i> View
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning px-3 edit-prescription" 
                                                    data-id="<?= $rx['id'] ?>">
                                                <i class="fas fa-edit me-1"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-prescription-bottle-alt fa-3x text-gray-300 mb-3"></i>
                            <h6 class="text-muted">No prescriptions found</h6>
                            <button class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#newPrescriptionModal">
                                <i class="fas fa-plus me-1"></i> Create First Prescription
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Prescription Modal -->
<div class="modal fade" id="newPrescriptionModal" tabindex="-1" aria-labelledby="newPrescriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title" id="newPrescriptionModalLabel">
                    <i class="fas fa-prescription me-2"></i> New Prescription
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="prescriptionForm" action="../api/create_prescription.php" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <!-- Patient Selection -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" class="form-select" required>
                                <option value="">Select Patient</option>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?= $patient['id'] ?>">
                                        <?= htmlspecialchars($patient['last_name'].', '.$patient['first_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Linked Appointment (optional) -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Linked Appointment</label>
                            <select name="appointment_id" class="form-select" id="appointmentSelect">
                                <option value="">None</option>
                                <!-- Will be populated via AJAX -->
                            </select>
                        </div>

                        <!-- Medication -->
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Medication <span class="text-danger">*</span></label>
                            <input type="text" name="medication" class="form-control" required>
                        </div>

                        <!-- Dosage -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Dosage <span class="text-danger">*</span></label>
                            <input type="text" name="dosage" class="form-control" placeholder="e.g. 10mg daily" required>
                        </div>

                        <!-- Instructions -->
                        <div class="col-12 mb-3">
                            <label class="form-label">Instructions</label>
                            <textarea name="instructions" class="form-control" rows="3" 
                                      placeholder="Take with food, avoid alcohol, etc."></textarea>
                        </div>

                        <!-- Prescription Date -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prescription Date <span class="text-danger">*</span></label>
                            <input type="date" name="prescription_date" class="form-control" 
                                   value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                        </div>

                        <!-- Refills -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Refills</label>
                            <select name="refills" class="form-select">
                                <option value="0">No refills</option>
                                <option value="1">1 refill</option>
                                <option value="2">2 refills</option>
                                <option value="3">3 refills</option>
                                <option value="4">4 refills</option>
                                <option value="5">5 refills</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Prescription
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Prescription Modal -->
<div class="modal fade" id="viewPrescriptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-prescription me-2"></i> Prescription Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="prescriptionDetails">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
                <button type="button" class="btn btn-success" id="printPrescriptionBtn">
                    <i class="fas fa-print me-1"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>