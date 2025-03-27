<?php
// api/notifications.php
require_once __DIR__.'/../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
  $stmt->execute([$_SESSION['user_id']]);
  echo json_encode(['count' => $stmt->fetchColumn()]);
}