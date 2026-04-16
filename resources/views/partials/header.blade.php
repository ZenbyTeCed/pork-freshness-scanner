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
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>

                <div id="authLinks" class="porky-nav-group porky-nav-hidden">
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>

                    <a href="{{ route('scan') }}" class="{{ request()->routeIs('scan') ? 'active' : '' }}">Scan</a>

                    <a href="{{ route('history') }}" class="{{ request()->routeIs('history') ? 'active' : '' }}">History</a>

                    <a href="{{ route('reports') }}" class="{{ request()->routeIs('reports') ? 'active' : '' }}">Reports</a>

                    <a href="{{ route('settings') }}" class="{{ request()->routeIs('settings') ? 'active' : '' }}">Settings</a>

                    <form id="logoutForm" action="{{ route('logout') }}" method="POST" class="porky-logout-form">
                        @csrf
                        <a href="#" id="logoutBtn">Logout</a>
                    </form>
                </div>
            </nav>

        </div>
    </div>
</header>