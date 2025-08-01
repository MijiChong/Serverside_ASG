<?php
// debug_profile_data.php - Use this to test what data is actually in your database
session_start();
header('Content-Type: application/json');
require 'mysql.php';

$firebase_uid = $_SESSION['uid'] ?? null;
if (!$firebase_uid) {
    echo json_encode(["error" => "User not logged in", "uid" => null]);
    exit;
}

try {
    // Check if profile exists
    $stmt = $pdo->prepare("SELECT * FROM personal_profile WHERE firebase_uid = ?");
    $stmt->execute([$firebase_uid]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    // Also get all profiles to see what's in the database
    $allStmt = $pdo->prepare("SELECT firebase_uid, username, email, first_name, last_name, avatar_gradient, created_at FROM personal_profile LIMIT 5");
    $allStmt->execute();
    $allProfiles = $allStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "current_uid" => $firebase_uid,
        "user_profile" => $profile,
        "all_profiles_sample" => $allProfiles,
        "profile_exists" => $profile ? true : false,
        "table_structure" => "personal_profile table columns should include: id, firebase_uid, username, email, first_name, last_name, dob, phone, address, avatar_gradient, created_at, updated_at"
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        "error" => $e->getMessage(),
        "current_uid" => $firebase_uid
    ], JSON_PRETTY_PRINT);
}
?>
