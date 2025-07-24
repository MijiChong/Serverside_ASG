import { initializeApp } from "https://www.gstatic.com/firebasejs/12.0.0/firebase-app.js";
import { getAnalytics } from "https://www.gstatic.com/firebasejs/12.0.0/firebase-analytics.js";
import { getAuth, createUserWithEmailAndPassword } from "https://www.gstatic.com/firebasejs/12.0.0/firebase-auth.js";
import { getFirestore, doc, setDoc } from "https://www.gstatic.com/firebasejs/12.0.0/firebase-firestore.js"; // 🔥 Firestore import

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

// Initialize
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);
const auth = getAuth(app);
const db = getFirestore(app); // 🔥 Init Firestore

// Register form event
const registerForm = document.getElementById('registerForm');
registerForm.addEventListener("submit", async function(event) {
    event.preventDefault();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const username = document.getElementById('username').value; // make sure this input exists!

    try {
        if (!email.includes('@') || password.length < 6 || username.trim() === '') {
            alert('Please enter a valid email, password, and username');
            return;
         }

        const userCredential = await createUserWithEmailAndPassword(auth, email, password);
        const user = userCredential.user;

        // 🔥 Store additional info to Firestore
        await setDoc(doc(db, "users", user.uid), {
            username: username,
            email: email,
            createdAt: new Date()
        });

        alert("Account Successfully Created");
        window.location.href = 'login.php';
    } catch (error) {
        alert(error.message);
    }
});
