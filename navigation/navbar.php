<?php
// Get current page name for active nav highlighting
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Handle different page name mappings
if ($current_page == 'journal_log') {
    $current_page = 'journal';
} elseif ($current_page == 'MoneyTracker_design') {
    $current_page = 'transactions';
}

// Get user profile info for better personalization
$user_initial = 'U';
$user_name = 'User';
$notification_count = 0;

if (isset($_SESSION['uid'])) {
    try {
        // Get user profile
        $stmt = $pdo->prepare("SELECT first_name, display_name FROM personal_profile WHERE firebase_uid = ?");
        $stmt->execute([$_SESSION['uid']]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($profile) {
            $user_name = $profile['display_name'] ?: $profile['first_name'] ?: 'User';
            $user_initial = strtoupper(substr($user_name, 0, 1));
        } elseif (isset($_SESSION['username'])) {
            $user_name = $_SESSION['username'];
            $user_initial = strtoupper(substr($user_name, 0, 1));
        }
        
        // Count today's activities for notifications (example)
        $today = date('Y-m-d');
        $stmt = $pdo->prepare("
            SELECT 
                (SELECT COUNT(*) FROM exercise_records WHERE firebase_uid = ? AND exercise_date = ?) +
                (SELECT COUNT(*) FROM journal_entries WHERE firebase_uid = ? AND entry_date = ?) +
                (SELECT COUNT(*) FROM transactions WHERE firebase_uid = ? AND transaction_date = ?) as total_today
        ");
        $stmt->execute([$_SESSION['uid'], $today, $_SESSION['uid'], $today, $_SESSION['uid'], $today]);
        $today_activities = $stmt->fetch(PDO::FETCH_ASSOC)['total_today'] ?? 0;
        
        // Simple notification logic
        if ($today_activities == 0) {
            $notification_count = 1; // Reminder to start tracking
        }
        
    } catch(PDOException $e) {
        // Use defaults on error
    }
}
?>

<style>
    :root {
        --primary-color: #4f46e5;
        --secondary-color: #06b6d4;
        --accent-color: #f59e0b;
        --success-color: #10b981;
        --navbar-height: 70px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Enhanced Navbar */
    .navbar.enhanced-navbar {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        min-height: var(--navbar-height);
        transition: var(--transition);
        position: sticky;
        top: 0;
        z-index: 1030;
    }

    .navbar.enhanced-navbar.scrolled {
        background: rgba(255, 255, 255, 0.98);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    /* Brand Enhancement */
    .navbar-brand.enhanced-brand {
        font-weight: 800;
        color: var(--primary-color) !important;
        font-size: 1.6rem;
        transition: var(--transition);
        text-decoration: none;
        display: flex;
        align-items: center;
        letter-spacing: -0.025em;
    }

    .navbar-brand.enhanced-brand:hover {
        transform: scale(1.05);
        color: var(--secondary-color) !important;
    }

    .brand-icon {
        margin-right: 12px;
        color: var(--accent-color);
        font-size: 1.8rem;
        transition: var(--transition);
    }

    .navbar-brand.enhanced-brand:hover .brand-icon {
        transform: rotate(10deg);
        color: var(--primary-color);
    }

    /* Navigation Links */
    .navbar-nav.enhanced-nav {
        align-items: center;
        gap: 5px;
    }

    .nav-item.enhanced-nav-item {
        position: relative;
    }

    .nav-link.enhanced-nav-link {
        color: #64748b !important;
        font-weight: 600;
        transition: var(--transition);
        border-radius: 12px;
        margin: 0 3px;
        padding: 10px 18px !important;
        position: relative;
        display: flex;
        align-items: center;
        text-decoration: none;
        font-size: 0.95rem;
    }

    .nav-link.enhanced-nav-link:hover {
        color: var(--primary-color) !important;
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(79, 70, 229, 0.05));
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
    }

    .nav-link.enhanced-nav-link.active {
        color: var(--primary-color) !important;
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.15), rgba(79, 70, 229, 0.08));
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.2);
    }

    .nav-link.enhanced-nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 6px;
        height: 6px;
        background: var(--primary-color);
        border-radius: 50%;
        animation: pulse-dot 2s infinite;
    }

    @keyframes pulse-dot {
        0%, 100% { opacity: 1; transform: translateX(-50%) scale(1); }
        50% { opacity: 0.7; transform: translateX(-50%) scale(1.2); }
    }

    .nav-icon {
        margin-right: 8px;
        width: 18px;
        text-align: center;
        font-size: 1.1rem;
        transition: var(--transition);
    }

    /* Module specific icon colors with hover effects */
    .dashboard-icon { 
        color: #06b6d4; 
        text-shadow: 0 0 10px rgba(6, 182, 212, 0.3);
    }
    .exercise-icon { 
        color: #ef4444; 
        text-shadow: 0 0 10px rgba(239, 68, 68, 0.3);
    }
    .journal-icon { 
        color: #8b5cf6; 
        text-shadow: 0 0 10px rgba(139, 92, 246, 0.3);
    }
    .transaction-icon { 
        color: #10b981; 
        text-shadow: 0 0 10px rgba(16, 185, 129, 0.3);
    }
    .habit-icon { 
        color: #f59e0b; 
        text-shadow: 0 0 10px rgba(245, 158, 11, 0.3);
    }

    .nav-link.enhanced-nav-link:hover .nav-icon {
        transform: scale(1.2);
    }

    /* Notification Badge */
    .notification-badge {
        position: absolute;
        top: 5px;
        right: 5px;
        background: linear-gradient(135deg, #ef4444, #f87171);
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        animation: bounce-in 0.5s ease-out;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
    }

    @keyframes bounce-in {
        0% { transform: scale(0); }
        50% { transform: scale(1.3); }
        100% { transform: scale(1); }
    }

    /* Enhanced User Profile Section */
    .user-profile.enhanced-profile {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 5px;
    }

    .user-avatar.enhanced-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.2rem;
        transition: var(--transition);
        cursor: pointer;
        position: relative;
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
    }

    .user-avatar.enhanced-avatar:hover {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
    }

    .user-avatar.enhanced-avatar::after {
        content: '';
        position: absolute;
        inset: -2px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--accent-color), var(--success-color));
        z-index: -1;
        opacity: 0;
        transition: var(--transition);
    }

    .user-avatar.enhanced-avatar:hover::after {
        opacity: 1;
        animation: rotate-border 3s linear infinite;
    }

    @keyframes rotate-border {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .user-info {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .user-greeting {
        font-size: 0.8rem;
        color: #64748b;
        font-weight: 500;
        margin: 0;
    }

    .user-name {
        font-size: 0.95rem;
        color: #1f2937;
        font-weight: 700;
        margin: 0;
        max-width: 120px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Enhanced Dropdown */
    .dropdown-toggle.enhanced-dropdown {
        background: none;
        border: none;
        color: inherit;
        font-size: 0.9rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: var(--transition);
        text-decoration: none;
    }

    .dropdown-toggle.enhanced-dropdown:hover {
        color: var(--primary-color);
    }

    .dropdown-toggle.enhanced-dropdown::after {
        transition: var(--transition);
    }

    .dropdown-toggle.enhanced-dropdown:hover::after {
        transform: rotate(180deg);
    }

    .dropdown-menu.enhanced-dropdown-menu {
        border: none;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        border-radius: 16px;
        padding: 15px 8px;
        margin-top: 15px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        min-width: 220px;
    }

    .dropdown-item.enhanced-dropdown-item {
        border-radius: 12px;
        padding: 12px 18px;
        transition: var(--transition);
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #374151;
        text-decoration: none;
        margin-bottom: 4px;
    }

    .dropdown-item.enhanced-dropdown-item:hover {
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(79, 70, 229, 0.05));
        color: var(--primary-color);
        transform: translateX(5px);
    }

    .dropdown-item.enhanced-dropdown-item i {
        width: 20px;
        text-align: center;
        font-size: 1.1rem;
    }

    .dropdown-divider.enhanced-divider {
        margin: 12px 10px;
        border-color: rgba(0, 0, 0, 0.08);
    }

    /* Logout item special styling */
    .dropdown-item.logout-item {
        color: #ef4444;
        margin-top: 8px;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        padding-top: 16px;
    }

    .dropdown-item.logout-item:hover {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(239, 68, 68, 0.05));
        color: #dc2626;
    }

    /* Mobile Navbar Enhancements */
    .navbar-toggler.enhanced-toggler {
        border: none;
        padding: 8px 10px;
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(79, 70, 229, 0.05));
        border-radius: 12px;
        transition: var(--transition);
    }

    .navbar-toggler.enhanced-toggler:hover {
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.15), rgba(79, 70, 229, 0.08));
        transform: scale(1.05);
    }

    .navbar-toggler.enhanced-toggler:focus {
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
    }

    .navbar-toggler-icon.enhanced-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%234f46e5' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2.5' d='m4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        transition: var(--transition);
    }

    /* Mobile Responsive */
    @media (max-width: 991px) {
        .navbar-nav.enhanced-nav {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(0, 0, 0, 0.08);
            gap: 8px;
        }
        
        .nav-link.enhanced-nav-link {
            padding: 15px 20px !important;
            border-radius: 15px;
            margin: 2px 0;
        }
        
        .user-profile.enhanced-profile {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(0, 0, 0, 0.08);
            justify-content: center;
        }

        .user-info {
            align-items: center;
            text-align: center;
        }

        .user-name {
            max-width: none;
        }
    }

    @media (max-width: 576px) {
        .navbar.enhanced-navbar {
            padding: 10px 0;
        }
        
        .navbar-brand.enhanced-brand {
            font-size: 1.4rem;
        }
        
        .brand-icon {
            font-size: 1.6rem;
        }
        
        .user-avatar.enhanced-avatar {
            width: 40px;
            height: 40px;
            font-size: 1.1rem;
        }
    }

    /* Loading Animation for Page Transitions */
    .page-loading {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color), var(--accent-color));
        transform: translateX(-100%);
        animation: loading-bar 2s ease-in-out infinite;
        z-index: 9999;
    }

    @keyframes loading-bar {
        0%, 100% { transform: translateX(-100%); }
        50% { transform: translateX(100%); }
    }
</style>

<!-- Enhanced Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-light enhanced-navbar" id="mainNavbar">
    <div class="container">
        <a class="navbar-brand enhanced-brand" href="dashboard.php">
            <i class="fas fa-book-open brand-icon"></i>
            MyTrackDiary
        </a>
        
        <button class="navbar-toggler enhanced-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon enhanced-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav enhanced-nav me-auto">
                <li class="nav-item enhanced-nav-item">
                    <a class="nav-link enhanced-nav-link <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>" href="dashboard.php">
                        <i class="fas fa-tachometer-alt nav-icon dashboard-icon"></i>
                        <span>Dashboard</span>
                        <!-- <?php if($notification_count > 0 && $current_page == 'dashboard'): ?>
                            <span class="notification-badge"><?php echo $notification_count; ?></span>
                        <?php endif; ?> -->
                    </a>
                </li>
                <li class="nav-item enhanced-nav-item">
                    <a class="nav-link enhanced-nav-link <?php echo ($current_page == 'exercise') ? 'active' : ''; ?>" href="exercise.php">
                        <i class="fas fa-heartbeat nav-icon exercise-icon"></i>
                        <span>Exercise</span>
                    </a>
                </li>
                <li class="nav-item enhanced-nav-item">
                    <a class="nav-link enhanced-nav-link <?php echo ($current_page == 'journal') ? 'active' : ''; ?>" href="journal_log.php">
                        <i class="fas fa-feather-alt nav-icon journal-icon"></i>
                        <span>Journal</span>
                    </a>
                </li>
                <li class="nav-item enhanced-nav-item">
                    <a class="nav-link enhanced-nav-link <?php echo ($current_page == 'transactions') ? 'active' : ''; ?>" href="MoneyTracker_design.php">
                        <i class="fas fa-coins nav-icon transaction-icon"></i>
                        <span>Transactions</span>
                    </a>
                </li>
                <li class="nav-item enhanced-nav-item">
                    <a class="nav-link enhanced-nav-link <?php echo ($current_page == 'habit') ? 'active' : ''; ?>" href="habit.php">
                        <i class="fas fa-seedling nav-icon habit-icon"></i>
                        <span>Habits</span>
                    </a>
                </li>
            </ul>
            
            <div class="user-profile enhanced-profile">
                <div class="user-avatar enhanced-avatar" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php echo $user_initial; ?>
                </div>
                <div class="user-info d-none d-lg-block">
                    <p class="user-greeting">Welcome back,</p>
                    <p class="user-name"><?php echo htmlspecialchars($user_name); ?></p>
                </div>
                <div class="dropdown">
                    <button class="btn dropdown-toggle enhanced-dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="d-lg-none"><?php echo htmlspecialchars($user_name); ?></span>
                        <span class="d-none d-lg-inline">Options</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end enhanced-dropdown-menu">
                        <li>
                            <a class="dropdown-item enhanced-dropdown-item" href="profile.php">
                                <i class="fas fa-user-circle"></i>
                                <span>My Profile</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item enhanced-dropdown-item" href="setting.php">
                                <i class="fas fa-cog"></i>
                                <span>Settings</span>
                            </a>
                        </li>
                        
                        <li><hr class="dropdown-divider enhanced-divider"></li>
                        <li>
                            <a class="dropdown-item enhanced-dropdown-item logout-item" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Page Loading Indicator -->
<div class="page-loading d-none" id="pageLoader"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Navbar scroll effect
    const navbar = document.getElementById('mainNavbar');
    let scrolled = false;

    window.addEventListener('scroll', function() {
        if (window.scrollY > 50 && !scrolled) {
            navbar.classList.add('scrolled');
            scrolled = true;
        } else if (window.scrollY <= 50 && scrolled) {
            navbar.classList.remove('scrolled');
            scrolled = false;
        }
    });

    // Enhanced navigation animations
    document.querySelectorAll('.enhanced-nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            // Don't prevent default, just add visual feedback
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);

            // Show loading indicator for navigation
            showPageLoader();
        });
    });

    // Auto-close mobile menu when clicking a link
    document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
        link.addEventListener('click', function() {
            const navbarCollapse = document.querySelector('.navbar-collapse');
            if (navbarCollapse.classList.contains('show')) {
                const bsCollapse = new bootstrap.Collapse(navbarCollapse);
                bsCollapse.hide();
            }
        });
    });

    // Enhanced dropdown interactions
    const userAvatar = document.querySelector('.user-avatar.enhanced-avatar');
    const dropdownMenu = document.querySelector('.enhanced-dropdown-menu');

    if (userAvatar && dropdownMenu) {
        userAvatar.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });

        userAvatar.addEventListener('mouseleave', function() {
            if (!dropdownMenu.classList.contains('show')) {
                this.style.transform = '';
            }
        });
    }

    // Keyboard navigation support
    document.addEventListener('keydown', function(e) {
        if (e.altKey) {
            switch(e.key) {
                case '1': window.location.href = 'dashboard.php'; break;
                case '2': window.location.href = 'exercise.php'; break;
                case '3': window.location.href = 'journal_log.php'; break;
                case '4': window.location.href = 'MoneyTracker_design.php'; break;
                case '5': window.location.href = 'habit.php'; break;
            }
        }
    });

    // Update notification badge based on today's activities
    updateNotificationBadge();
});

// Show page loading indicator
function showPageLoader() {
    const loader = document.getElementById('pageLoader');
    if (loader) {
        loader.classList.remove('d-none');
        setTimeout(() => {
            loader.classList.add('d-none');
        }, 2000);
    }
}

// Update notification badge
function updateNotificationBadge() {
    // This would typically make an AJAX call to check for new notifications
    // For now, we'll use the PHP-generated count
    const badge = document.querySelector('.notification-badge');
    if (badge && badge.textContent === '0') {
        badge.style.display = 'none';
    }
}


// Add smooth transitions when navigating
window.addEventListener('beforeunload', function() {
    showPageLoader();
});

// Preload navigation pages for faster loading
const navLinks = ['dashboard.php', 'exercise.php', 'journal_log.php', 'MoneyTracker_design.php', 'habit.php'];
navLinks.forEach(link => {
    const linkElement = document.createElement('link');
    linkElement.rel = 'prefetch';
    linkElement.href = link;
    document.head.appendChild(linkElement);
});
</script>