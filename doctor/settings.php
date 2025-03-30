<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

//$pdo = Database::getConnection();

// Get current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get additional profile data
if ($_SESSION['role'] === 'patient') {
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $profile = $stmt->fetch();
} elseif ($_SESSION['role'] === 'doctor') {
    $stmt = $pdo->prepare("SELECT * FROM doctors WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $profile = $stmt->fetch();
}

$page_title = 'Profile Settings';
include __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <h2>Profile Settings</h2>
    
    <form action="update_profile.php" method="post" class="mt-4" id="profileForm">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-control" 
                       value="<?= htmlspecialchars($user['first_name']) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-control" 
                       value="<?= htmlspecialchars($user['last_name']) ?>" required>
            </div>
        </div>

        <?php if ($_SESSION['role'] === 'patient'): ?>
            <div class="mb-3">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-control" 
                       value="<?= htmlspecialchars($profile['date_of_birth'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select">
                    <option value="">Select</option>
                    <option value="male" <?= ($profile['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                    <option value="female" <?= ($profile['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                </select>
            </div>
        <?php elseif ($_SESSION['role'] === 'doctor'): ?>
            <div class="mb-3">
                <label class="form-label">Specialization</label>
                <input type="text" name="specialization" class="form-control" 
                       value="<?= htmlspecialchars($profile['specialization'] ?? '') ?>" required>
            </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary">Save Changes</button>
        <div id="successMessage" class="alert alert-success mt-3" style="display: none;"></div>
    </form>
    
    <hr class="my-4">
    
    <h3>Change Password</h3>
    <form action="change_password.php" method="post" class="mt-3">
        <div class="mb-3">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Password</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>