<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$page_title = 'Admin Dashboard';
$custom_js = 'admin_dashboard.js';

// Get statistics
$users_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$doctors_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'doctor'")->fetchColumn();
$patients_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'patient'")->fetchColumn();
$appointments_count = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'scheduled'")->fetchColumn();

// Get recent appointments
$recent_appointments = $pdo->query(
    "SELECT a.*, 
    p.first_name as p_first, p.last_name as p_last,
    d.first_name as d_first, d.last_name as d_last
    FROM appointments a
    JOIN users p ON a.patient_id = p.id
    JOIN users d ON a.doctor_id = d.id
    ORDER BY a.appointment_date DESC LIMIT 5"
)->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-grid">
    <!-- Stat Cards -->
    <div class="stat-card">
        <div class="stat-icon bg-primary">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3>Total Users</h3>
            <p><?= $users_count ?></p>
        </div>
        <a href="users.php" class="stat-link">View All <i class="fas fa-arrow-right"></i></a>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-success">
            <i class="fas fa-user-md"></i>
        </div>
        <div class="stat-info">
            <h3>Doctors</h3>
            <p><?= $doctors_count ?></p>
        </div>
        <a href="doctors.php" class="stat-link">Manage <i class="fas fa-arrow-right"></i></a>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-warning">
            <i class="fas fa-procedures"></i>
        </div>
        <div class="stat-info">
            <h3>Patients</h3>
            <p><?= $patients_count ?></p>
        </div>
        <a href="patients.php" class="stat-link">View All <i class="fas fa-arrow-right"></i></a>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-danger">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-info">
            <h3>Appointments</h3>
            <p><?= $appointments_count ?></p>
        </div>
        <a href="appointments.php" class="stat-link">Schedule <i class="fas fa-arrow-right"></i></a>
    </div>

    <!-- Main Content Cards -->
    <div class="card full-width">
        <div class="card-header">
            <h3>Recent Appointments</h3>
            <div class="card-actions">
                <button class="btn btn-sm btn-primary">View All</button>
            </div>
        </div>
        <div class="card-body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_appointments as $appt): ?>
                    <tr>
                        <td><?= htmlspecialchars($appt['p_first'] . ' ' . $appt['p_last']) ?></td>
                        <td>Dr. <?= htmlspecialchars($appt['d_first'] . ' ' . $appt['d_last']) ?></td>
                        <td><?= date('M j, Y', strtotime($appt['appointment_date'])) ?></td>
                        <td><?= date('g:i A', strtotime($appt['appointment_time'])) ?></td>
                        <td><span class="status-badge <?= $appt['status'] ?>"><?= ucfirst($appt['status']) ?></span></td>
                        <td>
                            <button class="btn-icon" title="View"><i class="fas fa-eye"></i></button>
                            <button class="btn-icon" title="Edit"><i class="fas fa-edit"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>System Status</h3>
        </div>
        <div class="card-body">
            <div class="system-status">
                <div class="status-item online">
                    <i class="fas fa-server"></i>
                    <span>Database</span>
                </div>
                <div class="status-item online">
                    <i class="fas fa-network-wired"></i>
                    <span>API</span>
                </div>
                <div class="status-item online">
                    <i class="fas fa-shield-alt"></i>
                    <span>Security</span>
                </div>
            </div>
            <canvas id="systemLoadChart"></canvas>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Quick Actions</h3>
        </div>
        <div class="card-body">
            <div class="quick-actions-grid">
                <button class="quick-action">
                    <i class="fas fa-user-plus"></i>
                    <span>Add User</span>
                </button>
                <button class="quick-action">
                    <i class="fas fa-calendar-plus"></i>
                    <span>New Appointment</span>
                </button>
                <button class="quick-action">
                    <i class="fas fa-file-export"></i>
                    <span>Generate Report</span>
                </button>
                <button class="quick-action">
                    <i class="fas fa-bell"></i>
                    <span>Send Notification</span>
                </button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>