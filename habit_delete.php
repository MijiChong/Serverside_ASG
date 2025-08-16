<?php
session_start();
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}
require 'mysql.php';

$uid = $_SESSION['uid'];
$habit_id = $_GET['habit_id'] ?? null;

if ($habit_id) {
    $stmt = $pdo->prepare("DELETE FROM habit_logs WHERE habit_id = ?");
    $stmt->execute([$habit_id]);

    $stmt = $pdo->prepare("DELETE FROM habits WHERE habit_id = ? AND firebase_uid = ?");
    $stmt->execute([$habit_id, $uid]);
}

header("Location: habit.php");
exit();
?>
