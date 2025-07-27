import { initializeApp } from "https://www.gstatic.com/firebasejs/12.0.0/firebase-app.js";
import { getAuth, createUserWithEmailAndPassword } from "https://www.gstatic.com/firebasejs/12.0.0/firebase-auth.js";
import { getFirestore, doc, setDoc } from "https://www.gstatic.com/firebasejs/12.0.0/firebase-firestore.js";

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
const db = getFirestore(app);

const registerForm = document.getElementById('registerForm');

registerForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const username = document.getElementById('username').value.trim();

    try {
        // 1. Create Firebase account
        const userCredential = await createUserWithEmailAndPassword(auth, email, password);
        const user = userCredential.user;

        // 2. Store extra info in Firestore
        await setDoc(doc(db, "users", user.uid), {
            username: username,
            email: email,
            createdAt: new Date()
        });

        alert("Account created successfully! Please login.");
        // 3. Sign out from Firebase (optional but recommended)
        await auth.signOut();

        // 4. Redirect to login page
        window.location.href = "login.php";
    } catch (error) {
        console.error(error);
        alert(error.message);
    }
});
