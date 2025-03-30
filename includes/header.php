<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Healthcare Portal' ?></title>

    <link href="../assets/css/material-dashboard.min.css" rel="stylesheet" />
    <link href="../assets/css/custom-dashboard.css" rel="stylesheet" /> <!-- Your overrides -->

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <!-- Bootstrap Modal (if not already loaded) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="app-container">
        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title"><?= $page_title ?? 'Dashboard' ?></h1>
            </div>
            <div class="topbar-right">
                <div class="search-box">
                    <input type="text" placeholder="Search...">
                    <i class="fas fa-search"></i>
                </div>

                <div class="user-profile">
                        <!-- Dropdown Menu -->
                    <div class="dropdown">
                        <!-- Trigger Button -->
                        <button class="btn btn-link text-dark dropdown-toggle d-flex align-items-center p-0" 
                                type="button" 
                                id="profileDropdown" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false"
                                style="background: none; border: none;"
                                onclick="event.stopPropagation()">
                                
                            <div class="avatar avatar-sm me-2 bg-gradient-<?php echo ($_SESSION['role'] === 'doctor') ? 'primary' : 'secondary'; ?>">
                                <?php if (!empty($_SESSION['user_id'])) : ?>
                                    <img src="../assets/images/profiles/<?php echo (int)$_SESSION['user_id']; ?>.jpg" 
                                        alt="<?php echo htmlspecialchars($_SESSION['first_name'] ?? 'User'); ?>" 
                                        class="rounded-circle"
                                        onerror="this.onerror=null;this.src='../assets/images/default-avatar.jpg'">
                                <?php else : ?>
                                    <span class="text-white"><?php echo strtoupper(substr($_SESSION['first_name'] ?? 'U', 0, 1)); ?></span>
                                <?php endif; ?>
                            </div>
                            
                        </button>

                        <!-- Dropdown Menu -->
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2" 
                            aria-labelledby="profileDropdown"
                            style="min-width: 220px; border-radius: 12px;"
                            onclick="event.stopPropagation()">
                            
                            <li>
                                <div class="d-flex align-items-center px-3 py-2">
                                    <div class="avatar avatar-sm me-3 bg-gradient-<?php echo ($_SESSION['role'] === 'doctor') ? 'primary' : 'secondary'; ?>">
                                        <?php if (!empty($_SESSION['user_id'])) : ?>
                                            <img src="../assets/images/profiles/<?php echo (int)$_SESSION['user_id']; ?>.jpg" 
                                                class="rounded-circle"
                                                onerror="this.onerror=null;this.src='../assets/images/default-avatar.jpg'">
                                        <?php else : ?>
                                            <span class="text-white"><?php echo strtoupper(substr($_SESSION['first_name'] ?? 'U', 0, 1)); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-0"><?php echo htmlspecialchars(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')); ?></h6>
                                        <small class="text-muted">
                                            <?php if ($_SESSION['role'] === 'doctor') : ?>
                                                <i class="fas fa-user-md me-1"></i> Doctor
                                            <?php else : ?>
                                                <i class="fas fa-user me-1"></i> <?php echo ucfirst($_SESSION['role']); ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                            </li>
                            
                            <li><hr class="dropdown-divider my-2 mx-3"></li>
                            
                            <li>
                                <a class="dropdown-item d-flex align-items-center px-3 py-2" href="/profile" onclick="event.stopPropagation()">
                                    <i class="fas fa-user-cog me-2 text-muted"></i> My Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center px-3 py-2" href="/settings" onclick="event.stopPropagation()">
                                    <i class="fas fa-cog me-2 text-muted"></i> Settings
                                </a>
                            </li>
                            
                            <li><hr class="dropdown-divider my-2 mx-3"></li>
                            
                            <li>
                                <a class="dropdown-item d-flex align-items-center px-3 py-2 text-danger" href="/logout" onclick="event.stopPropagation()">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </a>
                            </li>
                        </ul>

                    </div>
                    
                </div>

            </div>
        </header>

        <!-- Sidebar -->
        <!-- Enhanced Sidebar with Material Dashboard 2 Styling -->
<aside class="sidebar bg-gradient-dark">

    <nav class="sidebar-nav px-3 mt-2">

    <div class="sidebar-header p-4 pb-2 position-relative">
        <!-- Gradient overlay -->
        <div class="position-absolute top-0 end-0 start-0 h-100 bg-gradient-primary opacity-6 rounded-bottom-3"></div>
        
        <h2 class="d-flex align-items-center gap-2 text-white position-relative">
            <i class="fas fa-heartbeat"></i> 
            <span>HealthPro</span>
        </h2>
        <p class="text-white text-sm position-relative opacity-8 mb-0">
            <?= ucfirst($_SESSION['role']) ?> Portal
        </p>
    </div>

        <?php if ($_SESSION['role'] === 'admin'): ?>
            <!-- Admin Menu -->
            <ul class="nav flex-column">
                <?php
                $current_page = basename($_SERVER['SCRIPT_NAME']);
                $admin_items = [
                    'dashboard.php' => ['icon' => 'tachometer-alt', 'label' => 'Dashboard'],
                    'users.php' => ['icon' => 'users', 'label' => 'Manage Users'],
                    // Add more admin items here
                ];
                
                foreach ($admin_items as $page => $item): 
                    $is_active = ($current_page === $page) ? 'active' : '';
                ?>
                <li class="nav-item mb-1">
                    <a class="nav-link d-flex align-items-center text-white py-3 px-3 rounded-3 <?= $is_active ?>" 
                       href="/healthcare_portal/admin/<?= $page ?>">
                        <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fas fa-<?= $item['icon'] ?> text-dark text-sm opacity-8"></i>
                        </div>
                        <span class="font-weight-bold"><?= $item['label'] ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            
        <?php elseif ($_SESSION['role'] === 'doctor'): ?>
            <!-- Doctor Menu -->
            <ul class="nav flex-column">
                <?php
                $current_page = basename($_SERVER['SCRIPT_NAME']);
                $menu_items = [
                    'dashboard.php' => ['icon' => 'tachometer-alt', 'label' => 'Dashboard'],
                    'appointments.php' => ['icon' => 'calendar-check', 'label' => 'Appointments'],
                    'patients.php' => ['icon' => 'procedures', 'label' => 'Patients'],
                    'prescriptions.php' => ['icon' => 'prescription', 'label' => 'Prescriptions'],
                    'medical_claims.php' => ['icon' => 'file-medical', 'label' => 'Medical Claims'],
                    'settings.php' => ['icon' => 'user-cog', 'label' => 'Profile Settings']
                ];
                
                foreach ($menu_items as $page => $item): 
                    $is_active = ($current_page === $page) ? 'active' : '';
                ?>
                <li class="nav-item mb-1">
                    <a class="nav-link d-flex align-items-center text-white py-3 px-3 rounded-3 <?= $is_active ?>" 
                       href="/healthcare_portal/doctor/<?= $page ?>">
                        <div class="icon icon-shape icon-sm shadow border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fas fa-<?= $item['icon'] ?> text-white text-sm opacity-8"></i>
                        </div>
                        <span class="font-weight-bold"><?= $item['label'] ?></span>
                        <?php if ($page === 'appointments'): ?>
                            <span class="badge bg-danger ms-auto">3</span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            
        <?php else: ?>
            <!-- Patient Menu -->
        <?php endif; ?>
    </nav>
    
    <div class="sidebar-footer px-3 py-4 mt-auto">
        <a href="../logout.php" 
           class="nav-item d-flex align-items-center text-danger py-2 px-3 rounded-3">
            <div class="icon icon-shape icon-sm shadow border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                <i class="fas fa-sign-out-alt text-danger text-sm"></i>
            </div>
            <span class="font-weight-bold">Logout</span>
        </a>
    </div>
</aside>

<!-- New Appointment Modal -->
<div class="modal fade" id="newAppointmentModal" tabindex="-1" aria-labelledby="newAppointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title" id="newAppointmentModalLabel">
                    <i class="fas fa-calendar-plus me-2"></i> New Appointment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="appointmentForm" action="../api/create_appointment.php" method="POST" onsubmit="return false;">
                <div class="modal-body">
                    <div class="row">
                        <!-- Patient Selection -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" class="form-select" required>
                                <option value="">Select Patient</option>
                                <?php
                                $patients = $pdo->query("
                                    SELECT u.id, u.first_name, u.last_name 
                                    FROM users u
                                    JOIN patients p ON u.id = p.user_id
                                    WHERE u.role = 'patient'
                                    ORDER BY u.last_name, u.first_name
                                ")->fetchAll();
                                
                                foreach ($patients as $patient) {
                                    echo '<option value="'.$patient['id'].'">'
                                        .htmlspecialchars($patient['last_name'].', '.$patient['first_name'])
                                        .'</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Date -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="appointment_date" class="form-control" 
                                   min="<?= date('Y-m-d') ?>" required>
                        </div>

                        <!-- Time -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Time <span class="text-danger">*</span></label>
                            <input type="time" name="appointment_time" class="form-control" 
                                   min="08:00" max="18:00" step="900" required>
                        </div>

                        <!-- Duration -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Duration <span class="text-danger">*</span></label>
                            <select name="duration" class="form-select" required>
                                <option value="15">15 minutes</option>
                                <option value="30" selected>30 minutes</option>
                                <option value="45">45 minutes</option>
                                <option value="60">60 minutes</option>
                            </select>
                        </div>

                        <!-- Reason -->
                        <div class="col-12 mb-3">
                            <label class="form-label">Reason <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Schedule Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

        <!-- Main Content -->
        <main class="main-content">

<script>
// Isolate profile dropdown from other click handlers
document.querySelector('.profile-dropdown').addEventListener('click', function(e) {
    e.stopImmediatePropagation();
});

// Alternative: Add this to doctor_dashboard.js at the very top
document.addEventListener('DOMContentLoaded', function() {
    // Prevent other handlers from affecting dropdown
    const profileDropdown = document.getElementById('profileDropdown');
    if (profileDropdown) {
        profileDropdown.addEventListener('click', function(e) {
            e.stopImmediatePropagation();
        }, true); // Capture phase
    }
});

$(document).ready(function() {
    // Initialize form with default times
    const now = new Date();
    const defaultTime = `${now.getHours().toString().padStart(2, '0')}:${Math.floor(now.getMinutes()/15)*15}`;
    $('input[name="appointment_time"]').val(`${defaultTime}:00`.substring(0,5));

    // Form submission handler
    $('#appointmentForm').submit(function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Scheduling...');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Appointment scheduled successfully',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message || 'Failed to schedule appointment', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Network error - please try again', 'error');
                console.error('Error:', xhr.responseText);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>