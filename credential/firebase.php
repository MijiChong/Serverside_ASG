<?php
require __DIR__ . '/vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;

$factory = (new Factory)->withServiceAccount(__DIR__.'/firebase_credentials.json');

$auth = $factory->createAuth();

// Example: Verify that connection works by listing users
try {
    $users = $auth->listUsers();
    foreach ($users as $user) {
        echo "User: " . $user->uid . " (" . $user->email . ")<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
