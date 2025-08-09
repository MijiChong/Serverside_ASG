<?php
session_start();
header('Content-Type: application/json');
require 'mysql.php';

if (!isset($_SESSION['uid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit();
}

$uid = $_SESSION['uid'];

$data = json_decode(file_get_contents("php://input"), true);
$firstName = $data['firstName'] ?? '';
$lastName  = $data['lastName'] ?? '';
$dob       = !empty($data['dob']) ? $data['dob'] : null;
$phone     = $data['phone'] ?? '';
$address   = $data['address'] ?? '';
$avatar    = isset($data['avatarGradient']) ? (int)$data['avatarGradient'] : 1;

// Log for debugging
error_log("Saving profile - UID: $uid, Avatar gradient: $avatar");

try {
    $stmt = $pdo->prepare("SELECT id FROM personal_profile WHERE firebase_uid = :uid");
    $stmt->execute([':uid' => $uid]);

    if ($stmt->fetch()) {
        // Update existing record - don't touch email and display_name as they come from Firestore
        $update = $pdo->prepare("
            UPDATE personal_profile
            SET first_name = :firstName,
                last_name = :lastName,
                dob = :dob,
                phone = :phone,
                address = :address,
                avatar_gradient = :avatar,
                updated_at = CURRENT_TIMESTAMP
            WHERE firebase_uid = :uid
        ");
        $result = $update->execute([
            ':firstName' => $firstName,
            ':lastName'  => $lastName,
            ':dob'       => $dob,
            ':phone'     => $phone,
            ':address'   => $address,
            ':avatar'    => $avatar,
            ':uid'       => $uid
        ]);
        
        error_log("Profile update result: " . ($result ? 'success' : 'failed'));
    } else {
        // Insert new record - email and display_name will be populated by sync
        $insert = $pdo->prepare("
            INSERT INTO personal_profile 
            (firebase_uid, first_name, last_name, dob, phone, address, avatar_gradient)
            VALUES (:uid, :firstName, :lastName, :dob, :phone, :address, :avatar)
        ");
        $result = $insert->execute([
            ':uid'       => $uid,
            ':firstName' => $firstName,
            ':lastName'  => $lastName,
            ':dob'       => $dob,
            ':phone'     => $phone,
            ':address'   => $address,
            ':avatar'    => $avatar
        ]);
        
        error_log("Profile insert result: " . ($result ? 'success' : 'failed'));
    }

    if ($result) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'Profile saved successfully', 
            'avatar_gradient' => $avatar
        ]);
    } else {
        throw new Exception('Failed to save profile data');
    }
    
} catch (Exception $e) {
    error_log("Database error in save_profile: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>