<?php
session_start();

if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

require 'mysql.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = $_SESSION['uid'];
    $mood = $_POST['mood'];
    $content = $_POST['content'];
    $entry_date = $_POST['entry_date'];

    try {
        $sql = "INSERT INTO journal_entries (firebase_uid, mood, content, entry_date) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$uid, $mood, $content, $entry_date]);

        header("Location: journal_log.php");
        exit();
    } catch (PDOException $e) {
        $error_message = "Error saving entry: " . $e->getMessage();
    }
}

// Set default date to today
$default_date = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyTrackDiary - New Journal Entry</title>
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
                            <i class="fas fa-pen me-3"></i>New Journal Entry
                        </h1>
                        <p class="page-subtitle">Capture your thoughts and emotions</p>
                    </div>
                    <a href="journal_log.php" class="btn btn-outline-journal">
                        <i class="fas fa-arrow-left me-2"></i>Back to Journal
                    </a>
                </div>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger animate-fade-in">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

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
                                   value="<?= $default_date ?>" 
                                   required>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <label for="mood" class="form-label">
                                <i class="fas fa-smile me-2"></i>How are you feeling?
                            </label>
                            <select id="mood" name="mood" class="form-select" required>
                                <option value="">Choose your mood...</option>
                                <option value="happy">😊 Happy</option>
                                <option value="excited">🤩 Excited</option>
                                <option value="neutral">😐 Neutral</option>
                                <option value="sad">😢 Sad</option>
                                <option value="angry">😠 Angry</option>
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
                                  placeholder="What's on your mind today? Share your thoughts, experiences, or reflections..."
                                  required></textarea>
                        <div class="form-text">
                            <i class="fas fa-lightbulb me-1"></i>
                            Tip: Be honest and detailed. This is your personal space to reflect and grow.
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-journal">
                            <i class="fas fa-save me-2"></i>Save Entry
                        </button>
                        <a href="journal_log.php" class="btn btn-outline-secondary ms-3">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // // Auto-resize textarea
        // const textarea = document.getElementById('content');
        // textarea.addEventListener('input', function() {
        //     this.style.height = 'auto';
        //     this.style.height = (this.scrollHeight) + 'px';
        // });

        // // Character counter (optional enhancement)
        // textarea.addEventListener('input', function() {
        //     const charCount = this.value.length;
        //     const maxChars = 5000;
            
        //     if (charCount > maxChars * 0.9) {
        //         this.classList.add('near-limit');
        //     } else {
        //         this.classList.remove('near-limit');
        //     }
        // });
    </script>
    <script src="js/create_journal.js"></script>
</body>
</html>