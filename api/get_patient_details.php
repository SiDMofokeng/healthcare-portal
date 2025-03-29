<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $patientId = $_GET['id'] ?? null;
    if (!$patientId) {
        throw new Exception('Patient ID is required');
    }

    // Get basic patient info
    $stmt = $pdo->prepare("
        SELECT 
            u.*,
            p.date_of_birth,
            p.gender,
            p.address,
            p.blood_type,
            p.allergies,
            COUNT(DISTINCT a.id) as appointment_count,
            COUNT(DISTINCT pr.id) as prescription_count,
            COUNT(DISTINCT mr.id) as record_count
        FROM users u
        JOIN patients p ON u.id = p.user_id
        LEFT JOIN appointments a ON u.id = a.patient_id
        LEFT JOIN prescriptions pr ON u.id = pr.patient_id
        LEFT JOIN medical_records mr ON u.id = mr.patient_id
        WHERE u.id = ?
        GROUP BY u.id
    ");
    $stmt->execute([$patientId]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        throw new Exception('Patient not found');
    }

    // Get last appointment date
    $stmt = $pdo->prepare("
        SELECT appointment_date 
        FROM appointments 
        WHERE patient_id = ? 
        ORDER BY appointment_date DESC 
        LIMIT 1
    ");
    $stmt->execute([$patientId]);
    $patient['last_appointment'] = $stmt->fetchColumn() ? date('M j, Y', strtotime($stmt->fetchColumn())) : null;

    // Get next appointment date
    $stmt = $pdo->prepare("
        SELECT appointment_date 
        FROM appointments 
        WHERE patient_id = ? 
        AND appointment_date >= CURDATE()
        ORDER BY appointment_date ASC 
        LIMIT 1
    ");
    $stmt->execute([$patientId]);
    $patient['next_appointment'] = $stmt->fetchColumn() ? date('M j, Y', strtotime($stmt->fetchColumn())) : null;

    echo json_encode($patient);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}