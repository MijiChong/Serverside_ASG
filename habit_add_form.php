<?php
session_start();
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}
require_once 'setting_loader.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Habit</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/habit_1.css" rel="stylesheet"> 
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
        <h2 class="page-title"><i class="fas fa-plus"></i> Add New Habit</h2>
        <p class="page-subtitle">Create a new habit to track</p>
        <br>

        <form action="habit_add.php" method="post">
            <div class="mb-3">
                <label for="habit_name" class="form-label">
                    <i class="fas fa-seedling text-secondary me-2"></i>
                    Habit Name
                </label>
                <input type="text" name="habit_name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="note" class="form-label">
                    <i class="fas fa-comment text-secondary me-2"></i>
                    Note (optional)
                </label>
                <textarea name="note" class="form-control"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-journal">
                    <i class="fas fa-save me-2"></i> Add Habit
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
