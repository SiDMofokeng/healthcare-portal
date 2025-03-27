<?php
// api/doctor_appointments.php
session_start();
require_once('../config/database.php');

// Ensure the user is logged in and is a doctor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$doctor_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT id, appointment_date, appointment_time, status, reason FROM appointments WHERE doctor_id = ?");
    $stmt->execute([$doctor_id]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $events = [];
    foreach ($appointments as $appointment) {
        // Combine date and time to create a valid datetime string
        $start_datetime = $appointment['appointment_date'] . 'T' . $appointment['appointment_time'];
        // Assume a default duration of 30 minutes (adjust as needed)
        $end_datetime = date('Y-m-d\TH:i:s', strtotime($start_datetime . ' +30 minutes'));
        
        $events[] = [
            'id'    => $appointment['id'],
            'title' => 'Appointment: ' . $appointment['reason'],
            'start' => $start_datetime,
            'end'   => $end_datetime,
            // You can include other details if necessary
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($events);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
