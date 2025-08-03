<?php
session_start();
header('Content-Type: application/json');
require 'mysql.php';

if (!isset($_SESSION['uid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$uid = $data['uid'] ?? $_SESSION['uid'];
$username = $data['username'] ?? '';
$email = $data['email'] ?? '';

// Log for debugging
error_log("Syncing Firestore data - UID: $uid, Username: $username, Email: $email");

try {
    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM personal_profile WHERE firebase_uid = :uid");
    $stmt->execute([':uid' => $uid]);
    
    if ($stmt->fetch()) {
        // Update existing record with Firestore data
        $update = $pdo->prepare("
            UPDATE personal_profile 
            SET email = :email, 
                display_name = :username,
                updated_at = CURRENT_TIMESTAMP
            WHERE firebase_uid = :uid
        ");
        $result = $update->execute([
            ':email' => $email,
            ':username' => $username,
            ':uid' => $uid
        ]);
        
        error_log("Updated existing profile: " . ($result ? 'success' : 'failed'));
    } else {
        // Create new record with Firestore data
        $insert = $pdo->prepare("
            INSERT INTO personal_profile 
            (firebase_uid, email, display_name, avatar_gradient) 
            VALUES (:uid, :email, :username, 1)
        ");
        $result = $insert->execute([
            ':uid' => $uid,
            ':email' => $email,
            ':username' => $username
        ]);
        
        error_log("Created new profile: " . ($result ? 'success' : 'failed'));
    }
    
    echo json_encode(['status' => 'success', 'message' => 'Firestore data synced successfully']);
    
} catch (Exception $e) {
    error_log("Sync error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>