<?php
session_start();
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}
require 'mysql.php';
require_once 'setting_loader.php';

$uid = $_SESSION['uid'];
$log_id = $_GET['log_id'] ?? null;

if (!$log_id) {
    header("Location: habit.php");
    exit();
}

// Fetch log with habit info for ownership check
$stmt = $pdo->prepare("SELECT hl.*, h.habit_name FROM habit_logs hl JOIN habits h ON hl.habit_id = h.habit_id WHERE hl.log_id = ? AND hl.firebase_uid = ?");
$stmt->execute([$log_id, $uid]);
$log = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$log) {
    header("Location: habit.php?error=notfound");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $log_date = $_POST['log_date'] ?? '';
    $status = $_POST['status'] ?? '';
    $note = $_POST['note'] ?? '';

    if (!$log_date || !in_array($status, ['done', 'missed'])) {
        $error = "Please fill all required fields correctly.";
    } else {
        // Check duplicate date (exclude current log)
        $stmt = $pdo->prepare("SELECT log_id FROM habit_logs WHERE habit_id = ? AND firebase_uid = ? AND log_date = ? AND log_id != ?");
        $stmt->execute([$log['habit_id'], $uid, $log_date, $log_id]);
        if ($stmt->fetch()) {
            header("Location: habit_log_edit.php?log_id=$log_id&error=duplicate");
            exit();
        }

        // Update log
        $stmt = $pdo->prepare("UPDATE habit_logs SET log_date = ?, status = ?, note = ? WHERE log_id = ?");
        $stmt->execute([$log_date, $status, $note, $log_id]);

        header("Location: habit_view_logs.php?habit_id=" . $log['habit_id']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Log - <?= htmlspecialchars($log['habit_name']) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/journal.css" />
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
</head>
<body <?php echo getBodyClass(); ?>>
    <?php include 'navigation/navbar.php'; ?>

    <div class="container main-content">
        <div class="page-header animate-fade-in">
            <nav aria-label="breadcrumb" style="background: none; padding: 0; margin: 0 0 15px 0; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <a href="dashboard.php" style="color: var(--text-secondary); text-decoration: none; transition: all 0.3s ease; display: flex; align-items: center; gap: 5px;"><i class="fas fa-home"></i> Dashboard</a>
                <i class="fas fa-chevron-right" style="font-size: 0.8rem; color: var(--text-secondary);"></i>
                <a href="habit.php" style="color: var(--text-secondary); text-decoration: none; transition: all 0.3s ease; display: flex; align-items: center; gap: 5px;">Habits</a>
                <i class="fas fa-chevron-right" style="font-size: 0.8rem; color: var(--text-secondary);"></i>
                <a href="habit_view_logs.php?habit_id=<?= $log['habit_id'] ?>" style="color: var(--text-secondary); text-decoration: none; transition: all 0.3s ease; display: flex; align-items: center; gap: 5px;"><?= htmlspecialchars($log['habit_name']) ?></a>
                <i class="fas fa-chevron-right" style="font-size: 0.8rem; color: var(--text-secondary);"></i>
                <span style="color: var(--text-primary); font-weight: 500;">Edit Log</span>
            </nav>
            <h1 class="page-title"><i class="fas fa-edit"></i> Edit Log for <?= htmlspecialchars($log['habit_name']) ?></h1>
            <p class="page-subtitle">Update your habit log entry details</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger animate-fade-in" style="border-radius: 12px; padding: 15px 20px; margin-bottom: 25px; border: none; display: flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #ef4444, #dc2626); color: white; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'duplicate'): ?>
            <div class="alert alert-warning animate-fade-in" style="border-radius: 12px; padding: 15px 20px; margin-bottom: 25px; border: none; display: flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #f59e0b, #d97706); color: white; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);">
                <i class="fas fa-exclamation-triangle"></i>
                A log for the selected date already exists. Please choose a different date.
            </div>
        <?php endif; ?>

        <div class="form-container animate-fade-in">
            <form action="habit_log_edit.php?log_id=<?= $log_id ?>" method="post">
                <div class="mb-4">
                    <label for="log_date" class="form-label">
                        <i class="fas fa-calendar-alt text-secondary me-2"></i>
                        Date
                    </label>
                    <input type="date" id="log_date" name="log_date" class="form-control" required value="<?= htmlspecialchars($log['log_date']) ?>" />
                </div>

                <div class="mb-4">
                    <label for="status" class="form-label">
                        <i class="fas fa-check-circle text-secondary me-2"></i>
                        Status
                    </label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="done" <?= $log['status'] === 'done' ? 'selected' : '' ?>>Done</option>
                        <option value="missed" <?= $log['status'] === 'missed' ? 'selected' : '' ?>>Missed</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="note" class="form-label">
                        <i class="fas fa-comment text-secondary me-2"></i>
                        Note (optional)
                    </label>
                    <input type="text" id="note" name="note" class="form-control" placeholder="Add a note about your progress..." value="<?= htmlspecialchars($log['note']) ?>" />
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-journal">
                        <i class="fas fa-save me-2"></i> Update Log
                    </button>
                    <a href="habit_view_logs.php?habit_id=<?= $log['habit_id'] ?>" class="btn btn-outline-journal">
                        <i class="fas fa-times me-2"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>