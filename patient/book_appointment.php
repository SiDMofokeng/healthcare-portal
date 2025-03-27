<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

// Only allow patients
if ($_SESSION['role'] !== 'patient') {
    header('HTTP/1.0 403 Forbidden');
    die('Access denied');
}

$errors = [];
$success = false;

// Get list of doctors
$doctors = $pdo->query("SELECT u.id, u.first_name, u.last_name, d.specialization 
                        FROM users u JOIN doctors d ON u.id = d.user_id 
                        WHERE u.role = 'doctor'")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $reason = trim($_POST['reason']);
    
    // Validate inputs
    if (empty($doctor_id)) $errors[] = "Doctor is required";
    if (empty($appointment_date)) $errors[] = "Date is required";
    if (empty($appointment_time)) $errors[] = "Time is required";
    if (empty($reason)) $errors[] = "Reason is required";
    
    // Check if date is in the future
    if (strtotime($appointment_date) < strtotime(date('Y-m-d'))) {
        $errors[] = "Appointment date must be in the future";
    }
    
    // Check if doctor is available (basic check)
    $stmt = $pdo->prepare("SELECT id FROM appointments 
                          WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ?");
    $stmt->execute([$doctor_id, $appointment_date, $appointment_time]);
    if ($stmt->fetch()) {
        $errors[] = "Doctor is not available at that time";
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO appointments 
                              (patient_id, doctor_id, appointment_date, appointment_time, reason) 
                              VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$_SESSION['user_id'], $doctor_id, $appointment_date, $appointment_time, $reason])) {
            $success = true;
        } else {
            $errors[] = "Failed to book appointment";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Appointment</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <h1>Book New Appointment</h1>
        
        <?php if ($success): ?>
            <div class="success">
                Appointment booked successfully! <a href="dashboard.php">Return to dashboard</a>.
            </div>
        <?php else: ?>
            <?php foreach ($errors as $error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="doctor_id">Doctor:</label>
                    <select name="doctor_id" id="doctor_id" required>
                        <option value="">Select a doctor</option>
                        <?php foreach ($doctors as $doctor): ?>
                            <option value="<?= $doctor['id'] ?>">
                                Dr. <?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']) ?> - <?= htmlspecialchars($doctor['specialization']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="appointment_date">Date:</label>
                    <input type="date" name="appointment_date" id="appointment_date" required 
                           min="<?= date('Y-m-d') ?>">
                </div>
                
                <div class="form-group">
                    <label for="appointment_time">Time:</label>
                    <input type="time" name="appointment_time" id="appointment_time" required 
                           min="08:00" max="17:00" step="1800"> <!-- 30 minute steps -->
                </div>
                
                <div class="form-group">
                    <label for="reason">Reason:</label>
                    <textarea name="reason" id="reason" required></textarea>
                </div>
                
                <button type="submit" class="btn">Book Appointment</button>
                <a href="dashboard.php" class="btn">Cancel</a>
            </form>
        <?php endif; ?>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Simple client-side validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const date = new Date(document.getElementById('appointment_date').value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (date < today) {
                alert('Appointment date must be in the future');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>