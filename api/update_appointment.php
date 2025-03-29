<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    if (empty($input['id']) || empty($input['status'])) {
        throw new Exception('Missing required fields');
    }

    $validStatuses = ['scheduled', 'completed', 'cancelled', 'no_show'];
    if (!in_array($input['status'], $validStatuses)) {
        throw new Exception('Invalid status');
    }

    // Update appointment
    $query = "UPDATE appointments SET status = ?";
    $params = [$input['status']];
    
    if (!empty($input['note'])) {
        $query .= ", notes = CONCAT(IFNULL(notes, ''), ?)";
        $params[] = "\n" . date('Y-m-d H:i') . " - Status changed to {$input['status']}: {$input['note']}";
    }
    
    $query .= " WHERE id = ? AND doctor_id = ?";
    $params[] = $input['id'];
    $params[] = $_SESSION['user_id'];

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    echo json_encode(['success' => $stmt->rowCount() > 0]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}