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

<div class="container-fluid py-4">
    
<div class="row">
    
    <!-- Today's Appointments -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-lg" style="background: linear-gradient(195deg, #4CAF50 0%, #2E7D32 100%);">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8 text-white">
                        <p class="text-sm mb-1 text-uppercase font-weight-bold opacity-8">Today's Appointments</p>
                        <h3 class="font-weight-bolder mb-0"><?= $today_appointments ?></h3>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-white shadow-white text-center">
                            <i class="fas fa-calendar-day text-success opacity-10"></i>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="schedule.php?view=today" class="text-white text-sm font-weight-bold">
                        View All <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Patients -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-lg" style="background: linear-gradient(195deg, #2196F3 0%, #0D47A1 100%);">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8 text-white">
                        <p class="text-sm mb-1 text-uppercase font-weight-bold opacity-8">Total Patients</p>
                        <h3 class="font-weight-bolder mb-0"><?= $patient_count ?></h3>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-white shadow-white text-center">
                            <i class="fas fa-procedures text-primary opacity-10"></i>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="patients.php" class="text-white text-sm font-weight-bold">
                        My Patients <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Prescriptions -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-lg" style="background: linear-gradient(195deg, #FF9800 0%, #E65100 100%);">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8 text-white">
                        <p class="text-sm mb-1 text-uppercase font-weight-bold opacity-8">Pending Prescriptions</p>
                        <h3 class="font-weight-bolder mb-0">12</h3>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-white shadow-white text-center">
                            <i class="fas fa-clock text-warning opacity-10"></i>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="prescriptions.php" class="text-white text-sm font-weight-bold">
                        Review <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Slots -->
    <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(195deg, #9C27B0 0%, #6A1B9A 100%);">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8 text-white">
                            <p class="text-sm mb-1 text-uppercase font-weight-bold opacity-8">Available Slots</p>
                            <h3 class="font-weight-bolder mb-0">5</h3>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-white shadow-white text-center">
                                <i class="fas fa-calendar-plus text-purple opacity-10"></i>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="schedule.php" class="text-white text-sm font-weight-bold">
                            Book Now <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Cards -->
    <div class="row mt-4">
        <div class="col-lg-8 mb-4">
            <div class="card card-plain border-0 shadow-lg mb-4">

                <!-- Enhanced Card Header -->
                <div class="card-header p-3" style="background: linear-gradient(195deg, #f8f9fa 0%, #e9ecef 100%); border-bottom: none;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 text-dark font-weight-bold">
                                <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                Upcoming Appointments
                            </h5>
                            <p class="text-sm text-muted mb-0">Next 5 scheduled visits</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary px-3">
                                <i class="fas fa-print me-1"></i> Print
                            </button>
                            <button class="btn btn-sm btn-primary px-3">
                                <i class="fas fa-list me-1"></i> View All
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body px-0 pt-0">
                    <?php if (count($upcoming_appointments) > 0): ?>
                        
                        <div class="list-group list-group-flush">
                        <?php foreach ($upcoming_appointments as $appt): ?>
                        <div class="list-group-item border-0 px-0 py-3">
                            <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm rounded-circle bg-gradient-primary me-3">
                                <span class="text-white"><?= strtoupper(substr($appt['first_name'], 0, 1)) ?></span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0"><?= htmlspecialchars($appt['first_name'].' '.$appt['last_name']) ?></h6>
                                <p class="text-sm text-muted mb-0"><?= htmlspecialchars($appt['reason']) ?></p>
                            </div>
                            <div class="text-end" style="margin-right: 15px;">
                                <span class="badge bg-<?= $appt['status'] === 'confirmed' ? 'success' : 'warning' ?>">
                                <?= ucfirst($appt['status']) ?>
                                </span>
                                <p class="text-sm mb-0 text-muted"><?= date('g:i A', strtotime($appt['appointment_time'])) ?></p>
                                <p class="text-xs mb-0 text-muted"><?= date('M j, Y', strtotime($appt['appointment_date'])) ?></p>
                            </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        </div>

                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-gray-300 mb-3"></i>
                            <h6 class="text-muted">No upcoming appointments</h6>
                            <a href="schedule.php" class="btn btn-sm btn-primary mt-2">Check Availability</a>
                        </div>
                    <?php endif; ?>
                </div>
                
            </div>
        </div>

        <div class="col-lg-4 mb-4">
                <div class="card shadow-lg">
                    
                    <!-- Enhanced Calendar Header -->
                    <div class="card-header p-3" style="background: linear-gradient(195deg, #f8f9fa 0%, #e9ecef 100%);">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0 text-dark font-weight-bold">
                                    <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                    Appointment Calendar
                                </h5>
                                <p class="text-sm text-muted mb-0">View and manage your schedule</p>
                            </div>
                            <button id="new-appointment-btn" class="btn btn-sm btn-primary px-3">
                                <i class="fas fa-plus me-1"></i> New Appointment
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-3">
                        <div id="calendar" style="min-height: 500px;"></div>
                    </div>

                </div>
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
                        right: 'dayGridMonth,timeGridWeek,listWeek'
                    },
                    themeSystem: 'bootstrap5',
                    events: {
                        url: '../api/doctor_appointments.php',
                        method: 'GET',
                        failure: function() {
                            alert('There was an error while fetching appointments!');
                        }
                    },
                    eventClick: function(info) {
                        // Custom modal implementation here
                        showAppointmentModal(info.event);
                    },
                    eventClassNames: 'fc-event-material',
                    dayHeaderClassNames: 'fc-day-header-material',
                    dayCellClassNames: 'fc-daygrid-day-material',
                    initialDate: new Date(),
                    navLinks: true,
                    editable: true,
                    selectable: true,
                    selectMirror: true,
                    dayMaxEvents: true,
                    eventBackgroundColor: '#4CAF50',
                    eventBorderColor: '#4CAF50',
                    eventTextColor: '#ffffff',
                    eventTimeFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    }
                });
                calendar.render();
            });

            function showAppointmentModal(event) {
                // Your custom modal implementation
                console.log('Appointment:', event.title);
            }
        </script>
    </div>

</div>


<?php include __DIR__ . '/../includes/footer.php'; ?>