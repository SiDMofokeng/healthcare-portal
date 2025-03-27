<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Healthcare Portal' ?></title>
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
                    <!-- Trigger Button -->
                    <button class="profile-trigger" aria-expanded="false" aria-controls="profile-dropdown">
                        <img src="<?= !empty($_SESSION['user_id']) 
                        ? '/assets/images/profiles/'.(int)$_SESSION['user_id'].'.jpg'
                        : '/assets/images/default-avatar.jpg' ?>" 
                        alt="<?= htmlspecialchars($_SESSION['first_name'] ?? 'User') ?>">
                        <span class="profile-name">
                        <?= htmlspecialchars($_SESSION['first_name'] ?? 'User') ?>
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                        </span>
                    </button>

                    <!-- Dropdown Menu -->
                    <div id="profile-dropdown" class="dropdown-menu">
                        <div class="profile-header">
                        <p class="profile-role">
                            <?php if ($_SESSION['role'] === 'doctor'): ?>
                            <i class="fas fa-user-md"></i> Doctor
                            <?php else: ?>
                            <i class="fas fa-user"></i> <?= ucfirst($_SESSION['role']) ?>
                            <?php endif; ?>
                        </p>
                        </div>
                        <ul>
                        <li><a href="/profile"><i class="fas fa-user-cog"></i> My Profile</a></li>
                        <li><a href="/settings"><i class="fas fa-cog"></i> Settings</a></li>
                        <li class="divider"></li>
                        <li><a href="/logout" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>

            </div>
        </header>

        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-heartbeat"></i> HealthPro</h2>
            </div>
            <nav class="sidebar-nav">
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="dashboard.php" class="nav-item active">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="users.php" class="nav-item">
                        <i class="fas fa-users"></i>
                        <span>Manage Users</span>
                    </a>
                    <a href="doctors.php" class="nav-item">
                        <i class="fas fa-user-md"></i>
                        <span>Doctors</span>
                    </a>
                    <a href="patients.php" class="nav-item">
                        <i class="fas fa-procedures"></i>
                        <span>Patients</span>
                    </a>
                    <a href="appointments.php" class="nav-item">
                        <i class="fas fa-calendar-check"></i>
                        <span>Appointments</span>
                    </a>
                    <a href="reports.php" class="nav-item">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports</span>
                    </a>
                    <a href="settings.php" class="nav-item">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                <?php elseif ($_SESSION['role'] === 'doctor'): ?>
                    <!-- Doctor navigation items -->
                    <a href="/doctor/dashboard" class="nav-item">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    
                    <a href="/doctor/appointments" class="nav-item">
                    <i class="fas fa-calendar-check"></i> Appointments
                    </a>
                    
                    <a href="/doctor/patients" class="nav-item">
                    <i class="fas fa-procedures"></i> Patients
                    </a>
                    
                    <a href="/doctor/prescriptions" class="nav-item">
                    <i class="fas fa-prescription-bottle-alt"></i> Prescriptions
                    </a>
                    
                    <div class="nav-divider"></div> <!-- Visual separator -->
                    
                    <!-- Secondary Items -->
                    <a href="/doctor/schedule" class="nav-item">
                    <i class="fas fa-clock"></i> Availability
                    </a>
                    
                    <a href="/doctor/profile" class="nav-item">
                    <i class="fas fa-user-cog"></i> Profile
                    </a>
                <?php else: ?>
                    <!-- Patient navigation items -->
                <?php endif; ?>
            </nav>
            <div class="sidebar-footer">
                <a href="../logout.php" class="nav-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">