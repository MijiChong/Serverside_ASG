import { doc, getDoc } from 'https://www.gstatic.com/firebasejs/10.7.0/firebase-firestore.js';

const uid = typeof FIREBASE_UID !== 'undefined' ? FIREBASE_UID : null;
if (!uid) console.error("FIREBASE_UID is missing!");

function showNotification(message, type = "info") {
    alert(`[${type.toUpperCase()}] ${message}`); // Replace this with custom toast later if needed
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
        // Load both Firestore and MySQL data concurrently
        const [firestoreData, mysqlResponse] = await Promise.all([
            getFirestoreUserData(),
            fetch("load_profile.php")
        ]);
        
        const mysqlData = await mysqlResponse.json();
        
        console.log("Firestore data:", firestoreData);
        console.log("MySQL data:", mysqlData);

        // Populate Firestore data (read-only fields)
        document.getElementById("username").value = firestoreData.username || "";
        document.getElementById("email").value = firestoreData.email || "";
        
        // Update avatar text with first letter of username or email
        const avatarText = document.querySelector('.avatar-text');
        if (avatarText) {
            const firstLetter = (firestoreData.username || firestoreData.email || 'U').charAt(0).toUpperCase();
            avatarText.textContent = firstLetter;
        }

        // Populate MySQL data (editable fields) - handle both error and success cases
        if (!mysqlData.error) {
            document.getElementById("firstName").value = mysqlData.first_name || "";
            document.getElementById("lastName").value  = mysqlData.last_name  || "";
            document.getElementById("dob").value       = mysqlData.dob        || "";
            document.getElementById("phone").value     = mysqlData.phone      || "";
            document.getElementById("address").value   = mysqlData.address    || "";

            // Handle avatar gradient
            const avatarGradient = mysqlData.avatar_gradient || 1;
            const avatar = document.getElementById("avatarPreview");
            avatar.className = `profile-avatar gradient-group-${avatarGradient}`;

            // Update hidden input
            const selectedGradient = document.getElementById('selectedGradient');
            if (selectedGradient) {
                selectedGradient.value = avatarGradient;
            }

            // Also visually select the corresponding gradient
            document.querySelectorAll(".gradient-option").forEach(el => {
                el.classList.remove("selected");
                if (parseInt(el.dataset.gradient) === parseInt(avatarGradient)) {
                    el.classList.add("selected");
                }
            });
        } else {
            console.warn("No existing MySQL profile or error:", mysqlData.error);
            // Set default values
            document.getElementById("firstName").value = "";
            document.getElementById("lastName").value = "";
            document.getElementById("dob").value = "";
            document.getElementById("phone").value = "";
            document.getElementById("address").value = "";
            
            // Set default avatar
            const avatar = document.getElementById("avatarPreview");
            avatar.className = `profile-avatar gradient-group-1`;
            const selectedGradient = document.getElementById('selectedGradient');
            if (selectedGradient) {
                selectedGradient.value = 1;
            }
        }
        
        // If this is the first time loading, sync Firestore data to MySQL
        if (firestoreData.username || firestoreData.email) {
            await syncFirestoreToMySQL(firestoreData.username, firestoreData.email);
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

document.getElementById("profileForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const selectedGradient = getSelectedGradient();
    console.log("Selected gradient for saving:", selectedGradient); // Debug log

    const profileData = {
        uid: uid,
        firstName: document.getElementById("firstName").value.trim(),
        lastName:  document.getElementById("lastName").value.trim(),
        dob:       document.getElementById("dob").value,
        phone:     document.getElementById("phone").value.trim(),
        address:   document.getElementById("address").value.trim(),
        avatarGradient: selectedGradient
    };

    console.log("Profile data being sent:", profileData); // Debug log

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

        console.log("Save result:", result); // Debug log

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

function resetForm() {
    if (confirm("Are you sure you want to reset all changes?")) {
        loadUserData();
    }
}