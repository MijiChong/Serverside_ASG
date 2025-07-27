<?php
session_start();
require 'mysql.php';

if (!isset($_SESSION['uid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit();
}

$uid = $_SESSION['uid'];

// Decode JSON input
$data = json_decode(file_get_contents("php://input"), true);
$firstName = $data['firstName'] ?? '';
$lastName  = $data['lastName'] ?? '';
$dob       = !empty($data['dob']) ? $data['dob'] : null; // Convert '' to null
$phone     = $data['phone'] ?? '';
$address   = $data['address'] ?? '';
$avatar    = $data['avatarGradient'] ?? 1;

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM personal_profile WHERE firebase_uid = :uid");
    $stmt->execute([':uid' => $uid]);

    if ($stmt->rowCount() > 0) {
        // UPDATE
        $update = $pdo->prepare("
            UPDATE personal_profile
            SET first_name = :firstName,
                last_name = :lastName,
                dob = :dob,
                phone = :phone,
                address = :address,
                avatar_gradient = :avatar
            WHERE firebase_uid = :uid
        ");
        $update->execute([
            ':firstName' => $firstName,
            ':lastName'  => $lastName,
            ':dob'       => $dob,
            ':phone'     => $phone,
            ':address'   => $address,
            ':avatar'    => $avatar,
            ':uid'       => $uid
        ]);
    } else {
        // INSERT
        $insert = $pdo->prepare("
            INSERT INTO personal_profile 
            (firebase_uid, first_name, last_name, dob, phone, address, avatar_gradient)
            VALUES (:uid, :firstName, :lastName, :dob, :phone, :address, :avatar)
        ");
        $insert->execute([
            ':uid'       => $uid,
            ':firstName' => $firstName,
            ':lastName'  => $lastName,
            ':dob'       => $dob,
            ':phone'     => $phone,
            ':address'   => $address,
            ':avatar'    => $avatar
        ]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Profile saved successfully']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
