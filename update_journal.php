<?php
session_start();

if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

require 'mysql.php';

$uid = $_SESSION['uid'];
$id = $_GET['id'] ?? null;
$error_message = '';

if (!$id) {
    header("Location: log_journal.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mood = $_POST['mood'];
    $content = $_POST['content'];
    $entry_date = $_POST['entry_date'];

    try {
        $sql = "UPDATE journal_entries 
                SET mood = :mood, content = :content, entry_date = :entry_date 
                WHERE id = :id AND firebase_uid = :uid";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':mood' => $mood,
            ':content' => $content,
            ':entry_date' => $entry_date,
            ':id' => $id,
            ':uid' => $uid
        ]);

        header("Location: journal_log.php");
        exit();
    } catch (PDOException $e) {
        $error_message = "Error updating entry: " . $e->getMessage();
    }
}

// Load entry
try {
    $sql = "SELECT * FROM journal_entries WHERE id = :id AND firebase_uid = :uid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id, ':uid' => $uid]);
    $entry = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$entry) {
        header("Location: journal_log.php");
        exit();
    }
} catch (PDOException $e) {
    $error_message = "Error loading entry: " . $e->getMessage();
    $entry = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyTrackDiary - Edit Journal Entry</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="navigation/navbar.css" rel="stylesheet">
    <link href="css/journal.css" rel="stylesheet">
</head>
<body>
    <?php include 'navigation/navbar.php'; ?>

    <div class="main-content">
        <div class="container">
            <!-- Header Section -->
            <div class="page-header animate-fade-in">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">
                            <i class="fas fa-edit me-3"></i>Edit Journal Entry
                        </h1>
                        <p class="page-subtitle">Update your thoughts and reflections</p>
                    </div>
                    <div>
                        <a href="view_journal.php?id=<?= $id ?>" class="btn btn-outline-journal me-2">
                            <i class="fas fa-eye me-2"></i>View Entry
                        </a>
                        <a href="journal_log.php" class="btn btn-outline-journal">
                            <i class="fas fa-arrow-left me-2"></i>Back to Journal
                        </a>
                    </div>
                </div>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger animate-fade-in">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <?php if ($entry): ?>
                <!-- Journal Entry Form -->
                <div class="form-container animate-fade-in">
                    <form method="POST" id="journalForm">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="entry_date" class="form-label">
                                    <i class="fas fa-calendar-alt me-2"></i>Date
                                </label>
                                <input type="date" 
                                       id="entry_date" 
                                       name="entry_date" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($entry['entry_date']) ?>" 
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <label for="mood" class="form-label">
                                    <i class="fas fa-smile me-2"></i>How are you feeling?
                                </label>
                                <select id="mood" name="mood" class="form-select" required>
                                    <option value="">Choose your mood...</option>
                                    <?php 
                                    $moods = [
                                        'happy' => '😊 Happy',
                                        'excited' => '🤩 Excited',
                                        'neutral' => '😐 Neutral',
                                        'sad' => '😢 Sad',
                                        'angry' => '😠 Angry'
                                    ];
                                    foreach ($moods as $value => $label): 
                                    ?>
                                        <option value="<?= $value ?>" <?= $entry['mood'] === $value ? 'selected' : '' ?>>
                                            <?= $label ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="content" class="form-label">
                                <i class="fas fa-edit me-2"></i>Your thoughts
                            </label>
                            <textarea id="content" 
                                      name="content" 
                                      class="form-control" 
                                      rows="8" 
                                      placeholder="Share your thoughts, experiences, or reflections..."
                                      required><?= htmlspecialchars($entry['content']) ?></textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Last updated: <?= date('M d, Y \a\t g:i A', strtotime($entry['created_at'] ?? $entry['entry_date'])) ?>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-journal">
                                <i class="fas fa-save me-2"></i>Update Entry
                            </button>
                            <a href="view_journal.php?id=<?= $id ?>" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-eye me-2"></i>View
                            </a>
                            <a href="journal_log.php" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <a href="delete_journal.php?id=<?= $id ?>" 
                               class="btn btn-outline-danger ms-auto"
                               onclick="return confirm('Are you sure you want to delete this entry? This action cannot be undone.')">
                                <i class="fas fa-trash me-2"></i>Delete Entry
                            </a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="empty-state animate-fade-in">
                    <div class="empty-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3>Entry not found</h3>
                    <p>The journal entry you're looking for doesn't exist or you don't have permission to access it.</p>
                    <a href="journal_log.php" class="btn btn-journal">
                        <i class="fas fa-arrow-left me-2"></i>Back to Journal
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="js/update_journal.js"></script>
</body>
</html>