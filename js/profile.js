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