<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json');

$appointmentId = $_GET['id'] ?? 0;

try {
    // Verify the doctor owns this appointment
    $stmt = $pdo->prepare(
        "SELECT 
            a.*,
            CONCAT(u.first_name, ' ', u.last_name) as patient_name,
            u.email as patient_email,
            u.phone as patient_phone
        FROM appointments a
        JOIN users u ON a.patient_id = u.id
        WHERE a.id = ? AND a.doctor_id = ?"
    );
    $stmt->execute([$appointmentId, $_SESSION['user_id']]);
    
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$appointment) {
        throw new Exception('Appointment not found or access denied');
    }
    
    // Format dates/times
    $appointment['appointment_date'] = date('F j, Y', strtotime($appointment['appointment_date']));
    $appointment['appointment_time'] = date('g:i A', strtotime($appointment['appointment_time']));
    $appointment['status'] = ucfirst($appointment['status']);
    
    echo json_encode($appointment);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}