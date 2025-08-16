<?php
session_start();
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

require 'mysql.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $habit_id = $_POST['habit_id'];
    $log_date = $_POST['log_date'];
    $status = $_POST['status'];
    $note = $_POST['note'] ?? '';

    // Get Firebase UID from session
    $firebase_uid = $_SESSION['uid'];

    // Check for duplicate log_date for this habit and user
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM habit_logs WHERE habit_id = ? AND firebase_uid = ? AND log_date = ?");
    $checkStmt->execute([$habit_id, $firebase_uid, $log_date]);
    $count = $checkStmt->fetchColumn();

    if ($count > 0) {
        // Redirect back with a warning query param
        header("Location: habit_view_logs.php?habit_id=" . urlencode($habit_id) . "&error=duplicate");
        exit();
    }

    // Insert new log if no duplicate
    $stmt = $pdo->prepare("
        INSERT INTO habit_logs (habit_id, log_date, status, note, firebase_uid) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$habit_id, $log_date, $status, $note, $firebase_uid]);

    header("Location: habit_view_logs.php?habit_id=" . urlencode($habit_id));
    exit();
}
