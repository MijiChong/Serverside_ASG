<?php
// profile.php
session_start();
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}
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
    
    <!-- Firebase JS SDKs -->
    <script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/12.0.0/firebase-app.js";
    import { getAuth, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/12.0.0/firebase-auth.js";

    const firebaseConfig = {
        apiKey: "AIzaSyBq_7VkBVj7zTJJWTltVcOk6KIU4Z6kVfE",
        authDomain: "serverside-39d3b.firebaseapp.com",
        projectId: "serverside-39d3b",
        storageBucket: "serverside-39d3b.appspot.com",
        messagingSenderId: "258572801740",
        appId: "1:258572801740:web:e96cfc7baefe9b11da1469",
        measurementId: "G-5NDQKLEGST"
    };

    const app = initializeApp(firebaseConfig);
    const auth = getAuth(app);

    onAuthStateChanged(auth, (user) => {
        if (!user) {
            window.location.href = 'login.php';
        } else {
            console.log("User is authenticated:", user.email);
            document.getElementById("userEmail").value = user.email;
            document.getElementById("userName").value = user.displayName || "(no username)";
        }
    });
    </script>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'navigation/navbar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <!-- Profile Header -->
            <div class="profile-header animate-fade-in">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center">
                        <div class="profile-avatar-container">
                            <div id="avatarPreview" class="profile-avatar gradient-group-<?= rand(1, 5) ?>" onclick="openAvatarModal()">
                                <span class="avatar-text">U</span>
                                <div class="avatar-overlay">
                                    <i class="fas fa-camera"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <h1 class="profile-title">
                            <i class="fas fa-user-circle me-2"></i>
                            My Profile
                        </h1>
                        <p class="profile-subtitle">
                            Manage your account information and preferences. Keep your profile up to date to get the most out of MyTrackDiary.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Profile Form -->
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="profile-card animate-fade-in">
                        <form id="profileForm" enctype="multipart/form-data">
                            <!-- Account Information Section -->
                            <div class="form-section">
                                <h3 class="section-title">
                                    <i class="fas fa-id-card me-2"></i>
                                    Account Information
                                </h3>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="username" disabled>
                                            <label for="username">
                                                <i class="fas fa-user me-1"></i>Username
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="email" class="form-control" id="email" disabled>
                                            <label for="email">
                                                <i class="fas fa-envelope me-1"></i>Email
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Information Section -->
                            <div class="form-section">
                                <h3 class="section-title">
                                    <i class="fas fa-address-card me-2"></i>
                                    Personal Information
                                </h3>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="firstName" placeholder="First Name">
                                            <label for="firstName">
                                                <i class="fas fa-user me-1"></i>First Name
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="lastName" placeholder="Last Name">
                                            <label for="lastName">
                                                <i class="fas fa-user me-1"></i>Last Name
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="date" class="form-control" id="dob">
                                            <label for="dob">
                                                <i class="fas fa-calendar me-1"></i>Date of Birth
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="tel" class="form-control" id="phone" placeholder="Phone Number">
                                            <label for="phone">
                                                <i class="fas fa-phone me-1"></i>Phone Number
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-floating mb-3">
                                    <textarea class="form-control" id="address" placeholder="Address" style="height: 100px"></textarea>
                                    <label for="address">
                                        <i class="fas fa-map-marker-alt me-1"></i>Address
                                    </label>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>
                                    Save Changes
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-lg" onclick="resetForm()">
                                    <i class="fas fa-undo me-2"></i>
                                    Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Avatar Selection Modal -->
    <div class="modal fade" id="avatarModal" tabindex="-1" aria-labelledby="avatarModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="avatarModalLabel">
                        <i class="fas fa-image me-2"></i>
                        Choose Profile Picture
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Upload Photo Section -->
                    <div class="upload-section">
                        <h6 class="mb-3">
                            <i class="fas fa-upload me-2"></i>
                            Upload Your Photo
                        </h6>
                        <div class="upload-area" onclick="document.getElementById('avatarUpload').click()">
                            <i class="fas fa-cloud-upload-alt upload-icon"></i>
                            <p class="upload-text">Click to upload or drag and drop</p>
                            <p class="upload-hint">PNG, JPG, GIF up to 5MB</p>
                        </div>
                        <input type="file" class="d-none" id="avatarUpload" accept="image/*">
                    </div>
                    
                    <div class="divider">
                        <span>OR</span>
                    </div>
                    
                    <!-- Gradient Selection -->
                    <div class="gradient-section">
                        <h6 class="mb-3">
                            <i class="fas fa-palette me-2"></i>
                            Choose Gradient
                        </h6>
                        <div class="gradient-options">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <div class="gradient-option gradient-group-<?= $i ?>" 
                                     onclick="selectGradient(<?= $i ?>)" 
                                     data-gradient="<?= $i ?>">
                                    <div class="gradient-check">
                                        <i class="fas fa-check"></i>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="applyAvatarSelection()">Apply Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Firebase integration -->
    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/12.0.0/firebase-app.js";
        import { getFirestore, doc, getDoc } from "https://www.gstatic.com/firebasejs/12.0.0/firebase-firestore.js";

        const firebaseConfig = {
            apiKey: "AIzaSyBq_7VkBVj7zTJJWTltVcOk6KIU4Z6kVfE",
            authDomain: "serverside-39d3b.firebaseapp.com",
            projectId: "serverside-39d3b",
            storageBucket: "serverside-39d3b.appspot.com",
            messagingSenderId: "258572801740",
            appId: "1:258572801740:web:e96cfc7baefe9b11da1469",
            measurementId: "G-5NDQKLEGST"
        };

        const app = initializeApp(firebaseConfig);
        const db = getFirestore(app);
        const uid = "<?= $uid ?>";

        const usernameField = document.getElementById("username");
        const emailField = document.getElementById("email");

        // Load Firebase user data
        async function loadUserData() {
            const userRef = doc(db, "users", uid);
            const snap = await getDoc(userRef);
            if (snap.exists()) {
                const data = snap.data();
                usernameField.value = data.username || "";
                emailField.value = data.email || "";
                document.querySelector("#avatarPreview .avatar-text").textContent = (data.username || "U")[0].toUpperCase();
            }
        }

        loadUserData();

        // Profile form submission
        document.getElementById("profileForm").addEventListener("submit", (e) => {
            e.preventDefault();
            
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
            submitBtn.disabled = true;
            
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                showNotification("Profile updated successfully!", "success");
            }, 1500);
        });

        // Show notification function
        function showNotification(message, type = "info") {
            const notification = document.createElement("div");
            notification.className = `alert alert-${type} alert-dismissible fade show notification`;
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 3000);
        }

        // Make functions available globally
        window.showNotification = showNotification;
    </script>

    <script>
        let selectedGradient = null;
        let uploadedImage = null;

        // Open avatar modal
        function openAvatarModal() {
            const modal = new bootstrap.Modal(document.getElementById('avatarModal'));
            modal.show();
        }

        // Handle file upload
        document.getElementById("avatarUpload").addEventListener("change", (e) => {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    showNotification("File size must be less than 5MB", "error");
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = (event) => {
                    uploadedImage = event.target.result;
                    selectedGradient = null;
                    
                    // Clear gradient selections
                    document.querySelectorAll('.gradient-option').forEach(option => {
                        option.classList.remove('active');
                    });
                    
                    // Update upload area to show preview
                    const uploadArea = document.querySelector('.upload-area');
                    uploadArea.innerHTML = `
                        <img src="${uploadedImage}" alt="Preview" class="upload-preview">
                        <p class="upload-text">Image selected</p>
                        <p class="upload-hint">Click to change</p>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });

        // Select gradient
        function selectGradient(groupNum) {
            selectedGradient = groupNum;
            uploadedImage = null;
            
            // Clear upload preview
            const uploadArea = document.querySelector('.upload-area');
            uploadArea.innerHTML = `
                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                <p class="upload-text">Click to upload or drag and drop</p>
                <p class="upload-hint">PNG, JPG, GIF up to 5MB</p>
            `;
            
            // Update gradient selection
            document.querySelectorAll('.gradient-option').forEach(option => {
                option.classList.remove('active');
            });
            document.querySelector(`[data-gradient="${groupNum}"]`).classList.add('active');
        }

        // Apply avatar selection
        function applyAvatarSelection() {
            const avatar = document.getElementById("avatarPreview");
            
            if (uploadedImage) {
                // Apply uploaded image
                avatar.style.backgroundImage = `url(${uploadedImage})`;
                avatar.style.backgroundSize = "cover";
                avatar.style.backgroundPosition = "center";
                avatar.querySelector(".avatar-text").style.display = "none";
                
                // Remove all gradient classes
                for (let i = 1; i <= 5; i++) {
                    avatar.classList.remove(`gradient-group-${i}`);
                }
            } else if (selectedGradient) {
                // Apply selected gradient
                avatar.style.backgroundImage = "";
                avatar.querySelector(".avatar-text").style.display = "flex";
                
                // Remove all gradient classes and add selected one
                for (let i = 1; i <= 5; i++) {
                    avatar.classList.remove(`gradient-group-${i}`);
                }
                avatar.classList.add(`gradient-group-${selectedGradient}`);
            }
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('avatarModal'));
            modal.hide();
            
            showNotification("Avatar updated successfully!", "success");
        }

        // Reset form function
        function resetForm() {
            document.getElementById("profileForm").reset();
            showNotification("Form reset successfully!", "info");
        }

        // Make functions available globally
        window.openAvatarModal = openAvatarModal;
        window.selectGradient = selectGradient;
        window.applyAvatarSelection = applyAvatarSelection;
        window.resetForm = resetForm;
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>