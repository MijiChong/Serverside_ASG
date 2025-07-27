<?php
$host = "serversideass.cnggwsq86587.ap-southeast-1.rds.amazonaws.com";
$dbname = "student_routine";
$username = "admin";
$password = "A]#09Ih^fEz6bI$";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
