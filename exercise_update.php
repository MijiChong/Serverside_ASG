<?php
session_start();

if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

require 'mysql.php';
require_once 'setting_loader.php';

$uid = $_SESSION['uid'];
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: exercise.php");
    exit();
}

$error = "";
$success = "";

// Retrieve existing data
try {
    $stmt = $pdo->prepare("SELECT * FROM exercise_records WHERE exercise_id = :id AND firebase_uid = :uid");
    $stmt->execute(['id' => $id, 'uid' => $uid]);
    $exercise = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$exercise) {
        $error = "Exercise not found or you don't have permission.";
    }
} catch (PDOException $e) {
    $error = $e->getMessage();
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exercise_type = $_POST['exercise_type'] ?? '';
    $weight = $_POST['user_weight'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $calories = round($_POST['calories_burned'], 2) ?? '';
    $notes = $_POST['notes'] ?? '';
    $custom_exercise_type = $_POST['custom_exercise_type'] ?? '';
    $exercise_intensity = $_POST['exercise_intensity'] ?? '';

    try {
        $update_sql = "UPDATE exercise_records 
                       SET exercise_type = :exercise_type, 
                            exercise_date = :exercise_date,                        
                           user_weight = :user_weight,
                           duration = :duration,
                           calories_burned = :calories_burned,
                           notes = :notes,
                           exercise_intensity = :exercise_intensity,
                           custom_exercise_type = :custom_exercise_type,
                           updated_at = NOW()
                       WHERE exercise_id = :id AND firebase_uid = :uid";
        $stmt = $pdo->prepare($update_sql);
        $stmt->execute([
            'exercise_type'   => $_POST['exercise_type'] ?? null,
            'exercise_date'   => $_POST['exercise_date'] ?? null,
            'user_weight'     => $_POST['user_weight'] ?? null,        // matches DB column
            'duration'        => $_POST['duration'] ?? null,
            'calories_burned' => $_POST['calories_burned'] ?? null,    // matches DB column
            'notes'           => $_POST['notes'] ?? null,
            'custom_exercise_type'=> $_POST['custom_exercise_type'] ?? null,
            'exercise_intensity' => $_POST['exercise_intensity'] ?? null,
            'id'              => $id,
            'uid'             => $uid
        ]);

        $success = "Exercise updated successfully!";
        // Redirect after save to avoid resubmission
        header("Location: exercise_view.php?id=" . $id);
        exit();
    } catch (PDOException $e) {
        $error = $e->getMessage();
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
                   
                    <form method="POST">
                        <div class="form-grid">

                            <!-- Date -->
                            <div class="form-group">
                                <label for="exercise_date">Date</label>
                                <input type="date" id="exercise_date" name="exercise_date" 
                                    value="<?php echo htmlspecialchars($exercise['exercise_date']); ?>" required>
                            </div>

                            <!-- Exercise Type -->
                            <div class="form-group">
                                <label for="exercise_type">Exercise Type</label>
                                <select id="exercise_type" name="exercise_type" required onchange="toggleCustomExercise()">
                                    <option value="">Select Exercise</option>
                                    <?php
                                    $types = [
                                        "Jogging" => "🏃‍♀️ Jogging",
                                        "Gym Workout" => "🏋️‍♀️ Gym Workout",
                                        "Cycling" => "🚴‍♀️ Cycling",
                                        "Swimming" => "🏊‍♀️ Swimming",
                                        "Yoga" => "🧘‍♀️ Yoga",
                                        "Basketball" => "🏀 Basketball",
                                        "Football" => "⚽ Football",
                                        "Tennis" => "🎾 Tennis",
                                        "Dancing" => "💃 Dancing",
                                        "Hiking" => "🥾 Hiking",
                                        "Walking" => "🚶‍♀️ Walking",
                                        "Running" => "🏃‍♂️ Running",
                                        "Badminton" => "🏸 Badminton",
                                        "Volleyball" => "🏐 Volleyball",
                                        "Boxing" => "🥊 Boxing",
                                        "Martial Arts" => "🥋 Martial Arts",
                                        "Pilates" => "🤸‍♀️ Pilates",
                                        "Zumba" => "💃 Zumba",
                                        "Crossfit" => "💪 CrossFit",
                                        "Rock Climbing" => "🧗‍♀️ Rock Climbing",
                                        "Other" => "❓ Other (Custom)"
                                    ];
                                    foreach ($types as $value => $label) {
                                        $selected = '';
                                        if ($value === "Other" && str_starts_with($exercise['exercise_type'], "Other")) {
                                            $selected = 'selected';
                                        } elseif ($exercise['exercise_type'] === $value) {
                                            $selected = 'selected';
                                        }

                                        echo "<option value=\"$value\" $selected>$label</option>";
                                    }
                                    ?>
                                </select>
                            
                                <!-- Custom Exercise (only for Other) -->
                                <div class="form-group" id="custom-exercise-group" style="<?= ($exercise['exercise_type'] == 'Other') ? '' : 'display:none; margin-top: 15px;' ?>">
                                    <label for="custom_exercise_type">Custom Exercise Type</label>
                                    <input type="text" id="custom_exercise_type" name="custom_exercise_type" 
                                        value="<?php echo htmlspecialchars($exercise['custom_exercise_type'] ?? ''); ?>" 
                                        placeholder="e.g., Parkour, Skateboarding...">
                                </div>

                                <!-- Intensity (only for Other) -->
                                <div class="form-group" id="intensity-group" style="<?= ($exercise['exercise_type'] == 'Other') ? '' : 'display:none; margin-top: 15px;' ?>">
                                    <label for="exercise_intensity">Intensity</label>
                                    <select id="exercise_intensity" name="exercise_intensity">
                                        <option value="light" <?php echo ($exercise['exercise_intensity'] === 'light') ? 'selected' : ''; ?>>💡 Light Intensity (2-4 MET)</option>
                                        <option value="moderate" <?php echo ($exercise['exercise_intensity'] === 'moderate') ? 'selected' : ''; ?>>⚡ Moderate Intensity (4-6 MET)</option>
                                        <option value="vigorous" <?php echo ($exercise['exercise_intensity'] === 'vigorous') ? 'selected' : ''; ?>>🔥 Vigorous Intensity (6-8 MET)</option>
                                        <option value="very_vigorous" <?php echo ($exercise['exercise_intensity'] === 'very_vigorous') ? 'selected' : ''; ?>>🚀 Very Vigorous (8+ MET)</option>
                                    </select>
                                </div>

                            </div>

                            <!-- Duration -->
                            <div class="form-group">
                                <label for="duration">Duration (minutes)</label>
                                <input type="number" id="duration" name="duration" 
                                    value="<?php echo htmlspecialchars($exercise['duration']); ?>" 
                                    min="1" required onchange="updateCaloriesField()">
                            </div>

                            <!-- Weight -->
                            <div class="form-group">
                                <label for="weight">Your Weight (kg)</label>
                                <input type="number" id="weight" name="user_weight" 
                                    value="<?php echo htmlspecialchars($exercise['user_weight']); ?>" 
                                    min="30" max="300" step="0.01" required onchange="updateCaloriesField()">
                            </div>

                            <!-- Calories -->
                            <div class="form-group" style="grid-column: 1 / span 2;">
                                <label for="calories">Calories Burned (kcal)
                                    <!-- <span style="font-size: 0.8rem; color: #666; font-weight: normal;">
                                        (Click the calculator button after making changes)
                                    </span> -->
                                </label>
                                <div style="position: relative;">
                                    <input type="number" id="calories" name="calories_burned" min="0" step="0.01" value="<?php echo htmlspecialchars($exercise['calories_burned']); ?>" required>
                                    <button type="button" id="recalculate-btn" class="btn-recalculate" title="Recalculate calories">
                                        <i class="fas fa-calculator"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="form-group">
                            <label for="notes">Notes (optional)</label>
                            <textarea id="notes" name="notes" rows="3"><?php echo htmlspecialchars($exercise['notes']); ?></textarea>
                        </div>

                        <div style="display: flex; gap: 15px; margin-top: 20px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <a href="exercise_view.php?id=<?php echo $id; ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
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