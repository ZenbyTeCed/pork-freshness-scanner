<header class="porky-header">
    <div class="porky-container">
        <div class="porky-header-inner">

            <div class="porky-brand">
                <div class="porky-logo">
                    <img src="{{ asset('images/logo.png') }}" alt="PORKY Logo">
                </div>
                <div class="porky-title">
                    <h1>PORKY</h1>
                    <p>AI Freshness Grading</p>
                </div>
            </div>

            <nav class="porky-nav">
                <a href="{{ route('home') }}">Home</a>

                <div id="authLinks" class="porky-nav-group" style="display: none;">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <a href="{{ route('scan') }}">Scan</a>
                    <a href="{{ route('history') }}">History</a>
                    <a href="{{ route('reports') }}">Reports</a>
                    <a href="{{ route('settings') }}">Settings</a>
                    <a href="#" id="logoutBtn">Logout</a>
                </div>

                <div id="guestLinks" class="porky-nav-group">
                    <a href="{{ route('login') }}">Login</a>
                </div>
            </nav>

        </div>
    </div>
</header>