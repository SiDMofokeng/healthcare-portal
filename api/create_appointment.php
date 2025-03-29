<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    session_start();

    // Validate session
    if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
        throw new Exception('Unauthorized access');
    }

    // Validate required fields
    $required = ['patient_id', 'appointment_date', 'appointment_time', 'reason', 'duration'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Validate date/time
    $date = DateTime::createFromFormat('Y-m-d', $_POST['appointment_date']);
    $time = DateTime::createFromFormat('H:i', $_POST['appointment_time']);
    
    if (!$date || !$time) {
        throw new Exception('Invalid date or time format');
    }

    // Check for time conflicts
    $stmt = $pdo->prepare("
        SELECT id FROM appointments
        WHERE doctor_id = :doctor_id
        AND appointment_date = :app_date
        AND (
            (appointment_time <= :end_time AND ADDTIME(appointment_time, duration_minutes*100) >= :start_time)
            OR (:start_time <= ADDTIME(appointment_time, duration_minutes*100) AND :end_time >= appointment_time)
        )
        AND status != 'cancelled'
    ");

    $start_time = $_POST['appointment_time'];
    $end_time = date('H:i:s', strtotime($start_time) + ($_POST['duration'] * 60));
    
    $stmt->execute([
        ':doctor_id' => $_SESSION['user_id'],
        ':app_date' => $_POST['appointment_date'],
        ':start_time' => $start_time,
        ':end_time' => $end_time
    ]);

    if ($stmt->fetch()) {
        throw new Exception('This time slot is already booked');
    }

    // Create appointment
    $stmt = $pdo->prepare("
        INSERT INTO appointments
        (patient_id, doctor_id, appointment_date, appointment_time, reason, status, duration_minutes)
        VALUES (?, ?, ?, ?, ?, 'scheduled', ?)
    ");

    $stmt->execute([
        $_POST['patient_id'],
        $_SESSION['user_id'],
        $_POST['appointment_date'],
        $_POST['appointment_time'],
        $_POST['reason'],
        $_POST['duration']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Appointment created successfully',
        'appointment_id' => $pdo->lastInsertId()
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}