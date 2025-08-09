<?php
// settings-loader.php - Include this at the top of each page after session_start()

require_once 'mysql.php';

// Define gradient options (must match settings.php)
$gradients = [
    1 => 'linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%)',
    2 => 'linear-gradient(135deg, #74b9ff 0%, #0984e3 50%, #6c5ce7 100%)',
    3 => 'linear-gradient(135deg, #fd79a8 0%, #e84393 50%, #f39c12 100%)',
    4 => 'linear-gradient(135deg, #00b894 0%, #00a085 50%, #55a3ff 100%)',
    5 => 'linear-gradient(135deg, #3742fa 0%, #2f3542 50%, #70a1ff 100%)',
    6 => 'linear-gradient(135deg, #ff9ff3 0%, #f368e0 50%, #feca57 100%)',
    7 => 'linear-gradient(135deg, #2c2c54 0%, #40407a 50%, #706fd3 100%)',
    8 => 'linear-gradient(135deg, #ff6b6b 0%, #feca57 50%, #ff9ff3 100%)'
];

// Defaults
$user_background = 1; // default gradient
$user_mode = 0; // default light mode

if (isset($_SESSION['uid'])) {
    $firebase_uid = $_SESSION['uid'];

    try {
        // Priority 1: Check cookies first for fastest loading
        if (isset($_COOKIE['background_theme']) && isset($_COOKIE['mode'])) {
            $user_background = (int)$_COOKIE['background_theme'];
            $user_mode = (int)$_COOKIE['mode'];
        } else {
            // Priority 2: Fetch from database if no cookies
            $settings_stmt = $pdo->prepare("SELECT background_gradient, mode FROM settings WHERE firebase_uid = ?");
            $settings_stmt->execute([$firebase_uid]);

            if ($settings = $settings_stmt->fetch(PDO::FETCH_ASSOC)) {
                $user_background = (int)$settings['background_gradient'];
                $user_mode = (int)$settings['mode'];

                // Set cookies for future page loads
                setcookie('background_theme', $user_background, time() + (86400 * 30), "/");
                setcookie('mode', $user_mode, time() + (86400 * 30), "/");
            }
        }
    } catch (PDOException $e) {
        // Log error but keep defaults
        error_log("Settings loader error: " . $e->getMessage());
    }
}

// Validate gradient exists, fallback to default if not
if (!array_key_exists($user_background, $gradients)) {
    $user_background = 1;
}

// Set the selected gradient
$selected_gradient = $gradients[$user_background];

// Function to echo body class for dark mode
function getBodyClass() {
    global $user_mode;
    return ($user_mode == 1) ? 'class="dark-mode"' : '';
}

// Function to get CSS custom property for gradient
function getGradientCSS() {
    global $selected_gradient;
    return "--background-gradient: $selected_gradient;";
}

// Function to get current theme values (for debugging)
function getCurrentTheme() {
    global $user_background, $user_mode, $selected_gradient;
    return [
        'background_id' => $user_background,
        'mode' => $user_mode,
        'gradient' => $selected_gradient
    ];
}
?>