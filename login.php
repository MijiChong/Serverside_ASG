<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyTrackDiary - Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script type="module" src="js/login.js" defer></script>
    <!-- Main CSS file -->
    <link href="css/login.css" rel="stylesheet">
    
</head>
<body>
    <div class="login-container">
        <div class="login-card animate-fade-in">
            <div class="brand-logo">
                <i class="fas fa-calendar-check"></i>
                <h1 class="brand-title">MyTrackDiary</h1>
                <p class="brand-subtitle">Manage your daily routine efficiently</p>
            </div>

            <form id="loginForm">
                <div class="input-group mb-3">
                    <span class="input-group-text">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                </div>

                <div class="input-group mb-4">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                </div>

                <div class="text-end mb-3">
                <a href="forgot_password.php" class="text-decoration-none text-primary">Forgot Password?</a>
                </div>


                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Login
                </button>
            </form>

            <div class="signup-link">
                Don't have an account? <a href="register.php">Sign up here</a>
            </div>
            
        </div>
    </div>
    <!-- Loading Spinner -->
    <div class="spinner-overlay" id="loadingSpinner">
        <div class="spinner"></div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>

