@extends('layouts.default')

@section('content')
<section class="porky-auth-section">
    <div class="porky-container">
        <div class="porky-auth-layout porky-auth-layout-wide"">

            <!-- LEFT SIDE -->
            <div class="porky-auth-showcase">
                <div class="porky-auth-branding">
                    <div class="porky-auth-brand-mark">P</div>
                    <div>
                        <h1 class="porky-auth-brand-title">PORKY</h1>
                        <p class="porky-auth-brand-subtitle">AI Freshness Grading</p>
                    </div>
                </div>

                <div class="porky-auth-showcase-text">
                    <h2>Create Your Account to Get Started</h2>
                    <p>
                        Register to access pork freshness analysis, track grading results,
                        and manage your data through the system dashboard.
                    </p>
                </div>
            </div>

            <!-- RIGHT SIDE -->
            <div class="porky-auth-wrapper porky-auth-wrapper-wide">
                <div class="porky-auth-card">

                    <div class="porky-auth-header">
                        <h1>Create Account</h1>
                        <p>Fill in your details to register.</p>
                    </div>

                    <form id="registerForm" class="porky-auth-form">
                        <div class="porky-form-group">
                            <label for="registerName">Full Name</label>
                            <input class="porky-register-input" type="text" id="registerName" name="name" placeholder="Enter your full name" required>
                        </div>

                        <div class="porky-form-group">
                            <label for="registerEmail">Email Address</label>
                            <input class="porky-register-input" type="email" id="registerEmail" name="email" placeholder="Enter your email" required>
                        </div>

                        <div class="porky-form-group">
                            <label for="registerPassword">Password</label>
                            <input class="porky-register-input" type="password" id="registerPassword" name="password" placeholder="Create a password" required>
                        </div>

                        <div class="porky-form-group">
                            <label for="registerConfirmPassword">Confirm Password</label>
                            <input class="porky-register-input" type="password" id="registerConfirmPassword" name="confirm_password" placeholder="Confirm your password" required>
                        </div>

                        <button type="submit" class="porky-auth-btn">
                            Create Account
                        </button>

                        <p class="porky-auth-footer-text">
                            Already have an account?
                            <a href="{{ route('login') }}">Sign in</a>
                        </p>
                    </form>

                </div>
            </div>

        </div>
    </div>
</section>
@endsection

<script type="module">
import { getAuth, GoogleAuthProvider, signInWithPopup } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";
import { registerUser, loginWithGoogle } from "{{ Vite::asset('resources/js/auth.js') }}";

const auth = getAuth();
const provider = new GoogleAuthProvider();

document.getElementById("googleRegisterBtn").addEventListener("click", async () => {
    try {
        const result = await signInWithPopup(auth, provider);
        const user = result.user;

        console.log("Registered:", user);

        window.location.href = "/dashboard";

    } catch (error) {
        console.error(error);
        alert("Google signup failed");
    }

document.getElementById("registerForm").addEventListener("submit", (e) => {
    e.preventDefault();

    const name = document.getElementById("registerName").value;
    const email = document.getElementById("registerEmail").value;
    const password = document.getElementById("registerPassword").value;
    const confirm = document.getElementById("registerConfirmPassword").value;

    registerUser(name, email, password, confirm);
});

document.getElementById("googleRegisterBtn")?.addEventListener("click", loginWithGoogle);
});
</script>