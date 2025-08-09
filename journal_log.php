<?php
session_start();

if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}
require 'mysql.php';
require_once 'setting_loader.php';

$firebase_uid = $_SESSION['uid'];
$entries = [];

try {
    $sql = "SELECT id, mood, content, entry_date FROM journal_entries WHERE firebase_uid = :uid ORDER BY entry_date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':uid', $firebase_uid, PDO::PARAM_STR);
    $stmt->execute();

    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = $e->getMessage();
}

// Mood emoji mapping
$mood_emojis = [
    'happy' => '😊',
    'sad' => '😢',
    'neutral' => '😐',
    'angry' => '😠',
    'excited' => '🤩'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyTrackDiary - Journal</title>
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
                            <i class="fas fa-book me-3"></i>Your Journal
                        </h1>
                        <p class="page-subtitle">Reflect on your thoughts and track your personal growth</p>
                    </div>
                    <a href="journal_create.php" class="btn btn-journal">
                        <i class="fas fa-plus me-2"></i>New Entry
                    </a>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger animate-fade-in">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Journal Entries -->
            <?php if (!empty($entries)): ?>
                <div class="row">
                    <?php foreach ($entries as $index => $entry): ?>
                        <div class="col-lg-6 col-md-12 mb-4">
                            <div class="journal-entry-card animate-fade-in" style="animation-delay: <?= $index * 0.1 ?>s;">
                                <div class="entry-header">
                                    <div class="entry-date">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        <?= date('M d, Y', strtotime($entry['entry_date'])) ?>
                                    </div>
                                    <div class="entry-mood">
                                        <span class="mood-emoji"><?= $mood_emojis[$entry['mood']] ?? '😐' ?></span>
                                        <span class="mood-text"><?= ucfirst(htmlspecialchars($entry['mood'])) ?></span>
                                    </div>
                                </div>
                                
                                <div class="entry-content">
                                    <?= nl2br(htmlspecialchars(substr($entry['content'], 0, 150))) ?>
                                    <?php if (strlen($entry['content']) > 150): ?>
                                        <span class="text-muted">...</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="entry-actions">
                                    <a href="journal_view.php?id=<?= $entry['id'] ?>" class="action-btn view-btn">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                    <a href="journal_update.php?id=<?= $entry['id'] ?>" class="action-btn edit-btn">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </a>
                                    <a href="journal_delete.php?id=<?= $entry['id'] ?>" 
                                       class="action-btn delete-btn"
                                       onclick="return confirm('Are you sure you want to delete this entry?')">
                                        <i class="fas fa-trash me-1"></i>Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state animate-fade-in">
                    <div class="empty-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h3>No journal entries yet</h3>
                    <p>Start your journaling journey by creating your first entry</p>
                    <a href="journal_create.php" class="btn btn-journal">
                        <i class="fas fa-plus me-2"></i>Create First Entry
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>