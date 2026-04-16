<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'PORKY') }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite([
        'public/css/general.css',
        'public/css/home.css',
        'public/css/dashboard.css',
        'public/css/scan.css',
        'public/css/history.css',
        'public/css/result.css',
        'public/css/settings.css',
        'resources/js/auth.js'
    ])
</head>
<body class="porky-body">

    <script>
        window.firebaseConfig = {
            apiKey: "{{ env('FIREBASE_API_KEY') }}",
            authDomain: "{{ env('FIREBASE_AUTH_DOMAIN') }}",
            projectId: "{{ env('FIREBASE_PROJECT_ID') }}",
            appId: "{{ env('FIREBASE_APP_ID') }}"
        };
    </script>

    @include('partials.header')

    <main class="porky-main">
        @yield('content')
    </main>

    @include('partials.footer')

    <script type="module">
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
            await signOut(auth);
            window.location.href = "/";
        });
    </script>

</body>
</html>