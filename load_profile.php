<?php
session_start();
header('Content-Type: application/json');
require 'mysql.php';

$firebase_uid = $_SESSION['uid'] ?? null;
if (!$firebase_uid) {
    http_response_code(401);
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

try {
    // Get MySQL profile data (excluding username and email which come from Firestore)
    $stmt = $pdo->prepare("SELECT first_name, last_name, dob, phone, address, avatar_gradient
                           FROM personal_profile 
                           WHERE firebase_uid = ?");
    $stmt->execute([$firebase_uid]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($profile) {
        // Return MySQL data - username and email will be loaded separately from Firestore
        echo json_encode($profile);
    } else {
        // Return empty profile structure with default avatar gradient
        echo json_encode([
            "first_name" => "",
            "last_name" => "", 
            "dob" => "",
            "phone" => "",
            "address" => "",
            "avatar_gradient" => 1
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>