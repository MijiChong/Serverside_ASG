const uid = typeof FIREBASE_UID !== 'undefined' ? FIREBASE_UID : null;
if (!uid) console.error("FIREBASE_UID is missing!");

function showNotification(message, type = "info") {
    alert(`[${type.toUpperCase()}] ${message}`); // Replace this with custom toast later if needed
}

function getSelectedGradient() {
    const selected = document.querySelector(".gradient-option.selected");
    return selected ? selected.dataset.gradient : 1;
}

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

            if (data.avatar_gradient) {
                const avatar = document.getElementById("avatarPreview");
                avatar.className = `profile-avatar gradient-group-${data.avatar_gradient}`;

                // Also visually select the corresponding gradient
                document.querySelectorAll(".gradient-option").forEach(el => {
                    el.classList.remove("selected");
                    if (parseInt(el.dataset.gradient) === data.avatar_gradient) {
                        el.classList.add("selected");
                    }
                });
            }
        } else {
            console.warn("No existing profile:", data.error);
        }
    } catch (error) {
        console.error("Error loading profile:", error);
        showNotification("Failed to load profile data", "danger");
    }
}

loadUserData();

document.getElementById("profileForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const profileData = {
        uid: uid,
        firstName: document.getElementById("firstName").value.trim(),
        lastName:  document.getElementById("lastName").value.trim(),
        dob:       document.getElementById("dob").value,
        phone:     document.getElementById("phone").value.trim(),
        address:   document.getElementById("address").value.trim(),
        avatarGradient: getSelectedGradient()
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
