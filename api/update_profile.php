<?php
// Add this at the very top
header('Content-Type: application/json');

// Enable error logging but don't display to users
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth_check.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

try {
    // Validate input
    if (empty($_POST['first_name']) {
        throw new Exception("First name is required");
    }
    if (empty($_POST['last_name'])) {
        throw new Exception("Last name is required");
    }

    // Start transaction
    $pdo->beginTransaction();

    // Update users table
    $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ? WHERE id = ?");
    if (!$stmt->execute([$_POST['first_name'], $_POST['last_name'], $_SESSION['user_id']])) {
        throw new Exception("Failed to update basic information");
    }

    // Handle role-specific updates
    if ($_SESSION['role'] === 'patient') {
        // Patient-specific updates
        $stmt = $pdo->prepare("UPDATE patients SET date_of_birth = ?, gender = ?, address = ?, blood_type = ? WHERE user_id = ?");
        $stmt->execute([
            $_POST['date_of_birth'] ?? null,
            $_POST['gender'] ?? null,
            $_POST['address'] ?? null,
            $_POST['blood_type'] ?? null,
            $_SESSION['user_id']
        ]);
    } elseif ($_SESSION['role'] === 'doctor') {
        // Doctor-specific updates
        $stmt = $pdo->prepare("UPDATE doctors SET specialization = ?, qualifications = ?, contact_number = ? WHERE user_id = ?");
        $stmt->execute([
            $_POST['specialization'],
            $_POST['qualifications'] ?? null,
            $_POST['contact_number'],
            $_SESSION['user_id']
        ]);
    }

    // Handle file upload
    $photoUpdated = false;
    if (!empty($_FILES['profile_photo']['tmp_name'])) {
        $targetDir = __DIR__ . '/../../assets/images/profiles/';
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        $targetFile = $targetDir . $_SESSION['user_id'] . '.jpg';
        
        // Validate image
        $check = getimagesize($_FILES['profile_photo']['tmp_name']);
        if ($check === false) {
            throw new Exception("File is not an image");
        }
        
        // Convert to JPG
        $image = imagecreatefromstring(file_get_contents($_FILES['profile_photo']['tmp_name']));
        if ($image !== false && imagejpeg($image, $targetFile, 85)) {
            $photoUpdated = true;
            imagedestroy($image);
        } else {
            throw new Exception("Failed to process image");
        }
    }

    $pdo->commit();
    
    // Update session
    $_SESSION['first_name'] = $_POST['first_name'];
    $_SESSION['last_name'] = $_POST['last_name'];
    
    $response = [
        'success' => true,
        'message' => 'Profile updated successfully',
        'photoUpdated' => $photoUpdated
    ];

} catch (PDOException $e) {
    $pdo->rollBack();
    $response['message'] = "Database error: " . $e->getMessage();
    error_log("PDOException: " . $e->getMessage());
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response['message'] = $e->getMessage();
    error_log("Exception: " . $e->getMessage());
}

// Ensure we only output JSON
die(json_encode($response));
?>