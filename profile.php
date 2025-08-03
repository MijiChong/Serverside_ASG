<?php
// profile.php
session_start();
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}
$uid = $_SESSION['uid']; // Pass UID to JS
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyTrackDiary - Profile</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Fixed CSS paths - Check these paths exist -->
    <link href="navigation/navbar.css" rel="stylesheet">
    <link href="css/profile.css" rel="stylesheet">
    
    <!-- Add fallback styles if CSS files don't load -->
    <style>
        /* Fallback styles in case external CSS doesn't load */
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }
        .main-content { padding: 2rem 0; }
        .profile-card { 
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
    </style>

    <!-- Firebase SDK -->
    <script type="module">
        import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.7.0/firebase-app.js';
        import { getFirestore } from 'https://www.gstatic.com/firebasejs/10.7.0/firebase-firestore.js';
        
        // Your Firebase config - replace with your actual config
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
        
        // Make db available globally
        window.firestore = db;
    </script>

    <!-- Pass UID to JS -->
    <script>
        const FIREBASE_UID = "<?= htmlspecialchars($uid); ?>";
    </script>
</head>
<body>
<?php include 'navigation/navbar.php'; ?>

<div class="main-content">
    <div class="container">
        <div class="profile-header animate-fade-in">
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
                        <!-- Hidden input to store gradient selection -->
                        <input type="hidden" name="gradient" id="selectedGradient" value="1">
                    </div>
                </div>
                <div class="col-md-9">
                    <h1 class="profile-title"><i class="fas fa-user-circle me-2"></i>My Profile</h1>
                    <p class="profile-subtitle">Manage your account information and preferences.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="profile-card animate-fade-in">
                    <form id="profileForm" enctype="multipart/form-data">
                        <div class="form-section">
                            <h3 class="section-title"><i class="fas fa-address-card me-2"></i>Personal Information</h3>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="username" placeholder="Username" readonly>
                                        <label for="username"><i class="fas fa-user-tag me-1"></i>Username</label>
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>Username cannot be changed
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="email" class="form-control" id="email" placeholder="Email" readonly>
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
                                        <input type="text" class="form-control" id="firstName" placeholder="First Name">
                                        <label for="firstName"><i class="fas fa-user me-1"></i>First Name</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="lastName" placeholder="Last Name">
                                        <label for="lastName"><i class="fas fa-user me-1"></i>Last Name</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="date" class="form-control" id="dob">
                                        <label for="dob"><i class="fas fa-calendar me-1"></i>Date of Birth</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="tel" class="form-control" id="phone" placeholder="Phone Number">
                                        <label for="phone"><i class="fas fa-phone me-1"></i>Phone Number</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-floating mb-3">
                                <textarea class="form-control" id="address" placeholder="Address" style="height: 100px"></textarea>
                                <label for="address"><i class="fas fa-map-marker-alt me-1"></i>Address</label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-lg" onclick="resetForm()">
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
                        <span class="avatar-text">M</span>
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

<!-- JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script type="module" src="./js/profile.js"></script>
<script src="./js/avatar.js"></script>

<?php echo "<script>console.log('UID from PHP:', '".htmlspecialchars($uid)."');</script>"; ?>
</body>
</html>