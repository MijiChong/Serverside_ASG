<?php
session_start();

if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

require 'mysql.php';

$uid = $_SESSION['uid'];
$id = $_GET['id'];

$sql = "DELETE FROM exercise_records WHERE exercise_id = :id AND firebase_uid = :uid";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id, 'uid' => $uid]);

header("Location: exercise.php");
exit();
?>