<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$pdo = Database::getConnection();

// Verify current password
$stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!password_verify($_POST['current_password'], $user['password'])) {
    die("Current password is incorrect");
}

// Update password
$hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->execute([$hashed_password, $_SESSION['user_id']]);

header("Location: settings.php?success=1");
exit();
?>