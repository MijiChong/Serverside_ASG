<?php
session_start();
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}
require 'mysql.php';
require_once 'setting_loader.php';

$uid = $_SESSION['uid'];
$habit_id = $_GET['habit_id'] ?? null;

if (!$habit_id) {
    header("Location: habit.php");
    exit();
}

// Fetch habit to edit
$stmt = $pdo->prepare("SELECT * FROM habits WHERE habit_id = ? AND firebase_uid = ?");
$stmt->execute([$habit_id, $uid]);
$habit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$habit) {
    header("Location: habit.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $habit_name = $_POST['habit_name'];
    $note = $_POST['note'] ?? '';

    $stmt = $pdo->prepare("UPDATE habits SET habit_name = ?, note = ? WHERE habit_id = ? AND firebase_uid = ?");
    $stmt->execute([$habit_name, $note, $habit_id, $uid]);

    header("Location: habit.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Habit</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/habit_1.css" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

<div class="container mt-5 animate-fade-in"> 
    <div class="form-container">
        <h2 class="page-title"><i class="fas fa-edit"></i> Edit Habit</h2>
        <p class="page-subtitle">Update your habit details</p>
        <br>
        <form method="post">
            <div class="mb-3">
                <label for="habit_name" class="form-label">
                    <i class="fas fa-seedling text-secondary me-2"></i>
                    Habit Name
                </label>
                <input type="text" name="habit_name" id="habit_name" 
                       class="form-control" 
                       value="<?= htmlspecialchars($habit['habit_name']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="note" class="form-label">
                    <i class="fas fa-comment text-secondary me-2"></i>
                    Note (optional)
                </label>
                <textarea name="note" id="note" class="form-control"><?= htmlspecialchars($habit['note']) ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-journal">
                    <i class="fas fa-save me-2"></i> Update Habit
                </button>
                <a href="habit.php" class="btn btn-outline-journal">
                    <i class="fas fa-times me-2"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
