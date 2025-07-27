import { initializeApp } from "https://www.gstatic.com/firebasejs/12.0.0/firebase-app.js";
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
const auth = getAuth(app);

const loginForm = document.getElementById('loginForm');
const spinner = document.getElementById('loadingSpinner');

loginForm.addEventListener('submit', async (e) => {
  e.preventDefault();

  const email = loginForm['email'].value.trim();
  const password = loginForm['password'].value;

  showSpinner(true);

  try {
    const { user } = await signInWithEmailAndPassword(auth, email, password);
    const idToken = await user.getIdToken();

    const resp = await fetch("session-login.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ idToken })
    });

    const data = await resp.json().catch(() => ({}));

    if (!resp.ok || data.error) {
      throw new Error(data.error || "Failed to create server session");
    }

    window.location.href = "dashboard.php";
  } catch (err) {
    alert("Login failed: " + err.message);
    console.error(err);
  } finally {
    showSpinner(false);
  }
});

function showSpinner(show) {
  if (spinner) spinner.style.display = show ? 'flex' : 'none';
}
