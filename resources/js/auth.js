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
export const auth = getAuth(app);

export const loginUser = async (email, password) => {
    try {
        const userCredential = await signInWithEmailAndPassword(auth, email, password);
        return userCredential.user;
    } catch (error) {
        console.error(error);
        alert(error.message);
        return null;
    }
};

export const registerUser = async (name, email, password, confirmPassword) => {
    if (password !== confirmPassword) {
        alert("Passwords do not match");
        return null;
    }

    try {
        const userCredential = await createUserWithEmailAndPassword(auth, email, password);

        await updateProfile(userCredential.user, {
            displayName: name
        });

        return userCredential.user;
    } catch (error) {
        console.error(error);
        alert(error.message);
        return null;
    }
};

export const loginWithGoogle = async () => {
    const provider = new GoogleAuthProvider();

    try {
        const result = await signInWithPopup(auth, provider);
        return result.user;
    } catch (error) {
        console.error(error);
        alert(error.message);
        return null;
    }
};