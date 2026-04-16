@extends('layouts.app')

@section('content')
<section class="porky-hero">
    <div class="porky-container">
        <div class="porky-hero-content">
            <div class="porky-badge">
                <span class="porky-badge-dot"></span>
                IoT + Edge AI Platform
            </div>

            <h1 class="porky-hero-title">
                AI-Based Pork <br>
                Freshness Grading System
            </h1>

            <p class="porky-hero-text">
                Assess pork quality instantly using image-based analysis. Powered by
                ESP32-CAM and real-time AI processing.
            </p>

            <div class="porky-hero-actions">
                @if (session()->has('firebase_uid'))
                    <a href="{{ route('scan') }}" class="porky-btn porky-btn-primary">
                        Start Scanning
                    </a>

                    <a href="{{ route('dashboard') }}" class="porky-btn porky-btn-secondary">
                        View Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="porky-btn porky-btn-primary">
                        Login to Start
                    </a>

                    <a href="{{ route('register') }}" class="porky-btn porky-btn-secondary">
                        Create Account
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>

<section class="porky-section">
    <div class="porky-container">
        <div class="porky-section-heading">
            <h2>How It Works</h2>
            <p>Three simple steps to grade pork freshness</p>
        </div>

        <div class="porky-steps">
            <div class="porky-step-card">
                <div class="porky-step-icon porky-step-icon-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="28">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 7h4l2-2h6l2 2h4v12H3V7z" />
                    </svg>
                </div>
                <span class="porky-step-label">Step 1</span>
                <h3>Capture</h3>
                <p>Use ESP32-CAM or upload an image of the pork sample for analysis.</p>
            </div>

            <div class="porky-step-arrow">›</div>

            <div class="porky-step-card">
                <div class="porky-step-icon porky-step-icon-purple">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="28">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <span class="porky-step-label">Step 2</span>
                <h3>Analyze</h3>
                <p>AI algorithms assess color, texture, and surface conditions in real-time.</p>
            </div>

            <div class="porky-step-arrow">›</div>

            <div class="porky-step-card">
                <div class="porky-step-icon porky-step-icon-green">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="28">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <span class="porky-step-label">Step 3</span>
                <h3>Result</h3>
                <p>Receive instant grading (A, B, or C) with confidence scores and recommendations.</p>
            </div>
        </div>
    </div>
</section>

<section class="porky-section porky-section-alt">
    <div class="porky-container">
        <div class="porky-section-heading">
            <h2>Key Features</h2>
            <p>Professional-grade freshness assessment technology</p>
        </div>

        <div class="porky-features-grid">
            <div class="porky-feature-card">
                <div class="porky-feature-icon porky-feature-green">
                    <!-- Clock / Real-time -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3M12 2a10 10 0 100 20 10 10 0 000-20z" />
                    </svg>
                </div>
                <h3>Real-time Analysis</h3>
                <p>Instant AI-powered freshness grading with high accuracy.</p>
            </div>

            <div class="porky-feature-card">
                <div class="porky-feature-icon porky-feature-blue">
                    <!-- Camera -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 7h4l2-2h6l2 2h4v12H3V7z" />
                    </svg>
                </div>
                <h3>ESP32-CAM Integration</h3>
                <p>IoT edge computing for seamless image capture.</p>
            </div>

            <div class="porky-feature-card">
                <div class="porky-feature-icon porky-feature-purple">
                    <!-- Trending Up -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 17l6-6 4 4 8-8" />
                    </svg>
                </div>
                <h3>Live Dashboard</h3>
                <p>Monitor trends and statistics with Firebase real-time updates.</p>
            </div>

            <div class="porky-feature-card">
                <div class="porky-feature-icon porky-feature-yellow">
                    <!-- Shield -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 3l7 4v5c0 5-3.5 9-7 9s-7-4-7-9V7l7-4z" />
                    </svg>
                </div>
                <h3>Quality Assurance</h3>
                <p>Confidence scores and detailed AI insights for every scan.</p>
            </div>
        </div>
    </div>
</section>

<section class="porky-section">
    <div class="porky-container">
        <div class="porky-disclaimer-card">
            <div class="porky-disclaimer-icon">🛡</div>
            <div class="porky-disclaimer-content">
                <h3>System Scope & Disclaimer</h3>
                <p>
                    This system evaluates pork freshness based on visible image features only and does not replace
                    laboratory testing. Results should be used as a preliminary assessment tool and supplemented
                    with standard food safety protocols.
                </p>
            </div>
        </div>
    </div>
</section>
@endsection