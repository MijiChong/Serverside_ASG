<?php
session_start();
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}
require 'mysql.php';
require_once 'setting_loader.php';

$uid = $_SESSION['uid'];
$habit_id = filter_var($_GET['habit_id'] ?? null, FILTER_VALIDATE_INT);

if (!$habit_id || $habit_id < 1) {
    header("Location: habit.php");
    exit();
}

try {
    // Fetch habit info
    $stmt = $pdo->prepare("SELECT * FROM habits WHERE habit_id = ? AND firebase_uid = ?");
    $stmt->execute([$habit_id, $uid]);
    $habit = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$habit) {
        header("Location: habit.php");
        exit();
    }

    // Fetch logs
    $stmt = $pdo->prepare("SELECT log_id, log_date, status, note FROM habit_logs WHERE habit_id = ? AND firebase_uid = ? ORDER BY log_date DESC");
    $stmt->execute([$habit_id, $uid]);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate stats
    $completedCount = 0;
    $missedCount = 0;
    foreach ($logs as $log) {
        if ($log['status'] === 'done') {
            $completedCount++;
        } elseif ($log['status'] === 'missed') {
            $missedCount++;
        }
    }
    $totalCount = count($logs);
    $successRate = $totalCount > 0 ? round($completedCount / $totalCount * 100) : 0;

    // Calculate streak
    $currentStreak = 0;
    $tempStreak = 0;
    $sortedLogs = array_reverse($logs);
    foreach ($sortedLogs as $log) {
        if ($log['status'] === 'done') {
            $tempStreak++;
            $currentStreak = max($currentStreak, $tempStreak);
        } else {
            $tempStreak = 0;
        }
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header("Location: error.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Habit Logs - <?= htmlspecialchars($habit['habit_name']) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/habit_view_logs.css" />
    <link href="navigation/navbar.css" rel="stylesheet"> 
    <link href="css/global-setting.css" rel="stylesheet">
    
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

<body <?php echo getBodyClass(); ?>>
</head>
<body class="gradient-bg">
    <?php include 'navigation/navbar.php'; ?>

    <div class="container py-5">
        <!-- Header Container -->
        <div class="header-card mb-4">
            <nav class="breadcrumb-custom mb-3">
                <a href="dashboard.php" class="text-decoration-none">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <i class="fas fa-chevron-right mx-2"></i>
                <a href="habit.php" class="text-decoration-none">Habits</a>
                <i class="fas fa-chevron-right mx-2"></i>
                <span class="breadcrumb-current"><?= htmlspecialchars($habit['habit_name']) ?> - Logs</span>
            </nav>
            
            <h1 class="header-title mb-2">
                <i class="fas fa-book me-2"></i>
                <?= htmlspecialchars($habit['habit_name']) ?>
            </h1>
            <p class="text-muted">Track your progress and build lasting habits</p>
        </div>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'duplicate'): ?>
            <div class="alert alert-danger mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i>
                A log for the selected date already exists. Please choose a different date or edit the existing log.
            </div>
        <?php endif; ?>

        <!-- Add New Log Form -->
        <div class="entry-card mb-4 p-4">
            <h3 class="mb-4">
                <i class="fas fa-plus-circle me-2" style="color: var(--habit-color);"></i>
                Add New Log Entry
            </h3>
            <form action="habit_log.php" method="post" class="row g-3">
                <input type="hidden" name="habit_id" value="<?= htmlspecialchars($habit_id) ?>">
                <div class="col-md-3">
                    <label for="log_date" class="form-label">Date</label>
                    <input type="date" id="log_date" name="log_date" class="form-control" required value="<?= date('Y-m-d') ?>" />
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="done">✅ Completed</option>
                        <option value="missed">❌ Missed</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="note" class="form-label">Note (optional)</label>
                    <input type="text" id="note" name="note" class="form-control" placeholder="How did it go today?" />
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-purple w-100 py-2">
                        <i class="fas fa-save me-1"></i>
                        Log Entry
                    </button>
                </div>
            </form>
        </div>

        <!-- Statistics -->
        <div class="row mb-4 g-3">
            <div class="col-lg-3 col-md-6">
                <div class="entry-card text-center p-3">
                    <div class="stat-number text-success"><?= $completedCount ?></div>
                    <div class="stat-label text-muted">
                        <i class="fas fa-check-circle me-1"></i>
                        Completed
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="entry-card text-center p-3">
                    <div class="stat-number text-danger"><?= $missedCount ?></div>
                    <div class="stat-label text-muted">
                        <i class="fas fa-times-circle me-1"></i>
                        Missed
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="entry-card text-center p-3">
                    <div class="stat-number text-primary"><?= $successRate ?>%</div>
                    <div class="stat-label text-muted">
                        <i class="fas fa-percentage me-1"></i>
                        Success Rate
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="entry-card text-center p-3">
                    <div class="stat-number text-warning"><?= $currentStreak ?></div>
                    <div class="stat-label text-muted">
                        <i class="fas fa-fire me-1"></i>
                        Best Streak
                    </div>
                </div>
            </div>
        </div>

        <!-- Logs Container -->
        <div class="entry-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">
                    <i class="fas fa-history me-2"></i>Recent Activity
                </h3>
                <span class="badge bg-light text-dark">
                    <i class="fas fa-list-ol me-1"></i>
                    <?= $totalCount ?> entries
                </span>
            </div>

            <?php if ($totalCount === 0): ?>
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-3x mb-3 text-muted"></i>
                    <h4 class="text-muted">No logs yet</h4>
                    <p class="text-muted">Start logging your habit progress to see your amazing journey unfold here.</p>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($logs as $index => $log): ?>
                        <div class="col-12 fade-in" style="animation-delay: <?= $index * 0.1 ?>s;">
                            <div class="entry-card p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <span class="badge <?= $log['status'] === 'done' ? 'bg-success' : 'bg-danger' ?> me-2">
                                            <i class="fas fa-<?= $log['status'] === 'done' ? 'check-circle' : 'times-circle' ?> me-1"></i>
                                            <?= $log['status'] === 'done' ? 'Completed' : 'Missed' ?>
                                        </span>
                                        <span class="text-muted">
                                            <i class="fas fa-calendar-day me-1"></i>
                                            <?= date("F j, Y", strtotime($log['log_date'])) ?>
                                        </span>
                                    </div>
                                    <div class="btn-group">
                                        <a href="habit_log_edit.php?log_id=<?= $log['log_id'] ?>" class="btn btn-sm btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="habit_log_delete.php?log_id=<?= $log['log_id'] ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this log?');">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </div>
                                <?php if (!empty($log['note'])): ?>
                                    <div class="mt-2 ps-3 border-start border-3 border-<?= $log['status'] === 'done' ? 'success' : 'danger' ?>">
                                        <i class="fas fa-comment me-2 text-muted"></i>
                                        <span class="text-muted"><?= htmlspecialchars($log['note']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</html>