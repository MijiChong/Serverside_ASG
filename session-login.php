<?php
require 'credential/vendor/autoload.php'; // Firebase PHP SDK autoload

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;

// Initialize Firebase Admin SDK
$factory = (new Factory)->withServiceAccount('credential/firebase_credentials.json'); // Replace with your service account key file
$auth = $factory->createAuth();

// Accept raw POST input
$data = json_decode(file_get_contents("php://input"), true);
$idToken = $data['idToken'] ?? null;

if (!$idToken) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing ID token']);
    exit;
}

try {
    $verifiedIdToken = $auth->verifyIdToken($idToken);
    $uid = $verifiedIdToken->claims()->get('sub');

    // Start PHP session
    session_start();
    $_SESSION['uid'] = $uid;

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid ID token']);
}
