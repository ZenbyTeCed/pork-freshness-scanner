import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
import {
    getAuth,
    createUserWithEmailAndPassword,
    signInWithEmailAndPassword,
    GoogleAuthProvider,
    signInWithPopup,
    updateProfile
} from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";

const firebaseConfig = window.firebaseConfig;


const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

export const registerUser = async (name, email, password, confirmPassword) => {
    if (password !== confirmPassword) {
        alert("Passwords do not match");
        return;
    }

    try {
        const userCredential = await createUserWithEmailAndPassword(auth, email, password);

        await updateProfile(userCredential.user, {
            displayName: name
        });

        alert("Account created!");
        window.location.href = "/dashboard";

    } catch (error) {
        console.error(error);
        alert(error.message);
    }
};

export const loginUser = async (email, password) => {
    try {
        await signInWithEmailAndPassword(auth, email, password);
        window.location.href = "/dashboard";

    } catch (error) {
        console.error(error);
        alert("Login failed");
    }
};

export const loginWithGoogle = async () => {
    const provider = new GoogleAuthProvider();

    try {
        await signInWithPopup(auth, provider);
        window.location.href = "/dashboard";

    } catch (error) {
        console.error(error);
        alert("Google login failed");
    }
};