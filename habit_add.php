<?php
session_start();
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}
require 'mysql.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = $_SESSION['uid'];
    $habit_name = $_POST['habit_name'];
    $note = $_POST['note'] ?? '';
    $created_time = date('Y-m-d H:i:s');

    $sql = "INSERT INTO habits (firebase_uid, habit_name, note, created_at) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$uid, $habit_name, $note, $created_time]);

    header("Location: habit.php");
    exit();
}
?>
