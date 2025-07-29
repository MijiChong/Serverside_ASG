<?php
session_start();

if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

require 'mysql.php';

$uid = $_SESSION['uid'];
$id = $_GET['id'];

$sql = "DELETE FROM journal_entries WHERE id = :id AND firebase_uid = :uid";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id, 'uid' => $uid]);

header("Location: journal_log.php");
exit();

?>
