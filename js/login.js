import { initializeApp } from "https://www.gstatic.com/firebasejs/12.0.0/firebase-app.js";
import { getAnalytics } from "https://www.gstatic.com/firebasejs/12.0.0/firebase-analytics.js";
import { getAuth, signInWithEmailAndPassword } from "https://www.gstatic.com/firebasejs/12.0.0/firebase-auth.js";

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
const analytics = getAnalytics(app);
const auth = getAuth(app);

// Login form submit
const loginForm = document.getElementById('loginForm');
loginForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const email = loginForm['email'].value;
    const password = loginForm['password'].value;

    // Show loading spinner
    document.getElementById('loadingSpinner').style.display = 'flex';

    signInWithEmailAndPassword(auth, email, password)
        .then((userCredential) => {
            // Successfully logged in
            const user = userCredential.user;

            // Optional: Delay to let spinner show briefly
            setTimeout(() => {
                window.location.href = 'dashboard.php'; // Your redirect page
            }, 2000); // Spinner visible for 1 second
        })
        .catch((error) => {
            document.getElementById('loadingSpinner').style.display = 'none'; // Hide spinner
            alert('Login failed: ' + error.message);
        });
});

