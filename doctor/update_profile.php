<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

//$pdo = Database::getConnection();

try {
    // Update users table
    $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ? WHERE id = ?");
    $stmt->execute([
        $_POST['first_name'],
        $_POST['last_name'],
        $_SESSION['user_id']
    ]);

    // Update role-specific table
    if ($_SESSION['role'] === 'patient') {
        $stmt = $pdo->prepare("UPDATE patients SET date_of_birth = ?, gender = ? WHERE user_id = ?");
        $stmt->execute([
            $_POST['date_of_birth'] ?? null,
            $_POST['gender'] ?? null,
            $_SESSION['user_id']
        ]);
    } elseif ($_SESSION['role'] === 'doctor') {
        $stmt = $pdo->prepare("UPDATE doctors SET specialization = ? WHERE user_id = ?");
        $stmt->execute([
            $_POST['specialization'],
            $_SESSION['user_id']
        ]);
    }

    header("Location: settings.php?success=1");
    exit();
} catch (PDOException $e) {
    die("Error updating profile: " . $e->getMessage());
}

// Return success response
http_response_code(200);
exit();
?>