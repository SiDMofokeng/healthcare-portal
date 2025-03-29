<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $appointmentId = $_GET['id'] ?? null;
    
    if (!$appointmentId) {
        throw new Exception('Missing appointment ID');
    }

    $stmt = $pdo->prepare("
        SELECT a.*, u.first_name, u.last_name, u.email, 
               p.date_of_birth, p.gender, p.blood_type,
               d.specialization
        FROM appointments a
        JOIN users u ON a.patient_id = u.id
        LEFT JOIN patients p ON u.id = p.user_id
        LEFT JOIN doctors d ON a.doctor_id = d.user_id
        WHERE a.id = ? AND a.doctor_id = ?
    ");
    $stmt->execute([$appointmentId, $_SESSION['user_id']]);
    
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$appointment) {
        throw new Exception('Appointment not found');
    }

    echo json_encode($appointment);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}