<style>
    :root {
        --primary-color: #4f46e5;
        --secondary-color: #06b6d4;
        --accent-color: #f59e0b;
        --success-color: #10b981;
    }

    .navbar {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .navbar-brand {
        font-weight: bold;
        color: var(--primary-color) !important;
        font-size: 1.5rem;
        transition: all 0.3s ease;
    }

    .navbar-brand:hover {
        transform: scale(1.05);
    }

    .navbar-brand i {
        margin-right: 10px;
        color: var(--accent-color);
    }

    .nav-link {
        color: #6b7280 !important;
        font-weight: 500;
        transition: all 0.3s ease;
        border-radius: 8px;
        margin: 0 5px;
        padding: 8px 15px !important;
    }

    .nav-link:hover {
        color: var(--primary-color) !important;
        background: rgba(79, 70, 229, 0.1);
        transform: translateY(-2px);
    }

    .nav-link.active {
        color: var(--primary-color) !important;
        background: rgba(79, 70, 229, 0.15);
        font-weight: 600;
    }

    .nav-link i {
        margin-right: 8px;
        width: 16px;
        text-align: center;
    }

    .user-profile {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .user-avatar:hover {
        transform: scale(1.1);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .dropdown-menu {
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        border-radius: 12px;
        padding: 10px;
        margin-top: 10px;
    }

    .dropdown-item {
        border-radius: 8px;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }

    .dropdown-item:hover {
        background: rgba(79, 70, 229, 0.1);
        color: var(--primary-color);
    }

    .dropdown-item i {
        width: 20px;
        text-align: center;
    }

    .navbar-toggler {
        border: none;
        padding: 4px 8px;
    }

    .navbar-toggler:focus {
        box-shadow: none;
    }

    /* Module specific icon colors */
    .exercise-icon { color: #ef4444; }
    .journal-icon { color: #8b5cf6; }
    .transaction-icon { color: #10b981; }
    .habit-icon { color: #f59e0b; }
    .dashboard-icon { color: #06b6d4; }

    /* Notification badge */
    .notification-badge {
        position: absolute;
        top: -2px;
        right: -2px;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }

    .nav-item {
        position: relative;
    }

    @media (max-width: 991px) {
        .navbar-nav {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .user-profile {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }
    }
</style>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <i class="fas fa-book-open"></i>
            MyTrackDiary
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>" href="dashboard.php">
                        <i class="fas fa-tachometer-alt dashboard-icon"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'exercise') ? 'active' : ''; ?>" href="exercise.php">
                        <i class="fas fa-heartbeat exercise-icon"></i>
                        Exercise
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'journal') ? 'active' : ''; ?>" href="journal_log.php">
                        <i class="fas fa-feather-alt journal-icon"></i>
                        Journal
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'transactions') ? 'active' : ''; ?>" href="transactions.php">
                        <i class="fas fa-coins transaction-icon"></i>
                        Transactions
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'habit') ? 'active' : ''; ?>" href="habit.php">
                        <i class="fas fa-seedling habit-icon"></i>
                        Habits
                    </a>
                </li>
            </ul>
            
            <div class="user-profile">
                <div class="user-avatar" data-bs-toggle="dropdown">
                    <?php
                    // Get user initial from session or default to 'S' for Student
                    $user_initial = isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 1)) : 'S';
                    echo $user_initial;
                    ?>
                </div>
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Welcome, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Student'; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php">
                            <i class="fas fa-user-circle"></i>My Profile
                        </a></li>
                        <li><a class="dropdown-item" href="settings.php">
                            <i class="fas fa-cog"></i>Settings
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i>Logout
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
// Add smooth scrolling for navigation links
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        // Add a subtle animation when clicking nav items
        this.style.transform = 'scale(0.95)';
        setTimeout(() => {
            this.style.transform = '';
        }, 150);
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
</script>
<?php
// Get current page name for active nav highlighting
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>