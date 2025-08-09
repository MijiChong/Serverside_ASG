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
    // Get complete profile data including username and email from MySQL
    $stmt = $pdo->prepare("SELECT email, display_name, first_name, last_name, dob, phone, address, avatar_gradient
                           FROM personal_profile 
                           WHERE firebase_uid = ?");
    $stmt->execute([$firebase_uid]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($profile) {
        // Return all data including username and email from MySQL
        echo json_encode([
            "email" => $profile['email'] ?? "",
            "username" => $profile['display_name'] ?? "",
            "first_name" => $profile['first_name'] ?? "",
            "last_name" => $profile['last_name'] ?? "", 
            "dob" => $profile['dob'] ?? "",
            "phone" => $profile['phone'] ?? "",
            "address" => $profile['address'] ?? "",
            "avatar_gradient" => (int)($profile['avatar_gradient'] ?? 1)
        ]);
    } else {
        // Return empty profile structure with default values
        echo json_encode([
            "email" => "",
            "username" => "",
            "first_name" => "",
            "last_name" => "", 
            "dob" => "",
            "phone" => "",
            "address" => "",
            "avatar_gradient" => 1
        ]);
    }
} catch (Exception $e) {
    error_log("Load profile error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>