<!DOCTYPE html>
<html lang="en">
<head>
    {{-- Main app layout head --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Porky</title>
    <link rel="icon" type="image/png" href="/images/Porky%20Vite.png">

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

    {{-- Firebase settings for frontend scripts --}}
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

    {{-- Page content slot --}}
    <main class="porky-main">
        @yield('content')
    </main>

    {{-- Shared footer --}}
    @include('partials.footer')

    {{-- Logout confirmation modal --}}
    <div id="logoutConfirmModal" class="logout-confirm-modal" hidden>
        <div class="logout-confirm-backdrop" data-logout-cancel></div>

        <div class="logout-confirm-panel" role="dialog" aria-modal="true" aria-labelledby="logoutConfirmTitle">
            <div class="logout-confirm-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
                </svg>
            </div>

            <h2 id="logoutConfirmTitle">Log out?</h2>
            <p>You will need to sign in again to access your scans and history.</p>

            <div class="logout-confirm-actions">
                <button type="button" id="logoutCancelBtn" class="logout-confirm-btn secondary">Cancel</button>
                <button type="button" id="logoutConfirmBtn" class="logout-confirm-btn primary">Logout</button>
            </div>
        </div>
    </div>

<script type="module">
    // Handles logout, mobile menu, and image fallback behavior.
    import { auth } from "{{ Vite::asset('resources/js/auth.js') }}";
    import { onAuthStateChanged, signOut } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";

    // const authLinks = document.getElementById("authLinks");
    // const guestLinks = document.getElementById("guestLinks");
    const logoutBtn = document.getElementById("logoutBtn");
    const mobileLogoutBtn = document.getElementById("mobileLogoutBtn");
    const mobileAccountToggle = document.getElementById("mobileAccountToggle");
    const mobileAccountMenu = document.getElementById("mobileAccountMenu");
    const logoutConfirmModal = document.getElementById("logoutConfirmModal");
    const logoutCancelBtn = document.getElementById("logoutCancelBtn");
    const logoutConfirmBtn = document.getElementById("logoutConfirmBtn");

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

    const closeMobileAccountMenu = () => {
        if (!mobileAccountMenu || !mobileAccountToggle) return;

        mobileAccountMenu.classList.remove("is-open");
        mobileAccountToggle.setAttribute("aria-expanded", "false");
    };

    const toggleMobileAccountMenu = () => {
        if (!mobileAccountMenu || !mobileAccountToggle) return;

        const shouldOpen = !mobileAccountMenu.classList.contains("is-open");
        mobileAccountMenu.hidden = false;
        mobileAccountMenu.classList.toggle("is-open", shouldOpen);
        mobileAccountToggle.setAttribute("aria-expanded", shouldOpen ? "true" : "false");
    };

    const openLogoutModal = () => {
        closeMobileAccountMenu();

        if (!logoutConfirmModal) {
            logoutUser();
            return;
        }

        logoutConfirmModal.hidden = false;
        document.body.classList.add("modal-open");
        logoutConfirmBtn?.focus();
    };

    const closeLogoutModal = () => {
        if (!logoutConfirmModal) {
            return;
        }

        logoutConfirmModal.hidden = true;
        document.body.classList.remove("modal-open");
    };

    const logoutUser = async () => {
        if (logoutConfirmBtn) {
            logoutConfirmBtn.disabled = true;
            logoutConfirmBtn.textContent = "Logging out...";
        }

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
    };

    logoutBtn?.addEventListener("click", async (e) => {
        e.preventDefault();
        openLogoutModal();
    });

    mobileLogoutBtn?.addEventListener("click", async (e) => {
        e.preventDefault();
        openLogoutModal();
    });

    logoutConfirmBtn?.addEventListener("click", logoutUser);
    logoutCancelBtn?.addEventListener("click", closeLogoutModal);
    logoutConfirmModal?.addEventListener("click", (e) => {
        if (e.target?.hasAttribute("data-logout-cancel")) {
            closeLogoutModal();
        }
    });

    mobileAccountToggle?.addEventListener("click", (e) => {
        e.preventDefault();
        toggleMobileAccountMenu();
    });

    document.addEventListener("click", (e) => {
        if (
            mobileAccountMenu &&
            mobileAccountToggle &&
            mobileAccountMenu.classList.contains("is-open") &&
            !mobileAccountMenu.contains(e.target) &&
            !mobileAccountToggle.contains(e.target)
        ) {
            closeMobileAccountMenu();
        }
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            closeMobileAccountMenu();
            closeLogoutModal();
        }
    });

    const porkyNav = document.querySelector('.porky-nav');

    document.querySelectorAll('img[data-fallback-image]').forEach((image) => {
        image.addEventListener('error', () => {
            const fallback = image.getAttribute('data-fallback-image');

            if (fallback && image.src !== new URL(fallback, window.location.origin).href) {
                image.src = fallback;
            }
        }, { once: true });
    });

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
                closeMobileAccountMenu();
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
