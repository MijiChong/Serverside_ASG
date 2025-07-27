<?php
require 'credential/vendor/autoload.php';

use Kreait\Firebase\Factory;

$factory = (new Factory)->withServiceAccount('credential/firebase_credentials.json');
$auth = $factory->createAuth();

$data = json_decode(file_get_contents("php://input"), true);
$idToken = $data['idToken'] ?? null;

session_start();

if (!$idToken) {
    echo json_encode(['error' => 'Missing ID token', 'received' => $data]);
    exit;
}

try {
    $verifiedIdToken = $auth->verifyIdToken($idToken);
    $uid = $verifiedIdToken->claims()->get('sub'); // Firebase UID

    $_SESSION['uid'] = $uid;

    echo json_encode([
        'success' => true,
        'uid' => $uid,
        'session_id' => session_id(),
        'session_uid' => $_SESSION['uid']
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
