<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$page_title = 'Doctor Dashboard';
$custom_js = 'doctor_dashboard.js';

try {
    // Get doctor's upcoming appointments
    $stmt = $pdo->prepare(
        "SELECT a.*, u.first_name, u.last_name 
        FROM appointments a
        JOIN users u ON a.patient_id = u.id
        WHERE a.doctor_id = ? AND a.appointment_date >= CURDATE()
        ORDER BY a.appointment_date, a.appointment_time LIMIT 5"
    );
    $stmt->execute([$_SESSION['user_id']]);
    $upcoming_appointments = $stmt->fetchAll();

    // Get today's appointments
    $today_stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM appointments 
        WHERE doctor_id = ? AND appointment_date = CURDATE()"
    );
    $today_stmt->execute([$_SESSION['user_id']]);
    $today_appointments = $today_stmt->fetchColumn();

    // Get patient count
    $patient_stmt = $pdo->prepare(
        "SELECT COUNT(DISTINCT patient_id) FROM appointments 
        WHERE doctor_id = ?"
    );
    $patient_stmt->execute([$_SESSION['user_id']]);
    $patient_count = $patient_stmt->fetchColumn();

} catch (PDOException $e) {
    // Log error and set default values
    error_log("Database error in doctor dashboard: " . $e->getMessage());
    $upcoming_appointments = [];
    $today_appointments = 0;
    $patient_count = 0;
}

include __DIR__ . '/../includes/header.php';

// In your PHP code (before HTML):
// Add status filtering and sorting
$stmt = $pdo->prepare(
    "SELECT a.*, 
     u.first_name, u.last_name, u.email, 
     COALESCE(u.phone, '') as phone
     FROM appointments a
     JOIN users u ON a.patient_id = u.id
     WHERE a.doctor_id = ? AND a.appointment_date >= CURDATE()
     ORDER BY a.appointment_date, a.appointment_time LIMIT 5"
);

function checkAppointmentConflict($pdo, $doctorId, $date, $startTime, $endTime, $excludeId = null) {
    $sql = "SELECT id FROM appointments 
            WHERE doctor_id = ? 
            AND appointment_date = ? 
            AND (
                (appointment_time BETWEEN ? AND ?)
                OR (ADDTIME(appointment_time, '00:30:00') BETWEEN ? AND ?)
            )
            AND status != 'cancelled'";
    
    $params = [$doctorId, $date, $startTime, $endTime, $startTime, $endTime];
    
    if ($excludeId) {
        $sql .= " AND id != ?";
        $params[] = $excludeId;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetch() !== false;
}

function formatPhoneLink($phone) {
    if (empty($phone)) {
        return ''; // Returns empty string if no phone number
    }
    return 'tel:' . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
}

?>

<!-- Add FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
<!-- Add FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>

<div class="dashboard-grid">
    
    <!-- Stat Cards -->
    <div class="stat-card">
        <div class="stat-icon bg-primary">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div class="stat-info">
            <h3>Today's Appointments</h3>
            <p><?= $today_appointments ?></p>
        </div>
        <a href="schedule.php?view=today" class="stat-link">View <i class="fas fa-arrow-right"></i></a>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-success">
            <i class="fas fa-procedures"></i>
        </div>
        <div class="stat-info">
            <h3>Total Patients</h3>
            <p><?= $patient_count ?></p>
        </div>
        <a href="patients.php" class="stat-link">My Patients <i class="fas fa-arrow-right"></i></a>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-warning">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-info">
            <h3>Pending Prescriptions</h3>
            <p>12</p>
        </div>
        <a href="prescriptions.php" class="stat-link">Review <i class="fas fa-arrow-right"></i></a>
    </div>

    <!-- Main Content Cards -->
    <div class="card full-width">
        <div class="card-header">
            <h3>Upcoming Appointments</h3>
            <div class="card-actions">
                <button class="btn btn-sm btn-primary">View All</button>
                <button class="btn btn-sm btn-outline">Print Schedule</button>
            </div>
        </div>
        <div class="card-body collapse">
            <?php if (count($upcoming_appointments) > 0): ?>
                <div class="appointments-list">
                    <?php foreach ($upcoming_appointments as $appt): ?>
                    <div class="appointment-item">
                        <div class="appointment-time">
                            <span class="time"><?= date('g:i A', strtotime($appt['appointment_time'])) ?></span>
                            <span class="date"><?= date('D, M j', strtotime($appt['appointment_date'])) ?></span>
                        </div>
                        <div class="appointment-details">
                            <h4><?= htmlspecialchars($appt['first_name'] . ' ' . $appt['last_name']) ?></h4>
                            <p class="reason"><?= htmlspecialchars($appt['reason'] ?? 'No reason provided', ENT_QUOTES, 'UTF-8') ?></p>
                            <div class="appointment-actions">
                                <button class="btn btn-sm btn-outline">Start Consultation</button>
                                <button class="btn btn-sm">View History</button>
                            </div>
                        </div>
                        <div class="appointment-status">
                            <span class="status-badge <?= $appt['status'] ?>"><?= ucfirst($appt['status']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>No upcoming appointments</p>
                    <a href="schedule.php" class="btn btn-primary">Check Availability</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Calendar Card -->
    <div class="card full-width">
        <div class="card-header">
            <h3><i class="fas fa-calendar-alt"></i> Appointment Calendar</h3>
            <button id="new-appointment-btn" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Appointment
            </button>
        </div>
        <div class="card-body">
            <!-- This is our calendar container -->
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Initialize FullCalendar -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: {
            url: '../api/doctor_appointments.php',
            method: 'GET',
            failure: function() {
                alert('There was an error while fetching appointments!');
            }
            },
            eventClick: function(info) {
            // Optionally load appointment details via AJAX into a modal
            alert('Appointment ID: ' + info.event.id + '\n' + info.event.title);
            }
        });
        calendar.render();
        });
    </script>


</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>