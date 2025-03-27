<?php
session_start();
require_once __DIR__.'/config/database.php'; // Critical addition

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // 1. Find user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // 2. Verify credentials
        if ($user && password_verify($password, $user['password'])) {
            
            // 3. Get role-specific data
            if ($user['role'] === 'doctor') {
                $stmt = $pdo->prepare("SELECT specialization FROM doctors WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                $doctorData = $stmt->fetch();
            }

            // 4. Set sessions
            $_SESSION = [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'first_name' => $user['first_name'] ?? '',
                'last_name' => $user['last_name'] ?? '',
                'role' => $user['role'],
                'specialization' => $doctorData['specialization'] ?? null
            ];

            // 5. Redirect
            header("Location: /healthcare_portal/{$user['role']}/dashboard.php");
            exit;
            
        } else {
            $error = "Invalid username or password";
        }
        
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $error = "Database error occurred";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Healthcare Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Healthcare Portal Login</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>