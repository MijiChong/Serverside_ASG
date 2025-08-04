<?php
// You can add PHP logic here if needed
session_start();
if (!isset($_SESSION['uid'])) {
    // Not logged in
    header('Location: login.php');
    exit;
}
$uid = $_SESSION['uid'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyTrackDiary - Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Navigation CSS -->
    <link href="navigation/navbar.css" rel="stylesheet">
    
    <!-- Main Dashboard CSS -->
    <link href="css/dashboard.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'navigation/navbar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <!-- Welcome Section -->
            <div class="welcome-section animate-fade-in">
                <h1 class="welcome-title">Welcome to MyTrackDiary</h1>
                <p class="welcome-subtitle">
                    Manage your daily routine efficiently with our comprehensive tracking system. 
                    Track your exercises, journal your thoughts, monitor transactions, and build healthy habits.
                </p>
                <div class="row stats-row">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="stat-card">
                            <div class="stat-number">0</div>
                            <div class="stat-label">Exercises Today</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="stat-card">
                            <div class="stat-number">0</div>
                            <div class="stat-label">Journal Entries</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="stat-card">
                            <div class="stat-number">$0</div>
                            <div class="stat-label">Today's Spending</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="stat-card">
                            <div class="stat-number">0</div>
                            <div class="stat-label">Habits Completed</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Module Cards -->
            <div class="row">
                <div class="col-lg-6 col-md-6 mb-4">
                    <div class="module-card exercise-card animate-fade-in">
                        <i class="fas fa-dumbbell module-icon"></i>
                        <h3 class="module-title">Exercise Tracker</h3>
                        <p class="module-description">
                            Record your daily workouts, track your progress, and stay motivated on your fitness journey. 
                            Monitor different types of exercises and their duration.
                        </p>
                        <a href="exercise.php" class="module-btn exercise-btn">
                            <i class="fas fa-play me-2"></i>Start Exercise
                        </a>
                    </div>
                </div>

                <div class="col-lg-6 col-md-6 mb-4">
                    <div class="module-card journal-card animate-fade-in">
                        <i class="fas fa-book module-icon"></i>
                        <h3 class="module-title">Daily Journal</h3>
                        <p class="module-description">
                            Write down your thoughts, reflect on your day, and track your personal growth. 
                            Keep a record of your daily experiences and emotions.
                        </p>
                        <a href="journal.php" class="module-btn journal-btn">
                            <i class="fas fa-pen me-2"></i>Write Entry
                        </a>
                    </div>
                </div>

                <div class="col-lg-6 col-md-6 mb-4">
                    <div class="module-card transaction-card animate-fade-in">
                        <i class="fas fa-wallet module-icon"></i>
                        <h3 class="module-title">Transaction Manager</h3>
                        <p class="module-description">
                            Track your income and expenses, categorize transactions, and monitor your financial health. 
                            Stay on top of your budget and spending habits.
                        </p>
                        <a href="MoneyTracker_design.php" class="module-btn transaction-btn">
                            <i class="fas fa-plus me-2"></i>Add Transaction
                        </a>
                    </div>
                </div>

                <div class="col-lg-6 col-md-6 mb-4">
                    <div class="module-card habit-card animate-fade-in">
                        <i class="fas fa-check-circle module-icon"></i>
                        <h3 class="module-title">Habit Tracker</h3>
                        <p class="module-description">
                            Build positive habits and break negative ones. Track your daily progress and maintain consistency 
                            in your personal development journey.
                        </p>
                        <a href="habits.php" class="module-btn habit-btn">
                            <i class="fas fa-tasks me-2"></i>View Habits
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions animate-fade-in">
                <h3><i class="fas fa-bolt me-2"></i>Quick Actions</h3>
                <a href="exercise.php" class="quick-btn">
                    <i class="fas fa-dumbbell me-1"></i>Log Exercise
                </a>
                <a href="journal.php" class="quick-btn">
                    <i class="fas fa-book me-1"></i>New Journal Entry
                </a>
                <a href="MoneyTracker_design.php" class="quick-btn">
                    <i class="fas fa-wallet me-1"></i>Add Transaction
                </a>
                <a href="habits.php" class="quick-btn">
                    <i class="fas fa-check me-1"></i>Check Habits
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
