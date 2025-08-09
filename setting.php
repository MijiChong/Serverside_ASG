<?php
session_start();
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}

require_once 'mysql.php';

$success_message = '';
$error_message = '';

// Get Firebase UID from session
$firebase_uid = $_SESSION['uid'];

// Handle form submission - Check for POST method and required fields
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if we have the required fields (background_theme and mode)
    if (isset($_POST['background_theme']) && isset($_POST['mode'])) {
        $background_gradient = (int)$_POST['background_theme'];
        $mode = (int)$_POST['mode'];

        try {
            // Check if settings already exist for this user
            $check_stmt = $pdo->prepare("SELECT setting_id FROM settings WHERE firebase_uid = ?");
            $check_stmt->execute([$firebase_uid]);
            $existing_settings = $check_stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing_settings) {
                // Update existing settings
                $update_stmt = $pdo->prepare("UPDATE settings SET background_gradient = ?, mode = ? WHERE firebase_uid = ?");
                $result = $update_stmt->execute([$background_gradient, $mode, $firebase_uid]);
            } else {
                // Insert new settings
                $insert_stmt = $pdo->prepare("INSERT INTO settings (firebase_uid, background_gradient, mode) VALUES (?, ?, ?)");
                $result = $insert_stmt->execute([$firebase_uid, $background_gradient, $mode]);
            }

            if ($result) {
                // Update cookies for instant UI effect (30 days)
                setcookie('background_theme', $background_gradient, time() + (86400 * 30), "/");
                setcookie('mode', $mode, time() + (86400 * 30), "/");

                // Set cookies immediately for this request
                $_COOKIE['background_theme'] = $background_gradient;
                $_COOKIE['mode'] = $mode;

                $success_message = "Settings updated successfully!";
            } else {
                $error_message = "Failed to save settings. Please try again.";
            }
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    } else {
        $error_message = "Required fields are missing. Please try again.";
    }
}

// Get current settings from database
$current_background = 1; // default
$current_mode = 0; // default

try {
    $settings_stmt = $pdo->prepare("SELECT background_gradient, mode FROM settings WHERE firebase_uid = ?");
    $settings_stmt->execute([$firebase_uid]);
    $settings = $settings_stmt->fetch(PDO::FETCH_ASSOC);

    if ($settings) {
        $current_background = (int)$settings['background_gradient'];
        $current_mode = (int)$settings['mode'];
    }
} catch (PDOException $e) {
    // Keep defaults if error occurs
    error_log("Settings fetch error: " . $e->getMessage());
}

// Override with cookie values for immediate effect
if (isset($_COOKIE['background_theme'])) {
    $current_background = (int)$_COOKIE['background_theme'];
}
if (isset($_COOKIE['mode'])) {
    $current_mode = (int)$_COOKIE['mode'];
}

// Background gradients array
$gradients = [
    1 => ['name' => 'Default Purple', 'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%)'],
    2 => ['name' => 'Ocean Blue', 'gradient' => 'linear-gradient(135deg, #74b9ff 0%, #0984e3 50%, #6c5ce7 100%)'],
    3 => ['name' => 'Sunset Orange', 'gradient' => 'linear-gradient(135deg, #fd79a8 0%, #e84393 50%, #f39c12 100%)'],
    4 => ['name' => 'Forest Green', 'gradient' => 'linear-gradient(135deg, #00b894 0%, #00a085 50%, #55a3ff 100%)'],
    5 => ['name' => 'Royal Blue', 'gradient' => 'linear-gradient(135deg, #3742fa 0%, #2f3542 50%, #70a1ff 100%)'],
    6 => ['name' => 'Pink Dream', 'gradient' => 'linear-gradient(135deg, #ff9ff3 0%, #f368e0 50%, #feca57 100%)'],
    7 => ['name' => 'Dark Night', 'gradient' => 'linear-gradient(135deg, #2c2c54 0%, #40407a 50%, #706fd3 100%)'],
    8 => ['name' => 'Warm Sunset', 'gradient' => 'linear-gradient(135deg, #ff6b6b 0%, #feca57 50%, #ff9ff3 100%)']
];

$current_page = 'setting';
$selected_gradient = $gradients[$current_background]['gradient'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyTrackDiary - Settings</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Navigation CSS -->
    <link href="navigation/navbar.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #06b6d4;
            --accent-color: #f59e0b;
            --success-color: #10b981;
            --exercise-color: #ef4444;
            --journal-color: #8b5cf6;
            --transaction-color: #10b981;
            --habit-color: #f59e0b;
            <?php 
            $selected_gradient = $gradients[$current_background]['gradient'];
            echo "--background-gradient: $selected_gradient;";
            ?>
        }

        body {
            background: var(--background-gradient);
            background-attachment: fixed;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        /* Dark mode styles */
        body.dark-mode {
            filter: invert(1) hue-rotate(180deg);
        }

        body.dark-mode img,
        body.dark-mode .user-avatar,
        body.dark-mode .gradient-preview {
            filter: invert(1) hue-rotate(180deg);
        }

        .main-content {
            padding: 30px 0;
        }

        .settings-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeIn 0.8s ease-in;
        }

        .settings-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 15px;
            text-align: center;
        }

        .settings-subtitle {
            color: #6b7280;
            font-size: 1.1rem;
            margin-bottom: 40px;
            text-align: center;
        }

        .setting-section {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .setting-section h4 {
            color: var(--primary-color);
            font-weight: bold;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .gradient-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .gradient-option {
            position: relative;
            border-radius: 15px;
            height: 120px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 3px solid transparent;
            overflow: hidden;
        }

        .gradient-option:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .gradient-option.selected {
            border-color: var(--primary-color);
            box-shadow: 0 0 20px rgba(79, 70, 229, 0.3);
        }

        .gradient-preview {
            width: 100%;
            height: 100%;
            border-radius: 12px;
        }

        .gradient-label {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.9);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #333;
        }

        .mode-toggle-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .mode-option {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 20px;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 3px solid transparent;
            background: rgba(255, 255, 255, 0.5);
        }

        .mode-option:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .mode-option.selected {
            border-color: var(--primary-color);
            background: rgba(79, 70, 229, 0.1);
        }

        .mode-icon {
            font-size: 2rem;
            color: var(--primary-color);
        }

        .mode-label {
            font-weight: 600;
            color: #333;
        }

        .save-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 15px;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: block;
            margin: 30px auto 0;
        }

        .save-btn:hover {
            background: #4338ca;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(67, 56, 202, 0.3);
        }

        .alert {
            border-radius: 15px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-left: 4px solid #ef4444;
        }

        /* Debug section styling */
        .debug-section {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-family: monospace;
            font-size: 12px;
            line-height: 1.4;
            display: none; /* Hidden by default */
        }

        @keyframes fadeIn {
            from { 
                opacity: 0; 
                transform: translateY(20px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }

        @media (max-width: 768px) {
            .settings-container {
                padding: 20px;
            }
            
            .settings-title {
                font-size: 2rem;
            }
            
            .gradient-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .mode-toggle-container {
                flex-direction: column;
                gap: 15px;
            }
        }

        /* Real-time preview */
        .preview-container {
            background: var(--background-gradient);
            border-radius: 15px;
            height: 100px;
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body <?php echo ($current_mode == 1) ? 'class="dark-mode"' : ''; ?>>
    <!-- Navigation Bar -->
    <?php include 'navigation/navbar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="settings-container">
                <h1 class="settings-title"><i class="fas fa-cog me-3"></i>Settings</h1>
                <p class="settings-subtitle">
                    Customize your MyTrackDiary experience with personalized themes and display preferences.
                </p>

                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="settingsForm">
                    <!-- Background Theme Section -->
                    <div class="setting-section">
                        <h4><i class="fas fa-palette"></i>Background Theme</h4>
                        <p class="text-muted">Choose a background gradient that reflects your style</p>
                        
                        <div class="gradient-grid">
                            <?php foreach ($gradients as $id => $gradient): ?>
                                <label class="gradient-option <?php echo ($current_background == $id) ? 'selected' : ''; ?>">
                                    <input type="radio" name="background_theme" value="<?php echo $id; ?>" 
                                           <?php echo ($current_background == $id) ? 'checked' : ''; ?> 
                                           style="display: none;">
                                    <div class="gradient-preview" style="background: <?php echo $gradient['gradient']; ?>"></div>
                                    <div class="gradient-label"><?php echo $gradient['name']; ?></div>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <!-- Real-time Preview -->
                        <div class="preview-container">
                            <i class="fas fa-eye me-2"></i>Live Preview
                        </div>
                    </div>

                    <!-- Display Mode Section -->
                    <div class="setting-section">
                        <h4><i class="fas fa-moon"></i>Display Mode</h4>
                        <p class="text-muted">Switch between light and dark modes for comfortable viewing</p>
                        
                        <div class="mode-toggle-container">
                            <label class="mode-option <?php echo ($current_mode == 0) ? 'selected' : ''; ?>">
                                <input type="radio" name="mode" value="0" 
                                       <?php echo ($current_mode == 0) ? 'checked' : ''; ?> 
                                       style="display: none;">
                                <i class="fas fa-sun mode-icon"></i>
                                <span class="mode-label">Light Mode</span>
                            </label>
                            
                            <label class="mode-option <?php echo ($current_mode == 1) ? 'selected' : ''; ?>">
                                <input type="radio" name="mode" value="1" 
                                       <?php echo ($current_mode == 1) ? 'checked' : ''; ?> 
                                       style="display: none;">
                                <i class="fas fa-moon mode-icon"></i>
                                <span class="mode-label">Dark Mode</span>
                            </label>
                        </div>
                    </div>

                    <!-- FIXED: Added the hidden input field for update_settings -->
                    <input type="hidden" name="update_settings" value="1">
                    
                    <button type="submit" class="save-btn">
                        <i class="fas fa-save me-2"></i>Save Settings
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Real-time preview functionality
        const gradientOptions = document.querySelectorAll('input[name="background_theme"]');
        const modeOptions = document.querySelectorAll('input[name="mode"]');
        const previewContainer = document.querySelector('.preview-container');
        const body = document.body;
        
        const gradients = {
            <?php foreach ($gradients as $id => $gradient): ?>
            <?php echo $id; ?>: '<?php echo $gradient['gradient']; ?>',
            <?php endforeach; ?>
        };

        // Update gradient preview
        gradientOptions.forEach(option => {
            option.addEventListener('change', function() {
                // Update selection visual
                document.querySelectorAll('.gradient-option').forEach(opt => opt.classList.remove('selected'));
                this.closest('.gradient-option').classList.add('selected');
                
                // Update live preview
                const selectedGradient = gradients[this.value];
                previewContainer.style.background = selectedGradient;
                
                // Apply to body for real-time effect
                document.documentElement.style.setProperty('--background-gradient', selectedGradient);
            });
        });

        // Update mode preview
        modeOptions.forEach(option => {
            option.addEventListener('change', function() {
                // Update selection visual
                document.querySelectorAll('.mode-option').forEach(opt => opt.classList.remove('selected'));
                this.closest('.mode-option').classList.add('selected');
                
                // Apply dark mode toggle
                if (this.value == '1') {
                    body.classList.add('dark-mode');
                } else {
                    body.classList.remove('dark-mode');
                }
            });
        });

        // Form submission with loading state
        document.getElementById('settingsForm').addEventListener('submit', function(e) {
            const saveBtn = this.querySelector('.save-btn');
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
            saveBtn.disabled = true;
        });

        // Smooth animations for options
        document.querySelectorAll('.gradient-option, .mode-option').forEach(option => {
            option.addEventListener('click', function() {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });
    </script>
</body>
</html>