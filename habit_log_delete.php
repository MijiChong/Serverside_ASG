<?php
session_start();
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}
require 'mysql.php';

$uid = $_SESSION['uid'];
$log_id = $_GET['log_id'] ?? null;

if (!$log_id) {
    header("Location: habit.php");
    exit();
}

// Verify the log belongs to this user
$stmt = $pdo->prepare("SELECT habit_id FROM habit_logs WHERE log_id = ? AND firebase_uid = ?");
$stmt->execute([$log_id, $uid]);
$log = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$log) {
    // Log not found or not owned by user
    header("Location: habit.php?error=notfound");
    exit();
}

// Delete the log
$stmt = $pdo->prepare("DELETE FROM habit_logs WHERE log_id = ?");
$stmt->execute([$log_id]);

// Redirect back to habit logs page
header("Location: habit_view_logs.php?habit_id=" . $log['habit_id']);
exit();
