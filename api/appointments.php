<?php
require_once __DIR__.'/../config/database.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$doctor_id = $_SESSION['user_id'];

try {
  switch ($method) {
    case 'GET':
      // Fetch appointments
      $stmt = $pdo->prepare("
        SELECT 
          a.id,
          CONCAT(p.first_name, ' ', p.last_name) AS title,
          CONCAT(a.appointment_date, 'T', a.appointment_time) AS start,
          CONCAT(a.appointment_date, 'T', ADDTIME(a.appointment_time, '01:00:00')) AS end,
          a.status,
          CASE 
            WHEN a.status = 'cancelled' THEN '#dc3545'
            WHEN a.status = 'completed' THEN '#28a745'
            ELSE '#007bff'
          END AS color
        FROM appointments a
        JOIN users p ON a.patient_id = p.id
        WHERE a.doctor_id = ?
      ");
      $stmt->execute([$doctor_id]);
      echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
      break;

    case 'PUT':
      // Update appointment
      $data = json_decode(file_get_contents('php://input'), true);
      $stmt = $pdo->prepare("
        UPDATE appointments 
        SET appointment_date = ?, 
            appointment_time = ? 
        WHERE id = ? AND doctor_id = ?
      ");
      $stmt->execute([
        $data['date'],
        $data['time'],
        $_GET['id'],
        $doctor_id
      ]);
      echo json_encode(['success' => true]);
      break;

    default:
      http_response_code(405);
      echo json_encode(['error' => 'Method not allowed']);
  }
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}