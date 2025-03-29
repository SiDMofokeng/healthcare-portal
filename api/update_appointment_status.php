<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Get raw POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    if (empty($input['id']) || empty($input['status'])) {
        throw new Exception('Missing required parameters');
    }

    $validStatuses = ['scheduled', 'completed', 'cancelled', 'no_show'];
    if (!in_array($input['status'], $validStatuses)) {
        throw new Exception('Invalid status value');
    }

    // Build the update query
    $query = "UPDATE appointments SET status = :status";
    $params = [
        ':status' => $input['status'],
        ':id' => $input['id'],
        ':doctor_id' => $_SESSION['user_id']
    ];

    // Add cancellation reason if provided
    if (!empty($input['reason'])) {
        $query .= ", notes = CONCAT(COALESCE(notes, ''), :reason)";
        $params[':reason'] = "\n" . date('Y-m-d H:i:s') . " - Status changed to {$input['status']}. Reason: {$input['reason']}";
    }

    $query .= " WHERE id = :id AND doctor_id = :doctor_id";

    // Execute the update
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    if ($stmt->rowCount() === 0) {
        throw new Exception('No appointment found or no changes made');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Appointment status updated successfully'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}