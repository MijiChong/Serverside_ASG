<?php
session_start();
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}
require 'mysql.php';
require_once 'setting_loader.php';
$uid = $_SESSION['uid'];
$stmt = $pdo->prepare("SELECT * FROM habits WHERE firebase_uid = ? ORDER BY created_at DESC");
$stmt->execute([$uid]);
$habits = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>MyTrackDiary - Habits</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="css/habit.css" rel="stylesheet">
    <link href="css/global-setting.css" rel="stylesheet">
    <!-- Navigation CSS -->
    <link href="navigation/navbar.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #06b6d4;
            --accent-color: #f59e0b;
            --success-color: #10b981;
            /* Apply user's selected gradient */
            <?php echo getGradientCSS(); ?>
        }
    </style>
</head>
<body <?php echo getBodyClass(); ?>>
    <!-- Navigation Bar -->
    <?php include 'navigation/navbar.php'; ?>

    <div class="container py-5 fade-in">
        <div class="header-card mb-5">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="header-title"><i class="fas fa-seedling me-2"></i> Your Habits</h2>
                    <p class="text-muted">Stay consistent and track your daily habits</p>
                </div>
                <a href="habit_add_form.php" class="btn btn-purple"><i class="fas fa-plus"></i> New Habit</a>
            </div>
        </div>

        <?php if (count($habits) === 0): ?>
            <div class="alert alert-info">You haven't added any habits yet.</div>
        <?php else: ?>
            <?php foreach ($habits as $habit): ?>
                <div class="entry-card mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h5 class="mb-1"><?= htmlspecialchars($habit['habit_name']) ?></h5>
                            <p class="mb-1 text-muted"><?= htmlspecialchars($habit['note']) ?></p>
                            <small class="text-secondary">Created: <?= htmlspecialchars($habit['created_at']) ?></small>
                        </div>
                        <div>
                            <a href="habit_view_logs.php?habit_id=<?= $habit['habit_id'] ?>" class="btn btn-view btn-sm me-1">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="habit_edit.php?habit_id=<?= $habit['habit_id'] ?>" class="btn btn-edit btn-sm me-1">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="habit_delete.php?habit_id=<?= $habit['habit_id'] ?>" class="btn btn-delete btn-sm" onclick="return confirm('Delete this habit?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
