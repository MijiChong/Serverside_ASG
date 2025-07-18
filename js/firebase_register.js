import { initializeApp } from "https://www.gstatic.com/firebasejs/12.0.0/firebase-app.js";
import { getAnalytics } from "https://www.gstatic.com/firebasejs/12.0.0/firebase-analytics.js";
import { getAuth, createUserWithEmailAndPassword } from "https://www.gstatic.com/firebasejs/12.0.0/firebase-auth.js";

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

// Register event
const registerForm = document.getElementById('registerForm');
registerForm.addEventListener("submit", function(event) {
    event.preventDefault();
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    createUserWithEmailAndPassword(auth, email, password)
    .then((userCredential) => {
        alert("Account Successfully Created");
        window.location.href = 'login.php';
    })
    .catch((error) => {
        alert(error.message);
    });
});
