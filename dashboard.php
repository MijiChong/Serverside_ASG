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
    
    // === ANALYTICS DATA ===
    // Weekly mood trend
    $stmt = $pdo->prepare("
        SELECT DATE(entry_date) as date, mood, COUNT(*) as count 
        FROM journal_entries 
        WHERE firebase_uid = ? AND entry_date >= ? 
        GROUP BY DATE(entry_date), mood
        ORDER BY entry_date DESC
    ");
    $stmt->execute([$uid, $weekAgo]);
    $weeklyMoodData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Exercise intensity over time
    $stmt = $pdo->prepare("
        SELECT DATE(exercise_date) as date, AVG(calories_burned/duration) as intensity
        FROM exercise_records 
        WHERE firebase_uid = ? AND exercise_date >= ? AND duration > 0
        GROUP BY DATE(exercise_date)
        ORDER BY exercise_date DESC
    ");
    $stmt->execute([$uid, $weekAgo]);
    $exerciseIntensity = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Top spending categories this month
    $stmt = $pdo->prepare("
        SELECT c.category_name, c.color, SUM(t.amount) as total, COUNT(*) as transactions
        FROM transactions t
        JOIN categories c ON t.category_id = c.category_id
        WHERE t.firebase_uid = ? AND t.transaction_type = 'expense' 
        AND DATE_FORMAT(t.transaction_date, '%Y-%m') = ?
        GROUP BY c.category_id, c.category_name, c.color
        ORDER BY total DESC
        LIMIT 6
    ");
    $stmt->execute([$uid, $currentMonth]);
    $topCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
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
    $topCategories = [];
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    
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

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <!-- Enhanced Welcome Section -->
            <?php date_default_timezone_set('Asia/Kuala_Lumpur'); ?>
            <div class="welcome-section enhanced-welcome animate-fade-in">
                <div class="row align-items-center">
                    <div class="col-lg-8">
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
                    <div class="col-lg-4 text-end">
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
                
                <!-- Enhanced Stats Cards with Animations -->
                <div class="row stats-row">
                    <div class="col-lg-3 col-md-6 mb-3">
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
                    
                    <div class="col-lg-3 col-md-6 mb-3">
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
                    
                    <div class="col-lg-3 col-md-6 mb-3">
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
                    
                    <div class="col-lg-3 col-md-6 mb-3">
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
            </div>

            <!-- Enhanced Module Cards -->
            <div class="modules-section">
                <div class="row">
                    <div class="col-lg-6 col-md-6 mb-4">
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
                                <a href="exercise.php" class="module-btn primary-btn">
                                    <i class="fas fa-play me-2"></i>Start Workout
                                </a>
                                <a href="exercise.php?view=history" class="module-btn secondary-btn">
                                    <i class="fas fa-history me-2"></i>View History
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-6 mb-4">
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
                            
                            <?php if(!empty($weeklyMoodData)): ?>
                            <div class="module-stats">
                                <div class="mood-overview">
                                    <?php 
                                    $moodCounts = [];
                                    foreach($weeklyMoodData as $mood) {
                                        $moodCounts[$mood['mood']] = ($moodCounts[$mood['mood']] ?? 0) + $mood['count'];
                                    }
                                    arsort($moodCounts);
                                    $topMood = array_key_first($moodCounts);
                                    ?>
                                    <span class="dominant-mood">Most frequent: <strong><?php echo ucfirst($topMood); ?></strong></span>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="module-actions">
                                <a href="journal_log.php" class="module-btn primary-btn">
                                    <i class="fas fa-pen me-2"></i>Write Entry
                                </a>
                                <a href="journal_log.php?view=entries" class="module-btn secondary-btn">
                                    <i class="fas fa-book-open me-2"></i>View Entries
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-6 mb-4">
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

                    <div class="col-lg-6 col-md-6 mb-4">
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
                                <a href="habit.php" class="module-btn primary-btn">
                                    <i class="fas fa-tasks me-2"></i>View Habits
                                </a>
                                <a href="habit.php?action=add" class="module-btn secondary-btn">
                                    <i class="fas fa-plus me-2"></i>Add Habit
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Floating Panel -->
            <div class="quick-actions animate-fade-in">
                <h3><i class="fas fa-bolt me-2"></i>Quick Actions</h3>
                <a href="exercise.php" class="quick-btn">
                    <i class="fas fa-dumbbell me-1"></i>Log Exercise
                </a>
                <a href="journal_log.php" class="quick-btn">
                    <i class="fas fa-book me-1"></i>New Journal Entry
                </a>
                <a href="MoneyTracker_design.php" class="quick-btn">
                    <i class="fas fa-wallet me-1"></i>Add Transaction
                </a>
                <a href="habit.php" class="quick-btn">
                    <i class="fas fa-check me-1"></i>Check Habits
                </a>
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

            // Initialize progress bars and circles
            initializeProgressElements();
            
            // Initialize charts
            initializeCharts();
            
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

        // Initialize charts
        function initializeCharts() {
            // Category spending chart
            <?php if(!empty($topCategories)): ?>
            const categoryCtx = document.getElementById('categoryChart');
            if (categoryCtx) {
                new Chart(categoryCtx, {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo json_encode(array_column($topCategories, 'category_name')); ?>,
                        datasets: [{
                            data: <?php echo json_encode(array_column($topCategories, 'total')); ?>,
                            backgroundColor: <?php echo json_encode(array_column($topCategories, 'color')); ?>,
                            borderWidth: 0,
                            hoverBorderWidth: 4,
                            hoverBorderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20,
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ':  + context.parsed.toFixed(2);
                                    }
                                }
                            }
                        },
                        cutout: '60%'
                    }
                });
            }
            <?php endif; ?>

            // Mood trend chart
            <?php if(!empty($weeklyMoodData)): ?>
            const moodCtx = document.getElementById('moodTrendChart');
            if (moodCtx) {
                // Process mood data for line chart
                const moodColors = {
                    'happy': '#10b981',
                    'excited': '#f59e0b', 
                    'neutral': '#6b7280',
                    'sad': '#3b82f6',
                    'angry': '#ef4444'
                };
                
                const moodData = <?php echo json_encode($weeklyMoodData); ?>;
                const processedData = {};
                
                moodData.forEach(item => {
                    if (!processedData[item.date]) {
                        processedData[item.date] = {};
                    }
                    processedData[item.date][item.mood] = item.count;
                });

                new Chart(moodCtx, {
                    type: 'line',
                    data: {
                        labels: Object.keys(processedData).map(date => new Date(date).toLocaleDateString('en-US', {month: 'short', day: 'numeric'})),
                        datasets: Object.keys(moodColors).map(mood => ({
                            label: mood.charAt(0).toUpperCase() + mood.slice(1),
                            data: Object.keys(processedData).map(date => processedData[date][mood] || 0),
                            borderColor: moodColors[mood],
                            backgroundColor: moodColors[mood] + '20',
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }))
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }
            <?php endif; ?>
        }

        // Initialize interactive elements
        function initializeInteractions() {
            // Chart tabs functionality
            document.querySelectorAll('.chart-tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    const chartType = this.dataset.chart;
                    
                    // Update active tab
                    document.querySelectorAll('.chart-tab').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show corresponding chart
                    document.querySelectorAll('.chart-wrapper').forEach(wrapper => {
                        wrapper.classList.remove('active');
                    });
                    document.getElementById(chartType + 'Chart').classList.add('active');
                });
            });

            // Time filter functionality
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Here you would typically reload data for the selected period
                    const period = this.dataset.period;
                    console.log('Filter changed to:', period);
                });
            });

            // Floating Action Button
            const fab = document.getElementById('quickActionsFab');
            const fabMain = fab.querySelector('.fab-main');
            
            fabMain.addEventListener('click', function() {
                fab.classList.toggle('active');
            });

            // Close FAB when clicking outside
            document.addEventListener('click', function(e) {
                if (!fab.contains(e.target)) {
                    fab.classList.remove('active');
                }
            });

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