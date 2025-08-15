<?php
session_start();

if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}
//$uid = $_SESSION['uid']; // Pass UID to JS
$firebase_uid = $_SESSION['uid'];

require 'mysql.php';

try {
    $uid = $_SESSION['uid'];
    $sql = "SELECT exercise_id, exercise_type, exercise_date, duration, user_weight, calories_burned, notes
            FROM exercise_records
            WHERE firebase_uid = ?
            ORDER BY exercise_date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$uid]);
    $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $exercises = [];
    $error_message = "Error fetching exercise records: " . $e->getMessage();
}
?>

<?php
//Filtering logic
$sql = "SELECT * FROM exercise_records WHERE firebase_uid = :uid";
$params = ['uid' => $uid];

if (!empty($_GET['exercise_type'])) {
    $sql .= " AND exercise_type = :exercise_type";
    $params['exercise_type'] = $_GET['exercise_type'];
}

if (!empty($_GET['date_from'])) {
    $sql .= " AND exercise_date >= :date_from";
    $params['date_from'] = $_GET['date_from'];
}

if (!empty($_GET['date_to'])) {
    $sql .= " AND exercise_date <= :date_to";
    $params['date_to'] = $_GET['date_to'];
}

$sql .= " ORDER BY exercise_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            <!-- Header Section -->
            <div class="page-header animate-fade-in">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">
                            <i class="fas fa-dumbbell me-3"></i>Exercise Tracker
                        </h1>
                        <p class="page-subtitle">Track your workouts, set goals, and monitor your fitness progress</p>
                    </div>
                    <a href="add_exercise.php" class="btn btn-exercise">
                        <i class="fas fa-plus me-2"></i>New Exercise
                    </a>
                </div>
            </div>


            <!-- Exercise Records Section -->
            <div id="records" class="content-section active">
                <div class="records-table animate-fade-in">
                    <div class="table-header">
                        <h2><i class="fas fa-list" style="margin-right: 10px;"></i>Exercise Records</h2>
                        <div style="display: flex; gap: 10px;">
                            <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#filterModal">
                                <i class="fas fa-filter"></i>
                                Filter
                            </button>
                        </div>  
                    </div>
                    
                    <?php if (empty($exercises)): ?>
                        <div class="empty-state">
                            <i class="fas fa-dumbbell"></i>
                            <h3 style="color: #666;">No exercises recorded yet</h3>
                            <p style="color: #999;">Start by adding your first workout!</p>
                            <a href="add_exercise.php" class="btn btn-exercise">
                                <i class="fas fa-plus me-2"></i>Create First Records
                            </a>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Exercise</th>
                                    <th>Duration</th>
                                    <th>Weight</th>
                                    <th>Calories Burned</th>
                                    <th>Notes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <?php
                                function getExerciseIcon($name) {
                                    $icons = [
                                        'Jogging'        => 'fa-running',
                                        'Gym Workout'    => 'fa-dumbbell',
                                        'Cycling'        => 'fa-bicycle',
                                        'Swimming'       => 'fa-swimmer',
                                        'Yoga'           => 'fa-child',       
                                        'Basketball'     => 'fa-basketball-ball',
                                        'Football'       => 'fa-football-ball', 
                                        'Tennis'         => 'fa-table-tennis',  
                                        'Dancing'        => 'fa-music',         
                                        'Hiking'         => 'fa-hiking',
                                        'Walking'        => 'fa-walking',
                                        'Running'        => 'fa-running',
                                        'Badminton'      => 'fa-table-tennis',  
                                        'Volleyball'     => 'fa-volleyball-ball',
                                        'Boxing'         => 'fa-hand-rock',     
                                        'Martial Arts'   => 'fa-user-ninja',
                                        'Pilates'        => 'fa-child',   
                                        'Zumba'          => 'fa-music',         
                                        'CrossFit'       => 'fa-dumbbell',
                                        'Rock Climbing'  => 'fa-mountain',
                                        'Other'          => 'fa-question'
                                    ];
                                    return $icons[$name] ?? 'fa-dumbbell'; // default icon
                                }
                            ?>
                            <tbody>
                                <?php foreach ($exercises as $exercise): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y', strtotime($exercise['exercise_date'])); ?></td>
                                        <td>
                                            <i class="fas <?php echo getExerciseIcon($exercise['exercise_type']); ?>  exercise-icon"></i>
                                            <?php echo htmlspecialchars($exercise['exercise_type']); ?>
                                        </td>
                                        <td><?php echo $exercise['duration']; ?> min</td>
                                        <td><?php echo $exercise['user_weight']; ?> kg</td>
                                        <td><?php echo $exercise['calories_burned']; ?> cal</td>
                                        <td><?php echo htmlspecialchars(substr($exercise['notes'], 0, 50)); ?><?php echo strlen($exercise['notes']) > 50 ? '...' : ''; ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="view_exercise.php?id=<?php echo $exercise['exercise_id']; ?>" class="action-btn view-btn" title="View">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <a href="update_exercise.php?id=<?php echo $exercise['exercise_id']; ?>" class="action-btn edit-btn" title="Edit">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="delete_exercise.php?id=<?php echo $exercise['exercise_id']; ?>" class="action-btn delete-btn" title="Delete"
                                                onclick="return confirm('Are you sure you want to delete this exercise record?');">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Filter Modal -->
                <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="GET" action="">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="filterModalLabel">Filter Exercises</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                <div class="modal-body">
                                    
                                <!-- Exercise Type -->
                                <div class="mb-3">
                                    <label for="exercise_type" class="form-label">Exercise Type</label>
                                    <select class="form-control" name="exercise_type" id="exercise_type">
                                    <option value="">All</option>
                                    <option value="Jogging">Jogging</option>
                                    <option value="Gym">Gym Workout</option>
                                    <option value="Cycling">Cycling</option>
                                    <option value="Swimming">Swimming</option>
                                    <option value="Yoga">Yoga</option>
                                    <option value="Basketball">Basketball</option>
                                    <option value="Football">Football</option>
                                    <option value="Tennis">Tennis</option>
                                    <option value="Dancing">Dancing</option>
                                    <option value="Hiking">Hiking</option>
                                    <option value="Walking">Walking</option>
                                    <option value="Running">Running</option>
                                    <option value="Badminton">Badminton</option>
                                    <option value="Volleyball">Volleyball</option>
                                    <option value="Boxing">Boxing</option>
                                    <option value="Martial_arts">Martial Arts</option>
                                    <option value="Pilates">Pilates</option>
                                    <option value="Zumba">Zumba</option>
                                    <option value="Crossfit">CrossFit</option>
                                    <option value="Rock_climbing">Rock Climbing</option>
                                    <option value="Other">Other (Custom)</option>
                                    </select>
                                </div>

                                <!-- Date From -->
                                <div class="mb-3">
                                    <label for="date_from" class="form-label">From Date</label>
                                    <input type="date" class="form-control" name="date_from" id="date_from">
                                </div>

                                <!-- Date To -->
                                <div class="mb-3">
                                    <label for="date_to" class="form-label">To Date</label>
                                    <input type="date" class="form-control" name="date_to" id="date_to">
                                </div>
                                </div>
                                <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-success">Apply Filter</button>
                                <a href="exercise.php" class="btn btn-primary">Clear Filter</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>