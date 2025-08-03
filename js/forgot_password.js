// js/forgot-password.js
import { initializeApp } from "https://www.gstatic.com/firebasejs/12.0.0/firebase-app.js";
import { getAuth, sendPasswordResetEmail } from "https://www.gstatic.com/firebasejs/12.0.0/firebase-auth.js";
import { getFirestore, collection, query, where, getDocs } from "https://www.gstatic.com/firebasejs/12.0.0/firebase-firestore.js";

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

const form = document.getElementById("verifyForm");
const spinner = document.getElementById("loadingSpinner");

form.addEventListener("submit", async (e) => {
  e.preventDefault();
  const email = document.getElementById("email").value.trim();
  const username = document.getElementById("username").value.trim();

  try {
    toggleSpinner(true);

    const usersRef = collection(db, "users");
    const q = query(usersRef, where("email", "==", email), where("username", "==", username));
    const snapshot = await getDocs(q);

    if (snapshot.empty) {
      alert("No user found with this email and username.");
      toggleSpinner(false);
      return;
    }

    await sendPasswordResetEmail(auth, email);
    alert("Reset link sent. Please check your email.");
    window.location.href = "login.php";
  } catch (err) {
    console.error(err);
    alert("Error: " + err.message);
  } finally {
    toggleSpinner(false);
  }
});

function toggleSpinner(show) {
  if (spinner) spinner.style.display = show ? "flex" : "none";
}
