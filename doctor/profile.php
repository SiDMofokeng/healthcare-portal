<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

// Add this at the top of the file after auth_check.php include
if (isset($_SESSION['profile_update_message'])) {
    $message = $_SESSION['profile_update_message'];
    $type = $_SESSION['profile_update_message_type'] ?? 'success';
    unset($_SESSION['profile_update_message']);
    unset($_SESSION['profile_update_message_type']);
}

$page_title = 'Profile Settings';
$custom_js = 'profile_settings.js';

try {
    // Get current user data from users table
    $user_stmt = $pdo->prepare(
        "SELECT id, username, email, first_name, last_name, created_at 
         FROM users WHERE id = ? LIMIT 1"
    );
    $user_stmt->execute([$_SESSION['user_id']]);
    $user = $user_stmt->fetch();

    // Get additional profile data based on role
    if ($_SESSION['role'] === 'patient') {
        $profile_stmt = $pdo->prepare(
            "SELECT date_of_birth, gender, address, blood_type 
             FROM patients WHERE user_id = ? LIMIT 1"
        );
        $profile_stmt->execute([$_SESSION['user_id']]);
        $profile_data = $profile_stmt->fetch();
    } elseif ($_SESSION['role'] === 'doctor') {
        $profile_stmt = $pdo->prepare(
            "SELECT specialization, qualifications, contact_number 
             FROM doctors WHERE user_id = ? LIMIT 1"
        );
        $profile_stmt->execute([$_SESSION['user_id']]);
        $profile_data = $profile_stmt->fetch();
    }

} catch (PDOException $e) {
    error_log("Database error in profile settings: " . $e->getMessage());
    // Handle error appropriately
    die("An error occurred while loading your profile. Please try again later.");
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-4">
            <!-- Profile Card -->
            <div class="card card-profile border-0 shadow-lg">
                <div class="card-header bg-gradient-primary p-3 position-relative">
                    <div class="position-absolute top-0 end-0 start-0 h-100 bg-gradient-primary opacity-6 rounded-top-3"></div>
                    <div class="text-center position-relative">
                        <div class="avatar avatar-xxl shadow-dark mt-2">
                            <?php if (file_exists("../assets/images/profiles/{$_SESSION['user_id']}.jpg")): ?>
                                <img src="../assets/images/profiles/<?= (int)$_SESSION['user_id'] ?>.jpg" 
                                     alt="<?= htmlspecialchars($user['first_name'] ?? 'User') ?>" 
                                     class="rounded-circle border-white"
                                     onerror="this.onerror=null;this.src='../assets/images/default-avatar.jpg'">
                            <?php else: ?>
                                <div class="avatar avatar-xxl rounded-circle bg-gradient-<?= ($_SESSION['role'] === 'doctor') ? 'primary' : 'secondary'; ?>">
                                    <span class="text-white display-4"><?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1)) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="text-center mt-4">
                        <h5 class="font-weight-bold">
                            <?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?>
                        </h5>
                        <p class="text-muted mb-0">
                            <?php if ($_SESSION['role'] === 'doctor'): ?>
                                <i class="fas fa-user-md me-1"></i> 
                                <?= htmlspecialchars($profile_data['specialization'] ?? 'Doctor') ?>
                            <?php else: ?>
                                <i class="fas fa-user me-1"></i> 
                                <?= ucfirst($_SESSION['role']) ?>
                            <?php endif; ?>
                        </p>
                        <p class="text-sm text-muted mt-2 mb-0">
                            Member since <?= date('M Y', strtotime($user['created_at'])) ?>
                        </p>
                    </div>
                    
                    <hr class="my-3">
                    
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0 text-muted">Email</h6>
                            <p class="text-sm mb-0"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                        <button class="btn btn-sm btn-link text-primary" data-bs-toggle="modal" data-bs-target="#emailModal">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                    </div>
                    
                    <?php if ($_SESSION['role'] === 'patient' && !empty($profile_data['contact_number'])): ?>
                    <div class="mt-3">
                        <h6 class="mb-0 text-muted">Contact Number</h6>
                        <p class="text-sm mb-0"><?= htmlspecialchars($profile_data['contact_number']) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($_SESSION['role'] === 'doctor' && !empty($profile_data['contact_number'])): ?>
                    <div class="mt-3">
                        <h6 class="mb-0 text-muted">Professional Contact</h6>
                        <p class="text-sm mb-0"><?= htmlspecialchars($profile_data['contact_number']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Change Password Card -->
            <div class="card border-0 shadow-lg mt-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="fas fa-lock me-2 text-primary"></i>
                        Change Password
                    </h6>
                </div>
                <div class="card-body">
                    <form id="passwordForm" action="../api/change_password.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <div class="input-group">
                                <input type="password" name="current_password" class="form-control" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" name="new_password" class="form-control" 
                                       pattern=".{8,}" title="Minimum 8 characters" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <div class="input-group">
                                <input type="password" name="confirm_password" class="form-control" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-1"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add this in the body section where you want messages to appear -->
     
            <?php if (isset($message)): ?>
                <div class="alert alert-<?= $type ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
        <div class="col-lg-8">
            <!-- Profile Information Form -->
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="fas fa-user-edit me-2 text-primary"></i>
                        Personal Information
                    </h6>
                </div>
                <div class="card-body">
                    <form id="profileForm" action="/healthcare_portal/api/update_profile.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="role" value="<?= $_SESSION['role'] ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control" 
                                       value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control" 
                                       value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                            </div>
                        </div>
                        
                        <!-- Profile Photo Upload -->
                        <div class="mb-3">
                            <label class="form-label">Profile Photo</label>
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <?php if (file_exists("../assets/images/profiles/{$_SESSION['user_id']}.jpg")): ?>
                                        <img src="../assets/images/profiles/<?= (int)$_SESSION['user_id'] ?>.jpg" 
                                             alt="Current profile photo" 
                                             class="rounded-circle" 
                                             width="60"
                                             onerror="this.onerror=null;this.src='../assets/images/default-avatar.jpg'">
                                    <?php else: ?>
                                        <div class="avatar avatar-lg rounded-circle bg-gradient-<?= ($_SESSION['role'] === 'doctor') ? 'primary' : 'secondary'; ?>">
                                            <span class="text-white display-6"><?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1)) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" name="profile_photo" class="form-control" accept="image/jpeg,image/png">
                                    <small class="text-muted">JPG or PNG, max 2MB</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Role-specific fields -->
                        <?php if ($_SESSION['role'] === 'patient'): ?>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" name="date_of_birth" class="form-control" 
                                           value="<?= htmlspecialchars($profile_data['date_of_birth'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Gender</label>
                                    <select name="gender" class="form-select">
                                        <option value="">Select</option>
                                        <option value="male" <?= ($profile_data['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                        <option value="female" <?= ($profile_data['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                        <option value="other" <?= ($profile_data['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($profile_data['address'] ?? '') ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Blood Type</label>
                                <select name="blood_type" class="form-select">
                                    <option value="">Unknown</option>
                                    <option value="A+" <?= ($profile_data['blood_type'] ?? '') === 'A+' ? 'selected' : '' ?>>A+</option>
                                    <option value="A-" <?= ($profile_data['blood_type'] ?? '') === 'A-' ? 'selected' : '' ?>>A-</option>
                                    <option value="B+" <?= ($profile_data['blood_type'] ?? '') === 'B+' ? 'selected' : '' ?>>B+</option>
                                    <option value="B-" <?= ($profile_data['blood_type'] ?? '') === 'B-' ? 'selected' : '' ?>>B-</option>
                                    <option value="AB+" <?= ($profile_data['blood_type'] ?? '') === 'AB+' ? 'selected' : '' ?>>AB+</option>
                                    <option value="AB-" <?= ($profile_data['blood_type'] ?? '') === 'AB-' ? 'selected' : '' ?>>AB-</option>
                                    <option value="O+" <?= ($profile_data['blood_type'] ?? '') === 'O+' ? 'selected' : '' ?>>O+</option>
                                    <option value="O-" <?= ($profile_data['blood_type'] ?? '') === 'O-' ? 'selected' : '' ?>>O-</option>
                                </select>
                            </div>
                            
                        <?php elseif ($_SESSION['role'] === 'doctor'): ?>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Specialization <span class="text-danger">*</span></label>
                                    <input type="text" name="specialization" class="form-control" 
                                           value="<?= htmlspecialchars($profile_data['specialization'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                                    <input type="tel" name="contact_number" class="form-control" 
                                           value="<?= htmlspecialchars($profile_data['contact_number'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Qualifications</label>
                                <textarea name="qualifications" class="form-control" rows="3"><?= htmlspecialchars($profile_data['qualifications'] ?? '') ?></textarea>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Email Change Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-envelope me-2"></i> Change Email Address
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="emailForm" action="../api/change_email.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Current Email</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Email <span class="text-danger">*</span></label>
                        <input type="email" name="new_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Email</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>