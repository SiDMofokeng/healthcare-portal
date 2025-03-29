<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $patientId = $_GET['id'] ?? null;
    if (!$patientId) {
        throw new Exception('Patient ID is required');
    }

    $stmt = $pdo->prepare("
        SELECT 
            u.first_name,
            u.last_name,
            u.email,
            p.date_of_birth,
            p.gender,
            p.blood_type,
            p.insurance_provider,
            p.medical_history,
            p.allergies,
            p.user_id
        FROM patients p
        JOIN users u ON p.user_id = u.id
        WHERE p.user_id = ?
    ");
    $stmt->execute([$patientId]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        throw new Exception('Patient not found');
    }

    echo json_encode($patient);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}