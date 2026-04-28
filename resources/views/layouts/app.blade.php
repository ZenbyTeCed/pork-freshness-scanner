<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Porky</title>
    <link rel="icon" type="image/png" href="{{ asset('images/Porky Vite.png') }}">

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

    // const authLinks = document.getElementById("authLinks");
    // const guestLinks = document.getElementById("guestLinks");
    const logoutBtn = document.getElementById("logoutBtn");

    // onAuthStateChanged(auth, (user) => {
    //     if (!authLinks || !guestLinks) return;

    //     if (user) {
    //         authLinks.style.display = "flex";
    //         guestLinks.style.display = "none";
    //     } else {
    //         authLinks.style.display = "none";
    //         guestLinks.style.display = "flex";
    //     }
    // });

    logoutBtn?.addEventListener("click", async (e) => {
        e.preventDefault();

        try {
            await signOut(auth);
        } catch (error) {
            console.error("Firebase signOut failed:", error);
        }

        try {
            await fetch("{{ route('logout') }}", {
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

    const porkyNav = document.querySelector('.porky-nav');

    if (porkyNav && window.matchMedia('(max-width: 1024px)').matches) {
        let lastScrollY = window.scrollY;
        let ticking = false;

        porkyNav.classList.add('porky-nav-scrollable');

        const updateMobileNavVisibility = () => {
            const currentScrollY = window.scrollY;
            const scrollingDown = currentScrollY > lastScrollY;
            const passedThreshold = currentScrollY > 80;

            if (scrollingDown && passedThreshold) {
                porkyNav.classList.add('porky-nav-hidden-on-scroll');
            } else {
                porkyNav.classList.remove('porky-nav-hidden-on-scroll');
            }

            lastScrollY = currentScrollY;
            ticking = false;
        };

        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(updateMobileNavVisibility);
                ticking = true;
            }
        }, { passive: true });
    }
</script>

</body>
</html>
