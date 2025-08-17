<?php
session_start();

if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

require 'mysql.php';
require_once 'setting_loader.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $uid = $_SESSION['uid']; // Pass UID to JS
    if ($_POST['exercise_type'] === "Other") {
        $exercise_type = 'Other';
        $custom_exercise_type = trim($_POST['custom_exercise_type']);
        $exercise_intensity = trim($_POST['exercise_intensity']);
    } else {
        $exercise_type = $_POST['exercise_type'];
        $custom_exercise_type = null;
        $exercise_intensity = null;
    }
    $exercise_date = $_POST['exercise_date'];
    $duration = $_POST['duration'];
    $weight = $_POST['user_weight'];
    $calories_burned = round($_POST['calories_burned'], 2);
    $notes = $_POST['notes'];
    
    try{
        $sql = "INSERT INTO exercise_records (firebase_uid, exercise_type, exercise_date, duration, user_weight, calories_burned, notes, custom_exercise_type, exercise_intensity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$uid, $exercise_type, $exercise_date, $duration, $weight, $calories_burned, $notes, $custom_exercise_type, $exercise_intensity]);

        header("Location: exercise.php"); 
        exit();
    } catch (PDOException $e) {
        $error_message = "Error saving exercise: " . $e->getMessage();
    }
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
    <!-- Navigation Bar -->
    <?php include 'navigation/navbar.php'; ?>
    
    <div class="main-content">
        <div class="container">
            <!-- Header -->
            <div class="page-header animate-fade-in">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">
                            <i class="fas fa-dumbbell me-3"></i>Exercise Tracker
                        </h1>
                        <p class="page-subtitle">Track your workouts, set goals, and monitor your fitness progress</p>
                    </div>
                    <a href="exercise.php" class="btn btn-exercise">
                        <i class="fas fa-arrow-left me-2"></i>Back To Records
                    </a>
                </div>
            </div>

            <!-- Add Exercise Section -->
            <div id="add-exercise" class="content-section active">
                <div class="form-card animate-fade-in">
                    <h2 style="margin-bottom: 25px; color: #333; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-plus-circle" style="color: #ff6b6b;"></i>
                        Add New Exercise
                    </h2>
                    <form method="POST">
                        <div class="form-grid">
                            <div class="form-group">

                            <!-- Date -->
                                <label for="exercise_date">Date</label>
                                <input type="date" id="exercise_date" name="exercise_date" max="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <!-- Exercise Type -->
                            <div class="form-group">
                                <label for="exercise_type">Exercise Type</label>
                                <select id="exercise_type" name="exercise_type" required onchange="toggleCustomExercise()">
                                    <option value="">Select Exercise</option>
                                    <option value="Jogging">🏃‍♀️ Jogging</option>
                                    <option value="Gym Workout">🏋️‍♀️ Gym Workout</option>
                                    <option value="Cycling">🚴‍♀️ Cycling</option>
                                    <option value="Swimming">🏊‍♀️ Swimming</option>
                                    <option value="Yoga">🧘‍♀️ Yoga</option>
                                    <option value="Basketball">🏀 Basketball</option>
                                    <option value="Football">⚽ Football</option>
                                    <option value="Tennis">🎾 Tennis</option>
                                    <option value="Dancing">💃 Dancing</option>
                                    <option value="Hiking">🥾 Hiking</option>
                                    <option value="Walking">🚶‍♀️ Walking</option>
                                    <option value="Running">🏃‍♂️ Running</option>
                                    <option value="Badminton">🏸 Badminton</option>
                                    <option value="Volleyball">🏐 Volleyball</option>
                                    <option value="Boxing">🥊 Boxing</option>
                                    <option value="Martial Arts">🥋 Martial Arts</option>
                                    <option value="Pilates">🤸‍♀️ Pilates</option>
                                    <option value="Zumba">💃 Zumba</option>
                                    <option value="Crossfit">💪 CrossFit</option>
                                    <option value="Rock Climbing">🧗‍♀️ Rock Climbing</option>
                                    <option value="Other">❓ Other (Custom)</option>
                                </select>
                                
                                <!-- Custom Exercise Input (Hidden by default) -->
                                <div id="custom-exercise-group" class="form-group" style="display: none; margin-top: 15px;">
                                    <label for="custom_exercise_type">Custom Exercise Name</label>
                                    <input type="text" id="custom_exercise_type" name="custom_exercise_type" placeholder="e.g., Parkour, Skateboarding...">
                                    <small style="color: #666; font-size: 0.8rem; margin-top: 5px; display: block;">
                                        Enter the name of your custom exercise
                                    </small>
                                </div>
                                
                                <!-- Exercise Intensity Selector (Hidden by default) -->
                                <div id="intensity-group" class="form-group" style="display: none; margin-top: 15px;">
                                    <label for="exercise_intensity">Exercise Intensity</label>
                                    <select id="exercise_intensity" name="exercise_intensity">
                                        <option value="light">💡 Light Intensity (2-4 MET)</option>
                                        <option value="moderate" selected>⚡ Moderate Intensity (4-6 MET)</option>
                                        <option value="vigorous">🔥 Vigorous Intensity (6-8 MET)</option>
                                        <option value="very_vigorous">🚀 Very Vigorous (8+ MET)</option>
                                    </select>
                                    <!-- <small style="color: #666; font-size: 0.8rem; margin-top: 5px; display: block;">
                                        How intense was your workout? This helps calculate calories.
                                    </small> -->
                                </div>
                            </div>

                            <!-- Duration -->
                            <div class="form-group">
                                <label for="duration">Duration (minutes)</label>
                                <input type="number" id="duration" name="duration" min="1" placeholder="e.g., 30" required onchange="updateCaloriesField()">
                            </div>

                            <!-- Weight -->
                            <div class="form-group" style="grid-column: 1 / span 1;">
                                <label for="weight">Your Weight (kg)</label>
                                <input type="number" id="weight" name="user_weight" min="30" max="300" step="0.01" placeholder="e.g., 65" onchange="updateCaloriesField()">
                                <!-- <small style="color: #666; font-size: 0.8rem; margin-top: 5px; display: block;">
                                    Used for calorie calculation. You can change this anytime.
                                </small> -->
                            </div>
                            
                            <!-- Calories -->
                            <div class="form-group" style="grid-column: 2 / span 2;">
                                <label for="calories">
                                    Calories Burned (kcal)
                                    <span style="font-size: 0.8rem; color: #666; font-weight: normal;">
                                        (auto-calculated, you can modify)
                                    </span>
                                </label>
                                <div style="position: relative;">
                                    <input type="number" id="calories" name="calories_burned" min="0" step="0.01" placeholder="Auto-calculated..." required>
                                    <button type="button" id="recalculate-btn" class="btn-recalculate" title="Recalculate calories">
                                        <i class="fas fa-calculator"></i>
                                    </button>
                                </div>
                                <small id="calories-info" style="color: #666; font-size: 0.8rem; margin-top: 5px; display: block;">
                                    Based on your exercise intensity, weight and duration
                                </small>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="form-group">
                            <label for="notes">Notes (optional)</label>
                            <textarea id="notes" name="notes" rows="3" placeholder="How did you feel? Any specific details about the workout..."></textarea>
                        </div>

                        <div style="display: flex; gap: 15px; margin-top: 20px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Save Exercise
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="clearForm()">
                                <i class="fas fa-undo"></i>
                                Reset Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="js/exercise.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>