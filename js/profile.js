// Get Firebase UID from hidden input
const uid = document.getElementById("firebaseUid").value;

// Load profile data from server (via PHP)
async function loadUserData() {
    try {
        const response = await fetch("load_profile.php");
        const data = await response.json();

        if (!data.error) {
            document.getElementById("firstName").value = data.first_name || "";
            document.getElementById("lastName").value  = data.last_name  || "";
            document.getElementById("dob").value       = data.dob        || "";
            document.getElementById("phone").value     = data.phone      || "";
            document.getElementById("address").value   = data.address    || "";

            // Optional: set avatar gradient preview if needed
            if (data.avatar_gradient) {
                const avatar = document.getElementById("avatarPreview");
                avatar.className = `profile-avatar gradient-group-${data.avatar_gradient}`;
            }
        } else {
            showNotification(data.error, "danger");
        }
    } catch (error) {
        console.error("Error loading profile:", error);
        showNotification("Failed to load profile data", "danger");
    }
}

loadUserData();

// Save profile data on form submit
document.getElementById("profileForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const profileData = {
        uid: uid,
        firstName: document.getElementById("firstName").value.trim(),
        lastName:  document.getElementById("lastName").value.trim(),
        dob:       document.getElementById("dob").value,
        phone:     document.getElementById("phone").value.trim(),
        address:   document.getElementById("address").value.trim(),
        avatarGradient: getSelectedGradient() // optional helper
    };

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

        if (result.success) {
            showNotification("Profile updated successfully!", "success");
        } else {
            showNotification(result.error || "Failed to save profile", "danger");
        }
    } catch (err) {
        console.error("Save error:", err);
        showNotification("Error saving profile", "danger");
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

// Helper: Notification alert
function showNotification(message, type = "info") {
    const notification = document.createElement("div");
    notification.className = `alert alert-${type} alert-dismissible fade show notification`;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

// Helper: Reset form
function resetForm() {
    document.getElementById("profileForm").reset();
}
window.resetForm = resetForm;

// Optional: get selected avatar gradient (based on your UI)
function getSelectedGradient() {
    const selected = document.querySelector(".gradient-option.selected");
    return selected ? parseInt(selected.dataset.gradient) : 1;
}
