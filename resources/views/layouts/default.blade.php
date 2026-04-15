<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PORKY</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['public/css/general.css', 'public/css/login-register.css'])
    @vite(['resources/js/auth.js'])
</head>
<body class="porky-body">

    <!-- SIMPLE HEADER -->
    <header class="porky-auth-header-bar">
        <div class="porky-container porky-header-flex">
            <div class="porky-auth-brand">
                <div class="porky-auth-logo-small">P</div>
                <span>PORKY</span>
            </div>

            <nav class="porky-auth-nav">
                <a href="{{ route('home') }}">Home</a>
            </nav>
        </div>
    </header>

    <main class="porky-main">
        @yield('content')
    </main>

    <!-- SIMPLE FOOTER -->
    <footer class="porky-auth-footer">
        <div class="porky-container">
            <p>&copy; {{ now()->year }} PORKY. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>

<script>
    window.firebaseConfig = {
        apiKey: "{{ env('FIREBASE_API_KEY') }}",
        authDomain: "{{ env('FIREBASE_AUTH_DOMAIN') }}",
        projectId: "{{ env('FIREBASE_PROJECT_ID') }}",
        appId: "{{ env('FIREBASE_APP_ID') }}"
    };
</script>