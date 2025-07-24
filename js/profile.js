// js/profile.js
import { initializeApp } from "https://www.gstatic.com/firebasejs/12.0.0/firebase-app.js";
import { getAuth, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/12.0.0/firebase-auth.js";
import { getFirestore, doc, getDoc, setDoc } from "https://www.gstatic.com/firebasejs/12.0.0/firebase-firestore.js";

// Firebase config
const firebaseConfig = {
    apiKey: "AIzaSyBq_7VkBVj7zTJJWTltVcOk6KIU4Z6kVfE",
    authDomain: "serverside-39d3b.firebaseapp.com",
    projectId: "serverside-39d3b",
    storageBucket: "serverside-39d3b.appspot.com",
    messagingSenderId: "258572801740",
    appId: "1:258572801740:web:e96cfc7baefe9b11da1469",
    measurementId: "G-5NDQKLEGST"
};

// Init Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const db = getFirestore(app);

// DOM elements
const usernameField = document.getElementById("username");
const emailField = document.getElementById("email");

// Load user data from Firestore
async function loadUserData(uid) {
    const userRef = doc(db, "users", uid);
    const snap = await getDoc(userRef);
    if (snap.exists()) {
        const data = snap.data();
        usernameField.value = data.username || "";
        emailField.value = data.email || "";
        document.querySelector("#avatarPreview .avatar-text").textContent =
            (data.username || "U")[0].toUpperCase();
    } else {
        console.warn("No user document found in Firestore!");
    }
}

// Auth listener
onAuthStateChanged(auth, async (user) => {
    if (!user) {
        window.location.href = 'login.php';
    } else {
        await loadUserData(user.uid);
    }
});

// Reset form
window.resetForm = function () {
    document.getElementById("profileForm").reset();
};

// Handle form save (future expansion)
document.getElementById("profileForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    alert("Profile Save functionality not implemented yet!");
});
