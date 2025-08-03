import { doc, getDoc } from 'https://www.gstatic.com/firebasejs/10.7.0/firebase-firestore.js';

const uid = typeof FIREBASE_UID !== 'undefined' ? FIREBASE_UID : null;
if (!uid) console.error("FIREBASE_UID is missing!");

function showNotification(message, type = "info") {
    // Create a better notification system
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : type === 'danger' ? 'danger' : 'info'} alert-dismissible fade show`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10001;
        min-width: 300px;
        animation: slideInRight 0.3s ease-out;
    `;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function getSelectedGradient() {
    // First try to get from selected gradient option
    const selected = document.querySelector(".gradient-option.selected");
    if (selected) {
        return parseInt(selected.dataset.gradient);
    }
    
    // Fallback to hidden input
    const selectedGradient = document.getElementById('selectedGradient');
    if (selectedGradient && selectedGradient.value) {
        return parseInt(selectedGradient.value);
    }
    
    // Default fallback
    return 1;
}

function showLoading(show = true) {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = show ? 'flex' : 'none';
    }
}

// Function to get Firestore user data
async function getFirestoreUserData() {
    try {
        if (!window.firestore) {
            throw new Error("Firestore not initialized");
        }
        
        const userDoc = await getDoc(doc(window.firestore, "users", uid));
        
        if (userDoc.exists()) {
            const userData = userDoc.data();
            return {
                username: userData.username || userData.displayName || '',
                email: userData.email || ''
            };
        } else {
            console.warn("No Firestore user document found");
            return { username: '', email: '' };
        }
    } catch (error) {
        console.error("Error fetching Firestore data:", error);
        return { username: '', email: '' };
    }
}

async function loadUserData() {
    showLoading(true);
    
    try {
        // First get Firestore data
        const firestoreData = await getFirestoreUserData();
        console.log("Firestore data:", firestoreData);

        // Sync Firestore data to MySQL first if we have it
        if (firestoreData.username || firestoreData.email) {
            await syncFirestoreToMySQL(firestoreData.username, firestoreData.email);
        }

        // Then load complete profile from MySQL (which now includes synced Firestore data)
        const mysqlResponse = await fetch("load_profile.php");
        const profileData = await mysqlResponse.json();
        
        console.log("MySQL profile data:", profileData);

        if (profileData.error) {
            console.warn("Error loading profile:", profileData.error);
            // Use Firestore data as fallback
            document.getElementById("username").value = firestoreData.username || "";
            document.getElementById("email").value = firestoreData.email || "";
        } else {
            // Use MySQL data (which includes synced Firestore data)
            document.getElementById("username").value = profileData.username || firestoreData.username || "";
            document.getElementById("email").value = profileData.email || firestoreData.email || "";
            
            // Fill other profile fields
            document.getElementById("firstName").value = profileData.first_name || "";
            document.getElementById("lastName").value = profileData.last_name || "";
            document.getElementById("dob").value = profileData.dob || "";
            document.getElementById("phone").value = profileData.phone || "";
            document.getElementById("address").value = profileData.address || "";

            // Handle avatar gradient
            const avatarGradient = profileData.avatar_gradient || 1;
            const avatar = document.getElementById("avatarPreview");
            if (avatar) {
                avatar.className = `profile-avatar gradient-group-${avatarGradient}`;
            }

            // Update hidden input
            const selectedGradient = document.getElementById('selectedGradient');
            if (selectedGradient) {
                selectedGradient.value = avatarGradient;
            }

            // Visually select the corresponding gradient
            document.querySelectorAll(".gradient-option").forEach(el => {
                el.classList.remove("selected");
                if (parseInt(el.dataset.gradient) === parseInt(avatarGradient)) {
                    el.classList.add("selected");
                }
            });
        }
        
        // Update avatar text with first letter of username or email
        const avatarText = document.querySelector('.avatar-text');
        if (avatarText) {
            const usernameField = document.getElementById("username").value;
            const emailField = document.getElementById("email").value;
            const firstLetter = (usernameField || emailField || 'U').charAt(0).toUpperCase();
            avatarText.textContent = firstLetter;
        }
        
    } catch (error) {
        console.error("Error loading profile:", error);
        showNotification("Failed to load profile data", "danger");
    } finally {
        showLoading(false);
    }
}

// Function to sync Firestore data to MySQL
async function syncFirestoreToMySQL(username, email) {
    try {
        const response = await fetch("sync_firestore_data.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                uid: uid,
                username: username,
                email: email
            })
        });
        
        const result = await response.json();
        if (result.status !== 'success') {
            console.warn("Failed to sync Firestore data to MySQL:", result.message);
        } else {
            console.log("Successfully synced Firestore data to MySQL");
        }
    } catch (error) {
        console.error("Error syncing Firestore data:", error);
    }
}

// Wait for Firestore to be initialized before loading data
function waitForFirestore() {
    if (window.firestore) {
        loadUserData();
    } else {
        setTimeout(waitForFirestore, 100);
    }
}

// Start loading when page is ready
document.addEventListener('DOMContentLoaded', waitForFirestore);

// Form submission handler
document.getElementById("profileForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const selectedGradient = getSelectedGradient();
    console.log("Selected gradient for saving:", selectedGradient);

    const profileData = {
        uid: uid,
        firstName: document.getElementById("firstName").value.trim(),
        lastName: document.getElementById("lastName").value.trim(),
        dob: document.getElementById("dob").value,
        phone: document.getElementById("phone").value.trim(),
        address: document.getElementById("address").value.trim(),
        avatarGradient: selectedGradient
    };

    console.log("Profile data being sent:", profileData);

    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
    submitBtn.disabled = true;

    try {
        const response = await fetch("save_profile.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(profileData)
        });
        const result = await response.json();

        console.log("Save result:", result);

        if (result.status === 'success') {
            showNotification("Profile updated successfully!", "success");
        } else {
            showNotification(result.message || "Failed to save profile", "danger");
        }
    } catch (err) {
        console.error("Save error:", err);
        showNotification("Error saving profile", "danger");
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

// Reset form function
function resetForm() {
    if (confirm("Are you sure you want to reset all changes?")) {
        loadUserData();
    }
}

// Make resetForm available globally
window.resetForm = resetForm;