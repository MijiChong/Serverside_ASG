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

$username = $data['username'] ?? '';
$email = $data['email'] ?? '';

// Validate input
if (empty($username) && empty($email)) {
    echo json_encode(['status' => 'error', 'message' => 'Username or email is required']);
    exit();
}

try {
    // Check if profile already exists
    $stmt = $pdo->prepare("SELECT id, username, email FROM personal_profile WHERE firebase_uid = :uid");
    $stmt->execute([':uid' => $uid]);
    $existingProfile = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingProfile) {
        // Update only if the values have changed
        $needsUpdate = false;
        $updateFields = [];
        $updateParams = [':uid' => $uid];

        if ($existingProfile['username'] !== $username && !empty($username)) {
            $updateFields[] = "username = :username";
            $updateParams[':username'] = $username;
            $needsUpdate = true;
        }

        if ($existingProfile['email'] !== $email && !empty($email)) {
            $updateFields[] = "email = :email";
            $updateParams[':email'] = $email;
            $needsUpdate = true;
        }

        if ($needsUpdate) {
            $updateFields[] = "updated_at = CURRENT_TIMESTAMP";
            $updateQuery = "UPDATE personal_profile SET " . implode(', ', $updateFields) . " WHERE firebase_uid = :uid";
            $updateStmt = $pdo->prepare($updateQuery);
            $updateStmt->execute($updateParams);
        }
    } else {
        // Insert new profile with Firestore data
        $insert = $pdo->prepare("
            INSERT INTO personal_profile 
            (firebase_uid, username, email, avatar_gradient)
            VALUES (:uid, :username, :email, 1)
        ");
        $insert->execute([
            ':uid' => $uid,
            ':username' => $username,
            ':email' => $email
        ]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Firestore data synced successfully']);
    exit;
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
?>