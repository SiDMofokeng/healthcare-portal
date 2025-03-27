<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$page_title = 'Patient Dashboard';
$custom_js = 'patient_dashboard.js';

// Get patient's upcoming appointments
$stmt = $pdo->prepare(
    "SELECT a.*, u.first_name, u.last_name, d.specialization
    FROM appointments a
    JOIN users u ON a.doctor_id = u.id
    JOIN doctors d ON a.doctor_id = d.user_id
    WHERE a.patient_id = ? AND a.appointment_date >= CURDATE()
    ORDER BY a.appointment_date, a.appointment_time LIMIT 3"
);
$stmt->execute([$_SESSION['user_id']]);
$upcoming_appointments = $stmt->fetchAll();

// Get recent prescriptions
$prescriptions = $pdo->prepare(
    "SELECT p.*, u.first_name, u.last_name 
    FROM prescriptions p
    JOIN users u ON p.doctor_id = u.id
    WHERE p.patient_id = ?
    ORDER BY p.prescription_date DESC LIMIT 2"
)->execute([$_SESSION['user_id']])->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-grid">
    <!-- Welcome Card -->
    <div class="card welcome-card">
        <div class="welcome-content">
            <h2>Hello, <?= htmlspecialchars($_SESSION['first_name']) ?>!</h2>
            <p>Welcome back to your health portal. Here's what's happening today.</p>
            <button class="btn btn-primary">Book Appointment</button>
        </div>
        <div class="welcome-image">
            <img src="../assets/images/patient-welcome.svg" alt="Healthcare">
        </div>
    </div>

    <!-- Health Summary Card -->
    <div class="card">
        <div class="card-header">
            <h3>Health Summary</h3>
        </div>
        <div class="card-body">
            <div class="health-metrics">
                <div class="metric-item">
                    <div class="metric-value">72</div>
                    <div class="metric-label">BPM</div>
                </div>
                <div class="metric-item">
                    <div class="metric-value">120/80</div>
                    <div class="metric-label">BP</div>
                </div>
                <div class="metric-item">
                    <div class="metric-value">98.6°F</div>
                    <div class="metric-label">Temp</div>
                </div>
            </div>
            <div class="health-timeline">
                <canvas id="healthChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Appointments Card -->
    <div class="card">
        <div class="card-header">
            <h3>Upcoming Appointments</h3>
            <div class="card-actions">
                <button class="btn btn-sm btn-outline">View All</button>
            </div>
        </div>
        <div class="card-body">
            <?php if (count($upcoming_appointments) > 0): ?>
                <div class="appointments-list compact">
                    <?php foreach ($upcoming_appointments as $appt): ?>
                    <div class="appointment-item">
                        <div class="appointment-time">
                            <span class="date"><?= date('M j', strtotime($appt['appointment_date'])) ?></span>
                            <span class="time"><?= date('g:i A', strtotime($appt['appointment_time'])) ?></span>
                        </div>
                        <div class="appointment-details">
                            <h4>Dr. <?= htmlspecialchars($appt['first_name'] . ' ' . $appt['last_name']) ?></h4>
                            <p class="specialization"><?= htmlspecialchars($appt['specialization']) ?></p>
                            <p class="reason"><?= htmlspecialchars($appt['reason']) ?></p>
                        </div>
                        <div class="appointment-actions">
                            <button class="btn-icon" title="Cancel"><i class="fas fa-times"></i></button>
                            <button class="btn-icon" title="Reschedule"><i class="fas fa-calendar-alt"></i></button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state small">
                    <i class="fas fa-calendar-plus"></i>
                    <p>No upcoming appointments</p>
                    <a href="book_appointment.php" class="btn btn-sm btn-primary">Book Now</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Prescriptions Card -->
    <div class="card">
        <div class="card-header">
            <h3>Recent Prescriptions</h3>
            <div class="card-actions">
                <button class="btn btn-sm btn-outline">View All</button>
            </div>
        </div>
        <div class="card-body">
            <?php if (count($prescriptions) > 0): ?>
                <div class="prescriptions-list">
                    <?php foreach ($prescriptions as $rx): ?>
                    <div class="prescription-item">
                        <div class="rx-header">
                            <h4><?= htmlspecialchars($rx['medication']) ?></h4>
                            <span class="rx-date"><?= date('M j, Y', strtotime($rx['prescription_date'])) ?></span>
                        </div>
                        <div class="rx-details">
                            <p><strong>Dosage:</strong> <?= htmlspecialchars($rx['dosage']) ?></p>
                            <p><strong>Instructions:</strong> <?= htmlspecialchars($rx['instructions']) ?></p>
                            <p><strong>Prescribed by:</strong> Dr. <?= htmlspecialchars($rx['first_name'] . ' ' . $rx['last_name']) ?></p>
                        </div>
                        <div class="rx-actions">
                            <button class="btn btn-sm btn-outline">Refill Request</button>
                            <button class="btn btn-sm">View Details</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state small">
                    <i class="fas fa-prescription-bottle-alt"></i>
                    <p>No recent prescriptions</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions Card -->
    <div class="card">
        <div class="card-header">
            <h3>Quick Actions</h3>
        </div>
        <div class="card-body">
            <div class="quick-actions-grid">
                <a href="book_appointment.php" class="quick-action">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Book Appointment</span>
                </a>
                <a href="medical_records.php" class="quick-action">
                    <i class="fas fa-file-medical"></i>
                    <span>View Records</span>
                </a>
                <a href="messages.php" class="quick-action">
                    <i class="fas fa-comment-medical"></i>
                    <span>Message Doctor</span>
                </a>
                <a href="billing.php" class="quick-action">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Pay Bill</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Health Tips Card -->
    <div class="card">
        <div class="card-header">
            <h3>Health Tips</h3>
            <div class="card-actions">
                <button class="btn btn-sm btn-outline">See More</button>
            </div>
        </div>
        <div class="card-body">
            <div class="health-tip">
                <h4>Stay Hydrated</h4>
                <p>Drink at least 8 glasses of water daily to maintain optimal body function.</p>
            </div>
            <div class="health-tip">
                <h4>Regular Exercise</h4>
                <p>Aim for 30 minutes of moderate activity most days of the week.</p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>