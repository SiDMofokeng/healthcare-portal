<?php
require_once 'config/database.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $role = 'patient'; // Default role

    // Validate inputs
    if (empty($username)) $errors[] = "Username is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($password)) $errors[] = "Password is required";
    if ($password !== $confirm_password) $errors[] = "Passwords don't match";
    if (empty($first_name)) $errors[] = "First name is required";
    if (empty($last_name)) $errors[] = "Last name is required";

    // Check if username/email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) $errors[] = "Username or email already exists";

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $pdo->beginTransaction();
        try {
            // Insert user
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role, first_name, last_name) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $hashed_password, $email, $role, $first_name, $last_name]);
            $user_id = $pdo->lastInsertId();
            
            // Insert patient record
            $stmt = $pdo->prepare("INSERT INTO patients (user_id) VALUES (?)");
            $stmt->execute([$user_id]);
            
            $pdo->commit();
            $success = true;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Patient Registration</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Patient Registration</h2>
        
        <?php if ($success): ?>
            <div class="success">
                Registration successful! <a href="login.php">Login here</a>.
            </div>
        <?php else: ?>
            <?php foreach ($errors as $error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
            
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                <input type="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <input type="text" name="first_name" placeholder="First Name" required value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
                <input type="text" name="last_name" placeholder="Last Name" required value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
                <button type="submit">Register</button>
            </form>
            <p>Already have an account? <a href="login.php">Login here</a>.</p>
        <?php endif; ?>
    </div>
</body>
</html>