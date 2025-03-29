<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json');

try {
    $response = ['success' => false];
    
    // Validate input
    $required = ['current_password', 'new_password', 'confirm_password'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    if ($_POST['new_password'] !== $_POST['confirm_password']) {
        throw new Exception("New passwords do not match");
    }
    
    if (strlen($_POST['new_password']) < 8) {
        throw new Exception("Password must be at least 8 characters");
    }

    // Verify current password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!password_verify($_POST['current_password'], $user['password'])) {
        throw new Exception("Current password is incorrect");
    }

    // Update password
    $hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $update_stmt->execute([$hashed_password, $_SESSION['user_id']]);
    
    $_SESSION['profile_update_message'] = 'Password changed successfully!';
    $_SESSION['profile_update_message_type'] = 'success';
    header('Location: ../doctor/profile.php');
    exit();
    
} catch (Exception $e) {
    $_SESSION['profile_update_message'] = 'Error: ' . $e->getMessage();
    $_SESSION['profile_update_message_type'] = 'danger';
    header('Location: ../doctor/profile.php');
    exit();
}
    
    $response = [
        'success' => true,
        'message' => 'Password changed successfully'
    ];
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>