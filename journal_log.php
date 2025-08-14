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

// Get filter and sort parameters
$mood_filter = $_GET['mood'] ?? '';
$sort_by = $_GET['sort'] ?? 'date_desc';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';

try {
    // Base SQL query
    $sql = "SELECT id, mood, content, entry_date FROM journal_entries WHERE firebase_uid = :uid";
    $params = [':uid' => $firebase_uid];
    
    // Apply filters
    if (!empty($mood_filter)) {
        $sql .= " AND mood = :mood";
        $params[':mood'] = $mood_filter;
    }
    
    if (!empty($date_from)) {
        $sql .= " AND entry_date >= :date_from";
        $params[':date_from'] = $date_from;
    }
    
    if (!empty($date_to)) {
        $sql .= " AND entry_date <= :date_to";
        $params[':date_to'] = $date_to;
    }
    
    if (!empty($search)) {
        $sql .= " AND content LIKE :search";
        $params[':search'] = '%' . $search . '%';
    }
    
    // Apply sorting
    switch ($sort_by) {
        case 'date_asc':
            $sql .= " ORDER BY entry_date ASC";
            break;
        case 'date_desc':
        default:
            $sql .= " ORDER BY entry_date DESC";
            break;
        case 'mood':
            $sql .= " ORDER BY mood ASC, entry_date DESC";
            break;
        case 'content_length':
            $sql .= " ORDER BY LENGTH(content) DESC, entry_date DESC";
            break;
    }

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    $stmt->execute();

    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count for display
    $total_count = count($entries);
    
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

// Available moods for filter
$available_moods = ['happy', 'sad', 'neutral', 'angry', 'excited'];

$month_filter = $_GET['month'] ?? '';
$year_filter = $_GET['year'] ?? '';

// Update your SQL query to handle month/year filtering
if (!empty($month_filter) && !empty($year_filter)) {
    $sql .= " AND YEAR(entry_date) = :year AND MONTH(entry_date) = :month";
    $params[':year'] = $year_filter;
    $params[':month'] = $month_filter;
} elseif (!empty($year_filter)) {
    $sql .= " AND YEAR(entry_date) = :year";
    $params[':year'] = $year_filter;
} elseif (!empty($month_filter)) {
    $sql .= " AND MONTH(entry_date) = :month";
    $params[':month'] = $month_filter;
}

// Generate available years from your entries
$available_years = [];
try {
    $year_stmt = $pdo->prepare("SELECT DISTINCT YEAR(entry_date) as year FROM journal_entries WHERE firebase_uid = :uid ORDER BY year DESC");
    $year_stmt->bindValue(':uid', $firebase_uid);
    $year_stmt->execute();
    $available_years = $year_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Handle error
}

$months = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
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
        /* Compact Filter Section - Replace existing filter styles */

        .filter-section {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }

        .filter-section:hover {
            box-shadow: var(--shadow-hover);
        }

        /* Compact Filter Row */
        .compact-filter-row {
            display: grid;
            grid-template-columns: 1fr auto auto auto;
            gap: 1rem;
            align-items: end;
            margin-bottom: 1rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }

        .filter-label i {
            color: var(--journal-color);
            margin-right: 0.5rem;
            width: 16px;
            text-align: center;
        }

        /* Enhanced Form Controls */
        .filter-section .form-control, 
        .filter-section .form-select {
            background: rgba(255, 255, 255, 0.95);
            border: 2px solid rgba(139, 92, 246, 0.2);
            border-radius: 12px;
            padding: 10px 14px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
            font-weight: 500;
            min-width: 0;
        }

        .filter-section .form-control:focus, 
        .filter-section .form-select:focus {
            background: rgba(255, 255, 255, 1);
            border-color: var(--journal-color);
            box-shadow: 0 0 0 0.2rem rgba(139, 92, 246, 0.15);
            transform: translateY(-1px);
        }

        .filter-section .form-control::placeholder {
            color: var(--text-secondary);
            opacity: 0.8;
        }

        /* Month/Year Select Styling */
        .date-select-group {
            display: flex;
            gap: 0.5rem;
        }

        .date-select {
            min-width: 80px;
        }

        /* Search Input */
        .search-input {
            width: 100%;
            min-width: 200px;
        }

        /* Filter Button */
        .btn-filter {
            background: linear-gradient(135deg, var(--journal-color) 0%, #a855f7 100%);
            border: none;
            color: white;
            border-radius: 12px;
            padding: 10px 16px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 80px;
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(139, 92, 246, 0.3);
            background: linear-gradient(135deg, #7c3aed 0%, #9333ea 100%);
            color: white;
        }

        .btn-clear {
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid rgba(139, 92, 246, 0.2);
            color: var(--text-primary);
            border-radius: 12px;
            padding: 10px 16px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
            min-width: 80px;
        }

        .btn-clear:hover {
            background: rgba(255, 255, 255, 0.95);
            border-color: var(--journal-color);
            color: var(--journal-color);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.1);
        }

        /* Scrollable Mood Filter Bar */
        .mood-filter-container {
            position: relative;
        }

        .mood-filter-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }

        .mood-filter-label i {
            color: var(--journal-color);
            margin-right: 0.5rem;
        }

        .mood-scroll-container {
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            background: rgba(139, 92, 246, 0.05);
            border: 1px solid rgba(139, 92, 246, 0.1);
            padding: 0.75rem 0;
        }

        .mood-filter-chips {
            display: flex;
            gap: 0.75rem;
            padding: 0 1rem;
            overflow-x: auto;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE and Edge */
            scroll-behavior: smooth;
        }

        .mood-filter-chips::-webkit-scrollbar {
            display: none; /* Chrome, Safari, Opera */
        }

        /* Scroll Buttons */
        .scroll-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.95);
            border: 2px solid rgba(139, 92, 246, 0.2);
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 2;
            backdrop-filter: blur(5px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .scroll-btn:hover {
            background: var(--journal-color);
            color: white;
            border-color: var(--journal-color);
            transform: translateY(-50%) scale(1.1);
        }

        .scroll-btn-left {
            left: 5px;
        }

        .scroll-btn-right {
            right: 5px;
        }

        .scroll-btn i {
            font-size: 0.8rem;
        }

        /* Mood Chips */
        .mood-chip {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid rgba(139, 92, 246, 0.2);
            border-radius: 25px;
            padding: 0.6rem 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: var(--text-primary);
            font-size: 0.85rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            backdrop-filter: blur(5px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .mood-chip:hover {
            background: rgba(255, 255, 255, 1);
            border-color: var(--journal-color);
            color: var(--journal-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.15);
        }

        .mood-chip.active {
            background: linear-gradient(135deg, var(--journal-color) 0%, #a855f7 100%);
            color: white;
            border-color: var(--journal-color);
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
            transform: translateY(-1px);
        }

        .mood-chip.active:hover {
            background: linear-gradient(135deg, #7c3aed 0%, #9333ea 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4);
        }

        .mood-chip .mood-emoji {
            font-size: 1.1rem;
        }

        /* Enhanced Results Info */
        .results-info {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--journal-color);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            backdrop-filter: blur(10px);
        }

        .results-info i {
            color: var(--journal-color);
        }

        .results-info strong {
            color: var(--journal-color);
        }

        /* Active Filter Indicators */
        .active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
            padding: 0.75rem;
            background: rgba(139, 92, 246, 0.05);
            border-radius: 12px;
            border: 1px solid rgba(139, 92, 246, 0.1);
        }

        .filter-tag {
            background: var(--journal-color);
            color: white;
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-weight: 500;
        }

        .filter-tag i.remove {
            cursor: pointer;
            opacity: 0.8;
            transition: opacity 0.2s ease;
            margin-left: 0.25rem;
        }

        .filter-tag i.remove:hover {
            opacity: 1;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .compact-filter-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .date-select-group {
                justify-content: center;
            }
            
            .filter-actions {
                display: flex;
                justify-content: center;
                gap: 1rem;
            }
        }

        @media (max-width: 768px) {
            .filter-section {
                padding: 1rem;
            }
            
            .compact-filter-row {
                gap: 0.75rem;
            }
            
            .mood-chip {
                padding: 0.5rem 0.8rem;
                font-size: 0.8rem;
            }
            
            .scroll-btn {
                width: 30px;
                height: 30px;
            }
            
            .scroll-btn i {
                font-size: 0.7rem;
            }
        }

        @media (max-width: 576px) {
            .results-info .d-flex {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }
            
            .filter-actions {
                flex-direction: column;
            }
            
            .date-select-group {
                flex-direction: column;
                gap: 0.5rem;
            }
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

    <!-- Compact Filter Section -->
    <div class="filter-section animate-fade-in">
        <!-- Active Filters Display -->
        <?php if (!empty($mood_filter) || !empty($search) || !empty($month_filter) || !empty($year_filter)): ?>
            <div class="active-filters">
                <?php if (!empty($search)): ?>
                    <span class="filter-tag">
                        <i class="fas fa-search"></i>
                        "<?= htmlspecialchars(substr($search, 0, 20)) ?><?= strlen($search) > 20 ? '...' : '' ?>"
                        <i class="fas fa-times remove" onclick="clearFilter('search')" title="Remove search filter"></i>
                    </span>
                <?php endif; ?>
                
                <?php if (!empty($mood_filter)): ?>
                    <span class="filter-tag">
                        <?= $mood_emojis[$mood_filter] ?? '😐' ?>
                        <?= ucfirst($mood_filter) ?>
                        <i class="fas fa-times remove" onclick="clearFilter('mood')" title="Remove mood filter"></i>
                    </span>
                <?php endif; ?>
                
                <?php if (!empty($month_filter) || !empty($year_filter)): ?>
                    <span class="filter-tag">
                        <i class="fas fa-calendar"></i>
                        <?php if (!empty($month_filter) && !empty($year_filter)): ?>
                            <?= $months[$month_filter] ?> <?= $year_filter ?>
                        <?php elseif (!empty($month_filter)): ?>
                            <?= $months[$month_filter] ?> (All Years)
                        <?php else: ?>
                            Year <?= $year_filter ?>
                        <?php endif; ?>
                        <i class="fas fa-times remove" onclick="clearFilter('date')" title="Remove date filter"></i>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="GET" class="filter-form" id="filterForm">
            <div class="compact-filter-row">
                <!-- Search Input -->
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-search"></i>
                        Search Content
                    </label>
                    <input type="text" 
                        name="search" 
                        class="form-control search-input" 
                        placeholder="Search your journal entries..." 
                        value="<?= htmlspecialchars($search) ?>"
                        id="searchInput">
                </div>

                <!-- Month/Year Filter -->
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-calendar-alt"></i>
                        Period
                    </label>
                    <div class="date-select-group">
                        <select name="month" class="form-select date-select" id="monthSelect">
                            <option value="">All Months</option>
                            <?php foreach ($months as $num => $name): ?>
                                <option value="<?= $num ?>" <?= $month_filter == $num ? 'selected' : '' ?>>
                                    <?= $name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <select name="year" class="form-select date-select" id="yearSelect">
                            <option value="">All Years</option>
                            <?php foreach ($available_years as $year): ?>
                                <option value="<?= $year ?>" <?= $year_filter == $year ? 'selected' : '' ?>>
                                    <?= $year ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="filter-group">
                    <label class="filter-label" style="visibility: hidden;">Actions</label>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-filter" title="Apply Filters">
                            <i class="fas fa-filter"></i>
                        </button>
                        <a href="?" class="btn btn-clear" title="Clear All Filters">
                            <i class="fas fa-eraser"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <!-- Scrollable Mood Filter Bar -->
        <div class="mood-filter-container">
            <div class="mood-filter-label">
                <i class="fas fa-smile"></i>
                Filter by Mood
            </div>
            
            <div class="mood-scroll-container">
                <!-- Scroll Left Button -->
                <div class="scroll-btn scroll-btn-left" onclick="scrollMoods('left')" id="scrollLeft">
                    <i class="fas fa-chevron-left"></i>
                </div>
                
                <!-- Mood Chips -->
                <div class="mood-filter-chips" id="moodChips">
                    <a href="?<?= http_build_query(array_merge($_GET, ['mood' => ''])) ?>" 
                    class="mood-chip <?= empty($mood_filter) ? 'active' : '' ?>"
                    title="Show all moods">
                        <i class="fas fa-list"></i>
                        All Moods
                    </a>
                    <?php foreach ($available_moods as $mood): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['mood' => $mood])) ?>" 
                        class="mood-chip <?= $mood_filter === $mood ? 'active' : '' ?>"
                        title="Filter by <?= ucfirst($mood) ?> mood">
                            <span class="mood-emoji"><?= $mood_emojis[$mood] ?></span>
                            <?= ucfirst($mood) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                
                <!-- Scroll Right Button -->
                <div class="scroll-btn scroll-btn-right" onclick="scrollMoods('right')" id="scrollRight">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Results Info -->
    <div class="results-info animate-fade-in">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center gap-3">
                <div>
                    <i class="fas fa-list-ul me-2"></i>
                    <strong><?= $total_count ?></strong> 
                    journal entr<?= $total_count === 1 ? 'y' : 'ies' ?> found
                    <?php if (!empty($mood_filter) || !empty($search) || !empty($month_filter) || !empty($year_filter)): ?>
                        <span class="badge bg-primary ms-2">filtered</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="text-muted d-flex align-items-center">
                <i class="fas fa-sort me-2"></i>
                <select name="sort" class="form-select form-select-sm" style="width: auto; background: transparent; border: none;" onchange="updateSort(this.value)">
                    <option value="date_desc" <?= ($sort_by ?? 'date_desc') === 'date_desc' ? 'selected' : '' ?>>Newest First</option>
                    <option value="date_asc" <?= ($sort_by ?? '') === 'date_asc' ? 'selected' : '' ?>>Oldest First</option>
                    <option value="mood" <?= ($sort_by ?? '') === 'mood' ? 'selected' : '' ?>>By Mood</option>
                    <option value="content_length" <?= ($sort_by ?? '') === 'content_length' ? 'selected' : '' ?>>By Length</option>
                </select>
            </div>
        </div>
    </div>

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
                                    <?php
                                    $content = htmlspecialchars($entry['content']);
                                    if (!empty($search)) {
                                        // Highlight search terms
                                        $content = str_ireplace(
                                            htmlspecialchars($search), 
                                            '<mark>' . htmlspecialchars($search) . '</mark>', 
                                            $content
                                        );
                                    }
                                    echo nl2br(substr($content, 0, 150));
                                    ?>
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
                    <?php if (!empty($mood_filter) || !empty($search) || !empty($date_from) || !empty($date_to)): ?>
                        <h3>No entries match your filters</h3>
                        <p>Try adjusting your search criteria or clear all filters</p>
                        <a href="?" class="btn btn-journal me-3">
                            <i class="fas fa-times me-2"></i>Clear Filters
                        </a>
                    <?php else: ?>
                        <h3>No journal entries yet</h3>
                        <p>Start your journaling journey by creating your first entry</p>
                    <?php endif; ?>
                    <a href="journal_create.php" class="btn btn-journal">
                        <i class="fas fa-plus me-2"></i>Create<?= empty($entries) ? ' First' : ' New' ?> Entry
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="js/journal.js"></script>

</body>
</html>