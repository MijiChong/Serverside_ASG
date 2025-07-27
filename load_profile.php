<?php
session_start();
require 'mysql.php';

$firebase_uid = $_SESSION['uid'] ?? null;
if (!$firebase_uid) {
    http_response_code(401);
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT first_name, last_name, dob, phone, address, avatar_gradient
                           FROM personal_profile 
                           WHERE firebase_uid = ?");
    $stmt->execute([$firebase_uid]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($profile) {
        echo json_encode($profile);
    } else {
        echo json_encode(["error" => "Profile not found"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
