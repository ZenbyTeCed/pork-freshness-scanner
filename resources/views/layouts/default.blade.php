<!DOCTYPE html>
<html lang="en">
<head>
    {{-- Auth layout head --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Porky</title>
    <link rel="icon" type="image/png" href="/images/Porky%20Vite.png">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['public/css/general.css',
            'resources/js/auth.js',
            'public/css/login-register.css'])
</head>
<body class="porky-body">

    {{-- Firebase settings for auth pages --}}
    <script>
        window.firebaseConfig = {
            apiKey: "{{ config('services.firebase.api_key') }}",
            authDomain: "{{ config('services.firebase.auth_domain') }}",
            projectId: "{{ config('services.firebase.project_id') }}",
            appId: "{{ config('services.firebase.app_id') }}"
        };
    </script>

    {{-- Shared navigation header --}}
    @include('partials.header')

    {{-- Auth page content slot --}}
    <main class="porky-main">
        @yield('content')
    </main>

    {{-- Simple auth footer --}}
    <footer class="porky-auth-footer">
        <div class="porky-container">
            <p>&copy; {{ now()->year }} PORKY. All rights reserved.</p>
        </div>
    </footer>

<script type="module">
// Handles auth navigation and logout behavior.
import { auth } from "{{ Vite::asset('resources/js/auth.js') }}";
import { onAuthStateChanged, signOut } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";

const authLinks = document.getElementById("authLinks");
const guestLinks = document.getElementById("guestLinks");
const logoutBtn = document.getElementById("logoutBtn");

onAuthStateChanged(auth, (user) => {
    if (!authLinks || !guestLinks) return;

    if (user) {
        authLinks.style.display = "flex";
        guestLinks.style.display = "none";
    } else {
        authLinks.style.display = "none";
        guestLinks.style.display = "flex";
    }
});

logoutBtn?.addEventListener("click", async (e) => {
    e.preventDefault();

    try {
        await signOut(auth);
    } catch (error) {
        console.error("Firebase signOut failed:", error);
    }

    try {
        const response = await fetch("{{ route('logout') }}", {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json",
                "Content-Type": "application/json"
            },
            body: JSON.stringify({})
        });

        window.location.href = "{{ route('login') }}";
    } catch (error) {
        console.error("Laravel logout failed:", error);
        window.location.href = "{{ route('login') }}";
    }
});
</script>
</body>
</html>
