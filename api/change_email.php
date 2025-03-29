<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json');

try {
    $response = ['success' => false];
    
    // Validate input
    $required = ['new_email', 'password'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    if (!filter_var($_POST['new_email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    // Verify password
    $stmt = $pdo->prepare("SELECT password, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!password_verify($_POST['password'], $user['password'])) {
        throw new Exception("Password is incorrect");
    }
    
    // Check if email already exists
    $email_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $email_stmt->execute([$_POST['new_email'], $_SESSION['user_id']]);
    if ($email_stmt->fetch()) {
        throw new Exception("Email address is already in use");
    }

    // Update email
    $update_stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
    $update_stmt->execute([$_POST['new_email'], $_SESSION['user_id']]);
    
    $_SESSION['profile_update_message'] = 'Email address updated successfully!';
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
        'message' => 'Email changed successfully'
    ];
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>