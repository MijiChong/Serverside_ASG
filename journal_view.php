<?php
session_start();

if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}
require_once 'setting_loader.php';
require 'mysql.php';

$uid = $_SESSION['uid'];
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: journal_log.php");
    exit();
}

try {
    $sql = "SELECT * FROM journal_entries WHERE id = :id AND firebase_uid = :uid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id, 'uid' => $uid]);
    $entry = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = $e->getMessage();
    $entry = null;
}

// Mood emoji and color mapping
$mood_data = [
    'happy' => ['emoji' => '😊', 'color' => '#10b981', 'label' => 'Happy'],
    'excited' => ['emoji' => '🤩', 'color' => '#f59e0b', 'label' => 'Excited'],
    'neutral' => ['emoji' => '😐', 'color' => '#6b7280', 'label' => 'Neutral'],
    'sad' => ['emoji' => '😢', 'color' => '#3b82f6', 'label' => 'Sad'],
    'angry' => ['emoji' => '😠', 'color' => '#ef4444', 'label' => 'Angry']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyTrackDiary - View Journal Entry</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="navigation/navbar.css" rel="stylesheet">
    <link href="css/journal.css" rel="stylesheet">
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

    <div class="main-content">
        <div class="container">
            <!-- Header Section -->
            <div class="page-header animate-fade-in">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">
                            <i class="fas fa-book-open me-3"></i>Journal Entry
                        </h1>
                        <p class="page-subtitle">Your personal reflection</p>
                    </div>
                    <div>
                        <a href="journal_update.php?id=<?= $id ?>" class="btn btn-journal me-2">
                            <i class="fas fa-edit me-2"></i>Edit Entry
                        </a>
                        <a href="journal_log.php" class="btn btn-outline-journal">
                            <i class="fas fa-arrow-left me-2"></i>Back to Journal
                        </a>
                    </div>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger animate-fade-in">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($entry): ?>
                <!-- Journal Entry Display -->
                <div class="journal-view-container animate-fade-in">
                    <!-- Entry Header -->
                    <div class="entry-view-header">
                        <div class="entry-meta">
                            <div class="entry-date-large">
                                <i class="fas fa-calendar-alt me-3"></i>
                                <div>
                                    <div class="date-primary"><?= date('F d, Y', strtotime($entry['entry_date'])) ?></div>
                                    <div class="date-secondary"><?= date('l', strtotime($entry['entry_date'])) ?></div>
                                </div>
                            </div>
                            
                            <div class="entry-mood-large">
                                <?php $mood_info = $mood_data[$entry['mood']] ?? $mood_data['neutral']; ?>
                                <div class="mood-display" style="color: <?= $mood_info['color'] ?>">
                                    <span class="mood-emoji-large"><?= $mood_info['emoji'] ?></span>
                                    <div class="mood-label-large"><?= $mood_info['label'] ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Entry Content -->
                    <div class="entry-content-display">
                        <h4 class="content-title">
                            <i class="fas fa-heart me-2"></i>Your Thoughts
                        </h4>
                        <div class="content-text">
                            <?= nl2br(htmlspecialchars($entry['content'])) ?>
                        </div>
                    </div>

                    <!-- Entry Actions -->
                    <div class="entry-view-actions">
                        <div class="action-group">
                            <a href="journal_update.php?id=<?= $entry['id'] ?>" class="action-btn edit-btn">
                                <i class="fas fa-edit me-2"></i>Edit Entry
                            </a>
                            <a href="journal_delete.php?id=<?= $entry['id'] ?>" 
                               class="action-btn delete-btn"
                               onclick="return confirm('Are you sure you want to delete this entry? This action cannot be undone.')">
                                <i class="fas fa-trash me-2"></i>Delete Entry
                            </a>
                        </div>
                        
                        <div class="navigation-group">
                            <a href="journal_log.php" class="action-btn back-btn">
                                <i class="fas fa-list me-2"></i>All Entries
                            </a>
                            <a href="journal_create.php" class="action-btn new-btn">
                                <i class="fas fa-plus me-2"></i>New Entry
                            </a>
                        </div>
                    </div>

                    <!-- Entry Stats (Optional Enhancement) -->
                    <div class="entry-stats">
                        <div class="stat-item">
                            <i class="fas fa-calendar-plus me-2"></i>
                            <small class="text-muted">
                                Created: <?= isset($entry['created_at']) ? date('M d, Y g:i A', strtotime($entry['created_at'])) : 'Unknown' ?>
                            </small>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-align-left me-2"></i>
                            <small class="text-muted">
                                <?= str_word_count($entry['content']) ?> words, <?= strlen($entry['content']) ?> characters
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
</body>
</html>