<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyTrackDiary - Register</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Main CSS file -->
    <link href="css/register.css" rel="stylesheet">
    
</head>
<body>
    <div class="register-container">
        <div class="register-card animate-fade-in">
            <div class="brand-logo">
                <i class="fas fa-calendar-check"></i>
                <h1 class="brand-title">MyTrackDiary</h1>
                <p class="brand-subtitle">Start your daily routine management</p>
            </div>

            <form id="registerForm">
                <div class="input-group mb-3">
                    <span class="input-group-text">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" id="username" class="form-control" name="username" placeholder="Username" required minlength="3">
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" id="email" class="form-control" name="email" placeholder="Email Address" required>
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password"  class="form-control" name="password" id="password" placeholder="Password" required minlength="8">
                </div>
                <div class="password-strength" id="passwordStrength"></div>

                <div class="input-group mb-4">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" id="confirm_pass" class="form-control" name="confirm_password" placeholder="Confirm Password" required>
                </div>
                <div class="password-match" id="passwordMatch"></div>

                <button type="submit" id="submit" class="btn btn-primary" >
                    <i class="fas fa-user-plus me-2"></i>
                    Create Account
                </button>
            </form>

            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="js/register.js" defer> </script>
    <script>
        // password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            
            let strength = 0;
            let hasRequirements = true;
            
            // Check length (minimum 8 characters)
            if (password.length >= 8) strength++;
            else hasRequirements = false;
            
            // Check for lowercase letter
            if (password.match(/[a-z]/)) strength++;
            else hasRequirements = false;
            
            // Check for uppercase letter
            if (password.match(/[A-Z]/)) strength++;
            else hasRequirements = false;
            
            // Check for number
            if (password.match(/[0-9]/)) strength++;
            else hasRequirements = false;
            
            // Check for special character (bonus)
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            strengthBar.style.width = (strength * 20) + '%';
            
            if (strength < 3 || !hasRequirements) {
                strengthBar.className = 'password-strength strength-weak';
            } else if (strength < 4) {
                strengthBar.className = 'password-strength strength-medium';
            } else {
                strengthBar.className = 'password-strength strength-strong';
            }
        });

        // Password confirmation checker
        document.getElementById('confirm_pass').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirmPassword.length === 0) {
                matchDiv.textContent = '';
                return;
            }
            
            if (password === confirmPassword) {
                matchDiv.textContent = '✓ Passwords match';
                matchDiv.className = 'password-match match-success';
            } else {
                matchDiv.textContent = '✗ Passwords do not match';
                matchDiv.className = 'password-match match-error';
            }
        });

        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            // Check password requirements
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long!');
                return false;
            }
            
            if (!password.match(/[a-z]/)) {
                e.preventDefault();
                alert('Password must contain at least one lowercase letter!');
                return false;
            }
            
            if (!password.match(/[A-Z]/)) {
                e.preventDefault();
                alert('Password must contain at least one uppercase letter!');
                return false;
            }
            
            if (!password.match(/[0-9]/)) {
                e.preventDefault();
                alert('Password must contain at least one number!');
                return false;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
        });
    </script>
</body>
</html>
<?php
// You can add PHP logic here if needed
?>