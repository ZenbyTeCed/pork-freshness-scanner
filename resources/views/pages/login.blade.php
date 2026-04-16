@extends('layouts.default')

@section('content')
<section class="porky-auth-section">
    <div class="porky-container">
        <div class="porky-auth-layout">

            <div class="porky-auth-showcase">
                <div class="porky-auth-branding">
                    <div class="porky-auth-brand-mark">P</div>
                    <div>
                        <h1 class="porky-auth-brand-title">PORKY</h1>
                        <p class="porky-auth-brand-subtitle">AI Freshness Grading</p>
                    </div>
                </div>

                <div class="porky-auth-showcase-text">
                    <h2>Monitor Pork Freshness with Smart Visual Analysis</h2>
                    <p>
                        Access the platform to analyze pork images, review grading results,
                        and monitor freshness records through a clean and reliable dashboard.
                    </p>
                </div>
            </div>

            <div class="porky-auth-wrapper">
                <div class="porky-auth-card">
                    <div class="porky-auth-header">
                        <h1>Login</h1>
                        <p>Enter your email and password.</p>
                    </div>

                    <form id="loginForm" class="porky-auth-form">
                        <div class="porky-form-group">
                            <label for="loginEmail">Email Address</label>
                            <input type="email" id="loginEmail" name="email" placeholder="Enter your email" required>
                        </div>

                        <div class="porky-form-group">
                            <label for="loginPassword">Password</label>
                            <input type="password" id="loginPassword" name="password" placeholder="Enter your password" required>
                        </div>

                        <button type="submit" class="porky-auth-btn">Login</button>

                        <div class="porky-auth-divider">
                            <span>OR</span>
                        </div>

                        <button type="button" id="googleLoginBtn" class="porky-google-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20">
                                <path fill="#EA4335" d="M24 9.5c3.2 0 6.1 1.1 8.3 3.2l6.2-6.2C34.6 2.5 29.7 0 24 0 14.6 0 6.4 5.6 2.7 13.7l7.2 5.6C11.6 13.1 17.2 9.5 24 9.5z"/>
                                <path fill="#4285F4" d="M46.1 24.5c0-1.6-.1-3.1-.4-4.5H24v9h12.4c-.5 2.7-2.1 5-4.5 6.5l7 5.4c4.1-3.8 6.2-9.3 6.2-16.4z"/>
                                <path fill="#FBBC05" d="M9.9 28.3c-.5-1.5-.8-3.1-.8-4.8s.3-3.3.8-4.8l-7.2-5.6C1 17.3 0 20.5 0 23.5s1 6.2 2.7 9l7.2-5.6z"/>
                                <path fill="#34A853" d="M24 48c6.5 0 12-2.1 16-5.7l-7-5.4c-2 1.3-4.5 2-9 2-6.8 0-12.4-3.6-14.4-8.8l-7.2 5.6C6.4 42.4 14.6 48 24 48z"/>
                            </svg>
                            <span>Continue with Google</span>
                        </button>

                        <p class="porky-auth-footer-text">
                            Don’t have an account?
                            <a href="{{ route('register') }}">Create Account</a>
                        </p>
                    </form>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection

<script type="module">
import { loginUser, loginWithGoogle } from "{{ Vite::asset('resources/js/auth.js') }}";

document.getElementById("loginForm")?.addEventListener("submit", async (e) => {
    e.preventDefault();

    const email = document.getElementById("loginEmail").value;
    const password = document.getElementById("loginPassword").value;

    const user = await loginUser(email, password);
    if (!user) return;

    const res = await fetch("/auth/login", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            uid: user.uid,
            email: user.email,
            name: user.displayName
        })
    });

    const data = await res.json();
    if (data.success) window.location.href = data.redirect;
});

document.getElementById("googleLoginBtn")?.addEventListener("click", async () => {
    const user = await loginWithGoogle();
    if (!user) return;

    const res = await fetch("/auth/google", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            uid: user.uid,
            email: user.email,
            name: user.displayName
        })
    });

    const data = await res.json();
    if (data.success) window.location.href = data.redirect;
});
</script>