<?php
// Enhanced dashboard with real data analytics and new features
session_start();
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}
$uid = $_SESSION['uid'];

require_once 'setting_loader.php';
require_once 'mysql.php'; // Use your existing database connection

try {
    // Check if user has completed their profile
    $stmt = $pdo->prepare("SELECT first_name, last_name, display_name, dob FROM personal_profile WHERE firebase_uid = ?");
    $stmt->execute([$uid]);
    $userProfile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Determine if this is a first-time user or incomplete profile
    $isFirstTimeUser = !$userProfile || 
                      empty($userProfile['first_name']) || 
                      empty($userProfile['last_name']) || 
                      empty($userProfile['display_name']);
    
    // Get today's date and time periods
    $today = date('Y-m-d');
    $currentMonth = date('Y-m');
    $currentYear = date('Y');
    $weekAgo = date('Y-m-d', strtotime('-7 days'));
    $monthAgo = date('Y-m-d', strtotime('-30 days'));
    
    // === TODAY'S STATS ===
    // Exercises today
    $stmt = $pdo->prepare("SELECT COUNT(*) as count, SUM(duration) as total_duration, SUM(calories_burned) as total_calories FROM exercise_records WHERE firebase_uid = ? AND exercise_date = ?");
    $stmt->execute([$uid, $today]);
    $todayExercise = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Journal entries today
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM journal_entries WHERE firebase_uid = ? AND entry_date = ?");
    $stmt->execute([$uid, $today]);
    $todayJournal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Today's spending and income
    $stmt = $pdo->prepare("SELECT SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) as expenses, SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END) as income FROM transactions WHERE firebase_uid = ? AND transaction_date = ?");
    $stmt->execute([$uid, $today]);
    $todayFinance = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Habits completed today
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM habit_logs WHERE firebase_uid = ? AND log_date = ? AND status = 'done'");
    $stmt->execute([$uid, $today]);
    $todayHabits = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // === STREAK CALCULATIONS ===
    // Exercise streak
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as streak 
        FROM (
            SELECT exercise_date 
            FROM exercise_records 
            WHERE firebase_uid = ? AND exercise_date >= (CURDATE() - INTERVAL 30 DAY)
            GROUP BY exercise_date
            ORDER BY exercise_date DESC
        ) as daily_exercise
    ");
    $stmt->execute([$uid]);
    $exerciseStreak = $stmt->fetch(PDO::FETCH_ASSOC)['streak'] ?? 0;
    
    // Journal streak
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as streak 
        FROM (
            SELECT entry_date 
            FROM journal_entries 
            WHERE firebase_uid = ? AND entry_date >= (CURDATE() - INTERVAL 30 DAY)
            GROUP BY entry_date
            ORDER BY entry_date DESC
        ) as daily_journal
    ");
    $stmt->execute([$uid]);
    $journalStreak = $stmt->fetch(PDO::FETCH_ASSOC)['streak'] ?? 0;
    
    // === WEEKLY PROGRESS ===
    $stmt = $pdo->prepare("SELECT COUNT(*) as sessions, SUM(duration) as total_minutes, AVG(calories_burned) as avg_calories FROM exercise_records WHERE firebase_uid = ? AND exercise_date >= ?");
    $stmt->execute([$uid, $weekAgo]);
    $weeklyExercise = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Weekly habit completion rate
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(CASE WHEN status = 'done' THEN 1 END) as completed,
            COUNT(*) as total
        FROM habit_logs 
        WHERE firebase_uid = ? AND log_date >= ?
    ");
    $stmt->execute([$uid, $weekAgo]);
    $weeklyHabits = $stmt->fetch(PDO::FETCH_ASSOC);
    $habitCompletionRate = $weeklyHabits['total'] > 0 ? round(($weeklyHabits['completed'] / $weeklyHabits['total']) * 100) : 0;
    
    // === MONTHLY FINANCIAL SUMMARY ===
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END) as total_income,
            SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) as total_expenses
        FROM transactions 
        WHERE firebase_uid = ? AND DATE_FORMAT(transaction_date, '%Y-%m') = ?
    ");
    $stmt->execute([$uid, $currentMonth]);
    $monthlyFinance = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // === GOALS AND ACHIEVEMENTS ===
    // Weekly exercise goal (example: 5 sessions per week)
    $weeklyExerciseGoal = 5;
    $exerciseGoalProgress = min(100, round(($weeklyExercise['sessions'] / $weeklyExerciseGoal) * 100));
    
    // Monthly savings goal (example: save 20% of income)
    $monthlySavingsGoal = ($monthlyFinance['total_income'] ?? 0) * 0.2;
    $actualSavings = ($monthlyFinance['total_income'] ?? 0) - ($monthlyFinance['total_expenses'] ?? 0);
    $savingsProgress = $monthlySavingsGoal > 0 ? min(100, round(($actualSavings / $monthlySavingsGoal) * 100)) : 0;
    
    // === RECENT ACTIVITIES (Enhanced) ===
    $recentActivities = [];
    
    // Recent exercises with more details
    $stmt = $pdo->prepare("SELECT exercise_type, exercise_date as activity_date, 'exercise' as type, duration, calories_burned, created_at FROM exercise_records WHERE firebase_uid = ? ORDER BY created_at DESC LIMIT 3");
    $stmt->execute([$uid]);
    $recentActivities = array_merge($recentActivities, $stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // Recent journal entries
    $stmt = $pdo->prepare("SELECT mood, entry_date as activity_date, 'journal' as type, SUBSTRING(content, 1, 50) as preview, created_at FROM journal_entries WHERE firebase_uid = ? ORDER BY created_at DESC LIMIT 3");
    $stmt->execute([$uid]);
    $recentActivities = array_merge($recentActivities, $stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // Recent transactions with categories
    $stmt = $pdo->prepare("SELECT t.transaction_type, t.transaction_date as activity_date, 'transaction' as type, t.amount, t.description, c.category_name, t.created_at FROM transactions t LEFT JOIN categories c ON t.category_id = c.category_id WHERE t.firebase_uid = ? ORDER BY t.created_at DESC LIMIT 3");
    $stmt->execute([$uid]);
    $recentActivities = array_merge($recentActivities, $stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // Sort by created_at timestamp
    usort($recentActivities, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    $recentActivities = array_slice($recentActivities, 0, 8); // Show more recent activities
    
    // === INSIGHTS AND RECOMMENDATIONS ===
    $insights = [];
    
    // Exercise insights
    if ($weeklyExercise['sessions'] >= 4) {
        $insights[] = ['type' => 'success', 'icon' => 'fas fa-trophy', 'message' => 'Great job! You\'ve been consistent with exercise this week.'];
    } elseif ($weeklyExercise['sessions'] < 2) {
        $insights[] = ['type' => 'warning', 'icon' => 'fas fa-exclamation-triangle', 'message' => 'Try to add more exercise sessions this week for better health.'];
    }
    
    // Financial insights
    if ($actualSavings > 0) {
        $insights[] = ['type' => 'success', 'icon' => 'fas fa-piggy-bank', 'message' => 'Excellent! You saved $' . number_format($actualSavings, 2) . ' this month.'];
    } elseif ($actualSavings < 0) {
        $insights[] = ['type' => 'danger', 'icon' => 'fas fa-exclamation-circle', 'message' => 'Your expenses exceeded income this month. Consider budgeting.'];
    }
    
    // Habit insights
    if ($habitCompletionRate >= 80) {
        $insights[] = ['type' => 'success', 'icon' => 'fas fa-star', 'message' => 'Amazing habit consistency! Keep up the great work.'];
    }
    
} catch(PDOException $e) {
    // Handle database errors gracefully
    $isFirstTimeUser = false;
    // Set default values for all variables
    $todayExercise = ['count' => 0, 'total_duration' => 0, 'total_calories' => 0];
    $todayJournal = ['count' => 0];
    $todayFinance = ['expenses' => 0, 'income' => 0];
    $todayHabits = ['count' => 0];
    $weeklyExercise = ['sessions' => 0, 'total_minutes' => 0, 'avg_calories' => 0];
    $habitCompletionRate = 0;
    $monthlyFinance = ['total_income' => 0, 'total_expenses' => 0];
    $recentActivities = [];
    $exerciseStreak = $journalStreak = 0;
    $exerciseGoalProgress = $savingsProgress = 0;
    $insights = [];
}
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
    
    <!-- Enhanced Dashboard CSS -->
    <link href="css/dashboard.css" rel="stylesheet">
    <link href="css/global-setting.css" rel="stylesheet">
    
    <style>
    :root {
        --primary-color: #4f46e5;
        --secondary-color: #06b6d4;
        --accent-color: #f59e0b;
        --success-color: #10b981;
        <?php echo getGradientCSS(); ?>
    }
    </style>
</head>
<body <?php echo getBodyClass(); ?>>
    
    <!-- Navigation Bar -->
    <?php include 'navigation/navbar.php'; ?>

    <!-- Main Content Container -->
    <div class="main-content">
        <div class="container-fluid px-4">
            
            <!-- Header Section with Welcome -->
            <?php date_default_timezone_set('Asia/Kuala_Lumpur'); ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="welcome-section enhanced-welcome animate-fade-in">
                        <div class="row align-items-center">
                            <div class="col-lg-8 col-xl-9">
                                <div class="welcome-content">
                                    <h1 class="welcome-title">
                                        Good <?php echo date('H') < 12 ? 'Morning' : (date('H') < 18 ? 'Afternoon' : 'Evening'); ?>! 👋
                                    </h1>
                                    <p class="welcome-subtitle">
                                        Ready to crush your goals today? Here's your personalized dashboard with real-time insights.
                                    </p>
                                    <div class="streak-indicators">
                                        <div class="streak-item">
                                            <i class="fas fa-fire exercise-color"></i>
                                            <span><?php echo $exerciseStreak; ?> day exercise streak</span>
                                        </div>
                                        <div class="streak-item">
                                            <i class="fas fa-pen-fancy journal-color"></i>
                                            <span><?php echo $journalStreak; ?> day journal streak</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-xl-3">
                                <div class="date-weather-widget">
                                    <div class="today-date">
                                        <i class="fas fa-calendar-day me-2"></i>
                                        <?php echo date('l, F j, Y'); ?>
                                    </div>
                                    <div class="current-time" id="currentTime">
                                        <?php echo date('h:i A'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Stats Row -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="stat-card exercise-stat enhanced-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="stat-icon">
                            <i class="fas fa-dumbbell"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $todayExercise['count'] ?: 0; ?></div>
                            <div class="stat-label">Workouts Today</div>
                            <div class="stat-detail">
                                <?php if($todayExercise['total_duration']): ?>
                                    <?php echo $todayExercise['total_duration']; ?> min • <?php echo $todayExercise['total_calories']; ?> cal
                                <?php else: ?>
                                    Ready to start?
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="progress-ring">
                            <div class="progress-circle" data-progress="<?php echo $exerciseGoalProgress; ?>"></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="stat-card journal-stat enhanced-card" data-aos="fade-up" data-aos-delay="200">
                        <div class="stat-icon">
                            <i class="fas fa-journal-whills"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $todayJournal['count'] ?: 0; ?></div>
                            <div class="stat-label">Journal Entries</div>
                            <div class="stat-detail">
                                <?php echo $journalStreak; ?> day streak 🔥
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="stat-card transaction-stat enhanced-card" data-aos="fade-up" data-aos-delay="300">
                        <div class="stat-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">$<?php echo number_format($todayFinance['expenses'] ?: 0, 2); ?></div>
                            <div class="stat-label">Today's Spending</div>
                            <div class="stat-detail">
                                <?php if($todayFinance['income']): ?>
                                    +$<?php echo number_format($todayFinance['income'], 2); ?> earned
                                <?php else: ?>
                                    No income today
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="stat-card habit-stat enhanced-card" data-aos="fade-up" data-aos-delay="400">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $todayHabits['count'] ?: 0; ?></div>
                            <div class="stat-label">Habits Done</div>
                            <div class="stat-detail"><?php echo $habitCompletionRate; ?>% weekly average</div>
                        </div>
                        <div class="habit-progress">
                            <div class="progress-bar" data-width="<?php echo $habitCompletionRate; ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="row">
                <!-- Left Column - Module Cards Only -->
                <div class="col-xl-8 col-lg-7">

                    <!-- Module Cards Grid -->
                    <div class="row mb-4">
                        <div class="col-lg-6 mb-4">
                            <div class="module-card exercise-card enhanced-module animate-fade-in">
                                <div class="module-header">
                                    <i class="fas fa-dumbbell module-icon"></i>
                                    <div class="module-badge">
                                        <?php echo $weeklyExercise['sessions']; ?> this week
                                    </div>
                                </div>
                                <h3 class="module-title">Exercise Tracker</h3>
                                <p class="module-description">
                                    Track workouts, monitor calories, and achieve your fitness goals with detailed analytics.
                                </p>
                                
                                <div class="module-stats">
                                    <div class="stats-grid">
                                        <div class="stat-item">
                                            <span class="stat-value"><?php echo $weeklyExercise['total_minutes'] ?: 0; ?></span>
                                            <span class="stat-label">Minutes</span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-value"><?php echo round($weeklyExercise['avg_calories'] ?: 0); ?></span>
                                            <span class="stat-label">Avg Calories</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="module-actions">
                                    <a href="exercise_add.php" class="module-btn primary-btn">
                                        <i class="fas fa-play me-2"></i>Start Workout
                                    </a>
                                    <a href="exercise.php?view=history" class="module-btn secondary-btn">
                                        <i class="fas fa-history me-2"></i>View Histories
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <div class="module-card journal-card enhanced-module animate-fade-in">
                                <div class="module-header">
                                    <i class="fas fa-journal-whills module-icon"></i>
                                    <div class="module-badge">
                                        <?php echo $journalStreak; ?> day streak
                                    </div>
                                </div>
                                <h3 class="module-title">Daily Journal</h3>
                                <p class="module-description">
                                    Reflect on your thoughts, track moods, and monitor your personal growth journey.
                                </p>
                                
                                <div class="module-stats">
                                    <div class="mood-overview">
                                        <span class="dominant-mood">Personal reflection space</span>
                                    </div>
                                </div>
                                
                                <div class="module-actions">
                                    <a href="journal_create.php" class="module-btn primary-btn">
                                        <i class="fas fa-pen me-2"></i>Write Entry
                                    </a>
                                    <a href="journal_log.php?view=entries" class="module-btn secondary-btn">
                                        <i class="fas fa-book-open me-2"></i>View Entries
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <div class="module-card transaction-card enhanced-module animate-fade-in">
                                <div class="module-header">
                                    <i class="fas fa-wallet module-icon"></i>
                                    <div class="module-badge <?php echo $actualSavings >= 0 ? 'positive' : 'negative'; ?>">
                                        <?php echo $actualSavings >= 0 ? '+' : ''; ?>$<?php echo number_format($actualSavings, 0); ?>
                                    </div>
                                </div>
                                <h3 class="module-title">Financial Tracker</h3>
                                <p class="module-description">
                                    Manage expenses, track income, and maintain healthy financial habits with smart insights.
                                </p>
                                
                                <div class="module-stats">
                                    <div class="financial-summary">
                                        <div class="summary-item income">
                                            <span class="summary-label">Monthly Income</span>
                                            <span class="summary-value">+$<?php echo number_format($monthlyFinance['total_income'] ?: 0, 0); ?></span>
                                        </div>
                                        <div class="summary-item expenses">
                                            <span class="summary-label">Monthly Expenses</span>
                                            <span class="summary-value">-$<?php echo number_format($monthlyFinance['total_expenses'] ?: 0, 0); ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="module-actions">
                                    <a href="MoneyTracker_design.php" class="module-btn primary-btn">
                                        <i class="fas fa-plus me-2"></i>Add Transaction
                                    </a>
                                    <a href="MoneyTracker_design.php?view=reports" class="module-btn secondary-btn">
                                        <i class="fas fa-chart-pie me-2"></i>View Reports
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <div class="module-card habit-card enhanced-module animate-fade-in">
                                <div class="module-header">
                                    <i class="fas fa-seedling module-icon"></i>
                                    <div class="module-badge">
                                        <?php echo $habitCompletionRate; ?>% rate
                                    </div>
                                </div>
                                <h3 class="module-title">Habit Builder</h3>
                                <p class="module-description">
                                    Build positive habits, track consistency, and develop lasting routines for success.
                                </p>
                                
                                <div class="module-stats">
                                    <div class="habit-progress-ring">
                                        <svg class="progress-ring" width="80" height="80">
                                            <circle class="progress-ring__circle" stroke="#e5e7eb" stroke-width="4" fill="transparent" r="36" cx="40" cy="40"/>
                                            <circle class="progress-ring__progress" stroke="#f59e0b" stroke-width="4" fill="transparent" r="36" cx="40" cy="40" 
                                                    style="stroke-dasharray: 226.19; stroke-dashoffset: <?php echo 226.19 - (226.19 * $habitCompletionRate / 100); ?>;"/>
                                        </svg>
                                        <div class="progress-text">
                                            <span class="progress-percentage"><?php echo $habitCompletionRate; ?>%</span>
                                            <span class="progress-label">Weekly</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="module-actions">
                                    <a href="habit_add_form.php?action=add" class="module-btn primary-btn">
                                        <i class="fas fa-tasks me-2"></i>Add Habit
                                    </a>
                                    <a href="habit.php" class="module-btn secondary-btn">
                                        <i class="fas fa-plus me-2"></i>View Habits
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Column - Activity Feed and Quick Actions -->
                <div class="col-xl-4 col-lg-5">
                    
                    <!-- Recent Activities -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="activity-feed enhanced-activity">
                                <h5 class="card-title">
                                    <span><i class="fas fa-clock me-2"></i>Recent Activities</span>
                                    <span class="activity-count"><?php echo count($recentActivities); ?></span>
                                </h5>
                                
                                <div class="activity-list">
                                    <?php if(!empty($recentActivities)): ?>
                                        <?php foreach($recentActivities as $activity): ?>
                                        <div class="activity-item enhanced-activity-item">
                                            <div class="activity-icon" style="background: 
                                                <?php 
                                                switch($activity['type']) {
                                                    case 'exercise': echo 'var(--exercise-color)'; break;
                                                    case 'journal': echo 'var(--journal-color)'; break;
                                                    case 'transaction': echo 'var(--transaction-color)'; break;
                                                    default: echo 'var(--primary-color)';
                                                }
                                                ?>">
                                                <i class="fas fa-<?php 
                                                    switch($activity['type']) {
                                                        case 'exercise': echo 'dumbbell'; break;
                                                        case 'journal': echo 'journal-whills'; break;
                                                        case 'transaction': echo 'wallet'; break;
                                                        default: echo 'circle';
                                                    }
                                                ?>"></i>
                                            </div>
                                            <div class="activity-content">
                                                <div class="activity-header">
                                                    <div class="activity-text">
                                                        <?php
                                                        switch($activity['type']) {
                                                            case 'exercise':
                                                                echo ucfirst($activity['exercise_type']) . " workout";
                                                                break;
                                                            case 'journal':
                                                                echo "Journal entry - " . ucfirst($activity['mood']);
                                                                break;
                                                            case 'transaction':
                                                                echo ucfirst($activity['transaction_type']) . " - " . ($activity['category_name'] ?? 'Uncategorized');
                                                                break;
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="activity-time">
                                                        <?php echo date('M j, g:i A', strtotime($activity['created_at'])); ?>
                                                    </div>
                                                </div>
                                                <div class="activity-detail">
                                                    <?php
                                                    switch($activity['type']) {
                                                        case 'exercise':
                                                            echo $activity['duration'] . " min • " . $activity['calories_burned'] . " calories";
                                                            break;
                                                        case 'journal':
                                                            echo isset($activity['preview']) ? $activity['preview'] . '...' : 'Personal reflection';
                                                            break;
                                                        case 'transaction':
                                                            echo "$" . number_format($activity['amount'], 2) . " • " . $activity['description'];
                                                            break;
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                    <div class="no-activity">
                                        <i class="fas fa-history"></i>
                                        <p>No recent activities yet.<br>Start tracking to see your progress here!</p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Setup Reminder (if needed) -->
                    <?php if($isFirstTimeUser): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="profile-reminder enhanced-reminder animate-fade-in" id="profileReminder">
                                <div class="pulse-animation">
                                    <h6><i class="fas fa-user-circle me-2"></i>Complete Your Profile</h6>
                                    <p class="mb-3">Set up your personal information to get personalized insights and better tracking experience.</p>
                                    <div class="d-flex gap-2">
                                        <a href="profile.php" class="btn btn-light btn-sm">
                                            <i class="fas fa-arrow-right me-1"></i>Complete Now
                                        </a>
                                        <button onclick="dismissReminder()" class="btn btn-outline-light btn-sm">
                                            <i class="fas fa-times me-1"></i>Later
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced JavaScript -->
    <script>
        // Real-time clock update
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });
            document.getElementById('currentTime').textContent = timeString;
        }
        setInterval(updateClock, 1000);

        // Profile reminder dismiss functionality
        function dismissReminder() {
            const reminder = document.getElementById('profileReminder');
            if (reminder) {
                reminder.style.animation = 'slideUp 0.5s ease-out forwards';
                setTimeout(() => {
                    reminder.remove();
                }, 500);
            }
            sessionStorage.setItem('profileReminderDismissed', 'true');
        }

        // Check if reminder was already dismissed
        document.addEventListener('DOMContentLoaded', function() {
            if (sessionStorage.getItem('profileReminderDismissed') === 'true') {
                const reminder = document.getElementById('profileReminder');
                if (reminder) reminder.style.display = 'none';
            }

            // Initialize progress elements
            initializeProgressElements();
            
            // Initialize interactive elements
            initializeInteractions();
        });

        // Initialize progress elements
        function initializeProgressElements() {
            // Animate progress bars
            document.querySelectorAll('.progress-bar').forEach(bar => {
                const width = bar.dataset.width;
                setTimeout(() => {
                    bar.style.width = width;
                }, 500);
            });

            // Animate progress bars custom
            document.querySelectorAll('.progress-bar-custom').forEach(bar => {
                const width = bar.dataset.width;
                setTimeout(() => {
                    bar.style.width = width;
                }, 800);
            });

            // Animate circular progress
            document.querySelectorAll('.progress-circle').forEach(circle => {
                const progress = circle.dataset.progress;
                const circumference = 2 * Math.PI * 40;
                const offset = circumference - (progress / 100 * circumference);
                circle.style.strokeDasharray = circumference;
                circle.style.strokeDashoffset = offset;
            });
        }

        // Initialize interactive elements
        function initializeInteractions() {
            // Module card hover effects
            document.querySelectorAll('.enhanced-module').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        }

        // Smooth scroll for internal links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>