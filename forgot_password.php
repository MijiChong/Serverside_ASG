<!-- forgot-password.php -->
<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>MyTrackDiary - Reset Password</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
  <link href="css/login.css" rel="stylesheet" />
  <script type="module" src="js/forgot_password.js" defer></script>
</head>
<body>
  <div class="login-container">
    <div class="login-card animate-fade-in">
      <div class="brand-logo">
        <i class="fas fa-key"></i>
        <h1 class="brand-title">Reset Password</h1>
        <p class="brand-subtitle">Verify your identity to receive a reset link</p>
      </div>

      <form id="verifyForm">
        <div class="input-group mb-3">
          <span class="input-group-text"><i class="fas fa-envelope"></i></span>
          <input type="email" id="email" class="form-control" placeholder="Email" required />
        </div>
        <div class="input-group mb-4">
          <span class="input-group-text"><i class="fas fa-user"></i></span>
          <input type="text" id="username" class="form-control" placeholder="Username" required />
        </div>
        <button type="submit" class="btn btn-warning w-100">
          <i class="fas fa-paper-plane me-2"></i>Send Reset Email
        </button>
      </form>

      <div class="login-link mt-4">
        <a href="login.php">← Back to Login</a>
      </div>
    </div>
  </div>

  <!-- Optional spinner overlay -->
  <div class="spinner-overlay" id="loadingSpinner" style="display:none;">
      <div class="spinner"></div>
  </div>
</body>
</html>
