<header class="porky-header">
    <div class="porky-container">
        <div class="porky-header-inner">

            <a href="{{ route('home') }}" class="porky-brand">
                <div class="porky-logo">
                    <img src="{{ asset('images/Porky Logo.png') }}" alt="PORKY Logo">
                </div>
                <div class="porky-title">
                    <h1>PORKY</h1>
                    <p>AI Freshness Grading</p>
                </div>
            </a>

            <nav class="porky-nav">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12 12 3l9.75 9M4.5 10.5v9a1.5 1.5 0 0 0 1.5 1.5h3.75v-6h4.5v6H18a1.5 1.5 0 0 0 1.5-1.5v-9" />
                    </svg>
                    <span>Home</span>
                </a>

                @if (session()->has('firebase_uid'))
                    <div id="authLinks" class="porky-nav-group">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                        <a href="{{ route('scan') }}" class="nav-link {{ request()->routeIs('scan') ? 'active' : '' }}">Scan</a>
                        <a href="{{ route('history') }}" class="nav-link {{ request()->routeIs('history') ? 'active' : '' }}">History</a>
                        <a href="{{ route('settings') }}" class="nav-link {{ request()->routeIs('settings') ? 'active' : '' }}">Settings</a>

                        <form id="logoutForm" action="{{ route('logout') }}" method="POST" class="porky-logout-form">
                            @csrf
                            <a type="button" id="logoutBtn" class="nav-link">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
                                </svg>
                                <span>Logout</span>
                            </a>
                        </form>
                    </div>
                @else
                    <div id="guestLinks" class="porky-nav-group">
                        <a href="{{ route('login') }}" class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m-3-3 3-3m0 0 3 3m-3-3v12" />
                            </svg>
                            <span>Login</span>
                        </a>
                    </div>
                @endif
            </nav>

        </div>
    </div>
</header>