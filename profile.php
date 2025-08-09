<?php
// profile.php
session_start();
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}
$uid = $_SESSION['uid'];
$current_page = 'profile'; // For navbar active state
require_once 'setting_loader.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyTrackDiary - Profile</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Navigation CSS -->
    <link href="navigation/navbar.css" rel="stylesheet">
    
    <!-- Profile CSS -->
    <link href="css/profile.css" rel="stylesheet">

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

    <!-- Firebase SDK -->
    <script type="module">
        import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.7.0/firebase-app.js';
        import { getFirestore } from 'https://www.gstatic.com/firebasejs/10.7.0/firebase-firestore.js';
        
        const firebaseConfig = {
            apiKey: "AIzaSyBq_7VkBVj7zTJJWTltVcOk6KIU4Z6kVfE",
            authDomain: "serverside-39d3b.firebaseapp.com",
            projectId: "serverside-39d3b",
            storageBucket: "serverside-39d3b.firebasestorage.app",
            messagingSenderId: "258572801740",
            appId: "1:258572801740:web:e96cfc7baefe9b11da1469",
            measurementId: "G-5NDQKLEGST"
        };
        
        const app = initializeApp(firebaseConfig);
        const db = getFirestore(app);
        window.firestore = db;
    </script>

    <script>
        const FIREBASE_UID = "<?= htmlspecialchars($uid); ?>";
    </script>
</head>
<body <?php echo getBodyClass(); ?>>
    <!-- Navigation Bar -->
    <?php include 'navigation/navbar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <!-- Welcome Section -->
            <div class="welcome-section animate-fade-in">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center">
                        <div class="profile-avatar-container">
                            <div id="avatarPreview" class="profile-avatar gradient-group-1" onclick="openAvatarModal()">
                                <span class="avatar-text">U</span>
                                <div class="avatar-overlay">
                                    <i class="fas fa-camera"></i>
                                    <span class="overlay-text">Change Avatar</span>
                                </div>
                            </div>
                            <input type="hidden" name="gradient" id="selectedGradient" value="1">
                        </div>
                    </div>
                    <div class="col-md-9">
                        <h1 class="welcome-title"><i class="fas fa-user-circle me-2"></i>My Profile</h1>
                        <p class="welcome-subtitle">
                            Manage your account information and preferences to personalize your MyTrackDiary experience.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Profile Form Card -->
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="module-card animate-fade-in">
                        <form id="profileForm">
                            <div class="form-section">
                                <h3 class="module-title"><i class="fas fa-address-card me-2"></i>Personal Information</h3>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="username" name="username" placeholder=" " readonly>
                                            <label for="username"><i class="fas fa-user-tag me-1"></i>Username</label>
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>Username cannot be changed
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="email" class="form-control" id="email" name="email" placeholder=" " readonly>
                                            <label for="email"><i class="fas fa-envelope me-1"></i>Email</label>
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>Email cannot be changed
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="firstName" name="firstName" placeholder=" ">
                                            <label for="firstName"><i class="fas fa-user me-1"></i>First Name</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="lastName" name="lastName" placeholder=" ">
                                            <label for="lastName"><i class="fas fa-user me-1"></i>Last Name</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="date" class="form-control" id="dob" name="dob" placeholder=" ">
                                            <label for="dob"><i class="fas fa-calendar me-1"></i>Date of Birth</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="tel" class="form-control" id="phone" name="phone" placeholder=" ">
                                            <label for="phone"><i class="fas fa-phone me-1"></i>Phone Number</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-floating mb-3" id="addressFloatingContainer">
                                    <textarea class="form-control" id="address" name="address" placeholder=" " style="height: 100px; padding-top: 1.625rem !important; padding-bottom: 0.625rem !important;"></textarea>
                                    <label for="address" id="addressLabel" style="position: absolute; top: 0; left: 0; height: 100%; padding: 1.625rem 0.75rem 0.625rem; pointer-events: none; border: 2px solid transparent; transform-origin: 0 0; transition: opacity 0.15s ease-in-out, transform 0.15s ease-in-out; color: #6b7280; font-weight: 500; font-size: 1rem; display: flex; align-items: flex-start; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; z-index: 2;"><i class="fas fa-map-marker-alt me-1"></i>Address</label>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="module-btn save-btn">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                                <button type="button" class="module-btn reset-btn" onclick="resetForm()">
                                    <i class="fas fa-undo me-2"></i>Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading overlay -->
    <div id="loadingOverlay" class="loading-overlay" style="display: none;">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p>Loading profile data...</p>
        </div>
    </div>

    <!-- Avatar Modal -->
    <div class="modal fade" id="avatarModal" tabindex="-1" aria-labelledby="avatarModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="avatarModalLabel">
                        <i class="fas fa-palette me-2"></i>Choose Your Avatar Color
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-center text-muted mb-4">Select a gradient color for your avatar</p>
                    <div class="gradient-selection d-flex flex-wrap justify-content-center gap-3">
                        <?php 
                        $gradientNames = [
                            1 => 'Purple Ocean',
                            2 => 'Pink Sunset', 
                            3 => 'Blue Sky',
                            4 => 'Green Forest',
                            5 => 'Orange Glow',
                            6 => 'Soft Mint'
                        ];
                        for ($i = 1; $i <= 6; $i++): ?>
                            <div class="gradient-container text-center">
                                <div class="gradient-option gradient-group-<?= $i ?>" 
                                     data-gradient="<?= $i ?>" 
                                     onclick="selectGradient(<?= $i ?>)"
                                     title="<?= $gradientNames[$i] ?>">
                                    <span class="gradient-letter">
                                        <?= substr($gradientNames[$i], 0, 1) ?>
                                    </span>
                                </div>
                                <small class="gradient-name"><?= $gradientNames[$i] ?></small>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <div class="preview-section mt-4 text-center">
                        <p class="mb-2"><strong>Preview:</strong></p>
                        <div id="previewAvatar" class="preview-avatar gradient-group-1">
                            <span class="avatar-text">U</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-primary" onclick="applyAvatarSelection()">
                        <i class="fas fa-check me-1"></i>Apply Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="js/profile.js"></script>
    <script src="js/avatar.js"></script>

    <!-- Enhanced Floating Label Support Script -->
    <script>
        // Ensure all form inputs have proper attributes for floating labels
        document.addEventListener('DOMContentLoaded', function() {
            const floatingInputs = document.querySelectorAll('.form-floating .form-control, .form-floating .form-select');
            
            floatingInputs.forEach(input => {
                // Ensure placeholder attribute exists (required for Bootstrap floating labels)
                if (!input.hasAttribute('placeholder') || input.getAttribute('placeholder') === '') {
                    input.setAttribute('placeholder', ' ');
                }
                
                // Add autocomplete attributes for better UX
                if (input.id === 'email') {
                    input.setAttribute('autocomplete', 'email');
                }
                if (input.id === 'firstName') {
                    input.setAttribute('autocomplete', 'given-name');
                }
                if (input.id === 'lastName') {
                    input.setAttribute('autocomplete', 'family-name');
                }
                if (input.id === 'phone') {
                    input.setAttribute('autocomplete', 'tel');
                }
                if (input.id === 'address') {
                    input.setAttribute('autocomplete', 'street-address');
                }
                if (input.id === 'dob') {
                    input.setAttribute('autocomplete', 'bday');
                }
            });
            
            // SPECIFIC FIX FOR ADDRESS TEXTAREA FLOATING LABEL
            const addressField = document.getElementById('address');
            const addressLabel = document.getElementById('addressLabel');
            const addressContainer = document.getElementById('addressFloatingContainer');
            
            if (addressField && addressLabel) {
                function updateAddressLabel() {
                    const hasContent = addressField.value && addressField.value.trim() !== '';
                    const isFocused = document.activeElement === addressField;
                    
                    if (hasContent || isFocused) {
                        // Float the label
                        addressLabel.style.cssText = `
                            position: absolute !important;
                            top: 0 !important;
                            left: 0 !important;
                            opacity: 0.65 !important;
                            transform: scale(0.85) translateY(-0.75rem) translateX(0.15rem) !important;
                            color: var(--primary-color) !important;
                            font-weight: 600 !important;
                            padding: 0.25rem 0.75rem !important;
                            height: auto !important;
                            pointer-events: none !important;
                            border: 2px solid transparent !important;
                            transform-origin: 0 0 !important;
                            transition: opacity 0.15s ease-in-out, transform 0.15s ease-in-out !important;
                            font-size: 1rem !important;
                            display: flex !important;
                            align-items: flex-start !important;
                            white-space: nowrap !important;
                            overflow: hidden !important;
                            text-overflow: ellipsis !important;
                            z-index: 2 !important;
                        `;
                    } else {
                        // Reset to normal position
                        addressLabel.style.cssText = `
                            position: absolute !important;
                            top: 0 !important;
                            left: 0 !important;
                            height: 100% !important;
                            padding: 1.625rem 0.75rem 0.625rem !important;
                            pointer-events: none !important;
                            border: 2px solid transparent !important;
                            transform-origin: 0 0 !important;
                            transition: opacity 0.15s ease-in-out, transform 0.15s ease-in-out !important;
                            color: #6b7280 !important;
                            font-weight: 500 !important;
                            font-size: 1rem !important;
                            display: flex !important;
                            align-items: flex-start !important;
                            white-space: nowrap !important;
                            overflow: hidden !important;
                            text-overflow: ellipsis !important;
                            z-index: 2 !important;
                            opacity: 1 !important;
                            transform: none !important;
                        `;
                    }
                }
                
                // Add event listeners for address field
                addressField.addEventListener('input', updateAddressLabel);
                addressField.addEventListener('focus', updateAddressLabel);
                addressField.addEventListener('blur', function() {
                    setTimeout(updateAddressLabel, 100);
                });
                addressField.addEventListener('change', updateAddressLabel);
                addressField.addEventListener('keyup', updateAddressLabel);
                addressField.addEventListener('paste', function() {
                    setTimeout(updateAddressLabel, 100);
                });
                
                // Initial update
                setTimeout(updateAddressLabel, 200);
                
                // Update when data is loaded
                window.addEventListener('load', function() {
                    setTimeout(updateAddressLabel, 500);
                });
                
                // Make the function globally available for manual updates
                window.updateAddressLabel = updateAddressLabel;
            }
            
            console.log('Enhanced floating label support initialized');
        });
    </script>
</body>
</html>