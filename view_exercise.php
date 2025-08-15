<?php
session_start();

if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

require 'mysql.php';

$uid = $_SESSION['uid'];
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: exercise.php"); // Redirect to exercise list
    exit();
}

try {
    $sql = "SELECT * FROM exercise_records WHERE exercise_id = :id AND firebase_uid = :uid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id, 'uid' => $uid]);
    $exercise = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = $e->getMessage();
    $exercise = null;
}

if (!$exercise) {
    header("Location: exercise.php?error=notfound");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyTrackDiary - Exercise Tracker</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Navigation CSS -->
    <link href="navigation/navbar.css" rel="stylesheet">

    <!-- Exercise CSS -->
    <link href="css/exercise.css" rel="stylesheet">

</head>

<body>
    <!-- Navigation Bar -->
    <?php include 'navigation/navbar.php'; ?>
    
    <div class="main-content">
        <div class="container">
            <!-- Header -->
            <div class="page-header animate-fade-in">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">
                            <i class="fas fa-dumbbell me-3"></i>Exercise Records
                        </h1>
                        <p class="page-subtitle">Your workout details</p>
                    </div>
                    <a href="exercise.php" class="btn btn-exercise">
                        <i class="fas fa-arrow-left me-2"></i>Back To Records
                    </a>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger animate-fade-in">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($exercise): ?>
                <!-- Exercise Entry Display -->
                <div class="exercise-view-container animate-fade-in">
                    <!-- Entry Header -->
                    <div class="entry-view-header">
                        <div class="entry-meta">
                            <div class="entry-date-large">
                                <i class="fas fa-calendar-alt me-3"></i>
                                <div>
                                    <div class="date-primary"><?= date('F d, Y', strtotime($exercise['exercise_date'])) ?></div>
                                    <div class="date-secondary"><?= date('l', strtotime($exercise['exercise_date'])) ?></div>
                                </div>
                            </div>                        
                        </div>
                    </div>

                    <!-- Entry Content -->
                    <div class="entry-content-display">
                        <h4 class="content-title">
                            <i class="fas fa-info-circle me-2"></i>Workout Details
                        </h4>

                        <!-- Row for Exercise Info -->
                        <div class="row mb-3 content-text">
                            <div class="col-md-3">
                                
                                 <p><strong>Exercise Type:</strong><br>
                                    <?php
                                    if ($exercise['exercise_type'] === 'Other') {
                                        echo htmlspecialchars("Other - {$exercise['custom_exercise_type']} (Intensity: {$exercise['exercise_intensity']})");
                                    } else {
                                        echo htmlspecialchars($exercise['exercise_type']);
                                    }
                                    ?>
                                </p>
                            </div>
                            <div class="col-md-3">
                                <p><strong>Weight (kg):</strong><br><?= htmlspecialchars($exercise['user_weight']) ?></p>
                            </div>
                            <div class="col-md-3">
                                <p><strong>Duration (min):</strong><br><?= htmlspecialchars($exercise['duration']) ?></p>
                            </div>
                            <div class="col-md-3">
                                <p><strong>Calories Burned (cal):</strong><br><?= htmlspecialchars($exercise['calories_burned']) ?></p>
                            </div>
                        </div>

                        <!-- Notes Section -->
                        <div class="mt-3 content-text">
                            <p><strong>Notes:</strong></p>
                            <p>
                                <?= !empty($exercise['notes']) 
                                    ? nl2br(htmlspecialchars($exercise['notes'])) 
                                    : '<em>You do not write a note for this exercise.</em>' ?>
                            </p>
                        </div>
                    </div>

                <!-- Entry Actions -->
                <div class="entry-view-actions">
                    <div class="action-group ms-auto">
                        <a href="update_exercise.php?id=<?= $exercise['exercise_id'] ?>" class="action-btn edit-btn">
                            <i class="fas fa-edit me-2"></i>Edit Record
                        </a>
                        <a href="delete_exercise.php?id=<?= $exercise['exercise_id'] ?>" 
                           class="action-btn delete-btn"
                           onclick="return confirm('Are you sure you want to delete this record? This action cannot be undone.')">
                            <i class="fas fa-trash me-2"></i>Delete Record
                        </a>
                    </div>
                </div>

                <!-- Entry Stats -->
                <div class="entry-stats">
                    <div class="stat-item">
                        <i class="fas fa-calendar-plus me-2"></i>
                        <small class="text-muted">
                            <?php if (!empty($exercise['updated_at']) && $exercise['updated_at'] !== $exercise['created_at']): ?>
                                Updated: <?= date('M d, Y g:i A', strtotime($exercise['updated_at'])) ?>
                            <?php else: ?>
                                Created: <?= date('M d, Y g:i A', strtotime($exercise['created_at'])) ?>
                            <?php endif; ?>
                        </small>
                    </div>
                    
                    <div class="stat-item">
                        <i class="fas fa-dumbbell me-2"></i>
                        <small class="text-muted">
                            <?= htmlspecialchars($exercise['duration']) ?> min, <?= htmlspecialchars($exercise['calories_burned']) ?> cal
                        </small>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Entry Not Found -->
            <div class="empty-state animate-fade-in">
                <div class="empty-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Record not found</h3>
                <p>The exercise record you're looking for doesn't exist or you don't have permission to access it.</p>
                <a href="exercise.php" class="btn btn-exercise">
                    <i class="fas fa-arrow-left me-2"></i>Back to Exercise
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="js/exercise.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

</body>
</html>