@extends('layouts.app')

@section('content')
<div class="result-page">
    <div class="result-container">
        <a href="{{ route('history') }}" class="result-back-link">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 19.5-7.5-7.5 7.5-7.5M3 12h18" />
            </svg>
            <span>Back to History</span>
        </a>

        <div class="result-header">
            <h1>Analysis Result</h1>
        </div>

        <div class="result-grid">
            <div class="result-left">
                <div class="result-card">
                    <h2>Sample Image</h2>

                    <div class="result-image-wrap">
                        <img id="resultImage" src="" alt="Pork sample">
                    </div>
                </div>

                <div class="result-card insight-card">
                    <div class="section-title with-icon">
                        <div class="section-icon green-soft">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </div>
                        <h2>AI Insight</h2>
                    </div>

                    <p class="insight-text">
                        The analyzed sample shows optimal freshness characteristics. The meat displays a healthy pink color
                        with good moisture retention. Surface texture appears firm with no signs of discoloration or slime formation.
                    </p>

                    <div class="detected-section">
                        <h3>Visual Indicators Detected:</h3>

                        <div class="indicator-list">
                            <div class="indicator-item">
                                <span class="indicator-dot blue"></span>
                                <div>
                                    <h4>Color</h4>
                                    <p>Light pink to reddish</p>
                                </div>
                            </div>

                            <div class="indicator-item">
                                <span class="indicator-dot purple"></span>
                                <div>
                                    <h4>Surface Condition</h4>
                                    <p>Moist, not slimy</p>
                                </div>
                            </div>

                            <div class="indicator-item">
                                <span class="indicator-dot green"></span>
                                <div>
                                    <h4>Texture</h4>
                                    <p>Firm and elastic</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="result-card recommendation-card">
                    <h2>Recommendation</h2>
                    <p>
                        Excellent quality. Safe for immediate consumption or storage. Recommended for all cooking methods.
                    </p>
                </div>
            </div>

            <div class="result-right">
                <div class="result-card grade-card">
                    <div class="grade-icon-box">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </div>

                    <h2 id="gradeDisplay">Grade A</h2>
                    <p class="grade-subtitle" id="gradeSubtitle">Excellent Freshness</p>

                    <div class="grade-divider"></div>

                    <p class="grade-description">
                        Premium quality pork with optimal freshness indicators
                    </p>
                </div>

                <div class="result-card confidence-card">
                    <div class="section-title with-icon confidence-title">
                        <div class="section-icon purple-soft">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6.75 6.75 0 1 0 0-13.5 6.75 6.75 0 0 0 0 13.5Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 12V8.25m0 0 2.25 2.25M12 8.25 9.75 10.5" />
                            </svg>
                        </div>
                        <h2>Confidence Score</h2>
                    </div>

                    <div class="confidence-row">
                        <span class="confidence-value" id="confidenceValue">96.5%</span>
                        <span class="confidence-label">Accuracy</span>
                    </div>

                    <div class="confidence-bar">
                        <div class="confidence-fill" id="confidenceFill" style="width: 96.5%;"></div>
                    </div>

                    <p class="confidence-note">
                        High confidence indicates reliable analysis based on clear visual indicators
                    </p>
                </div>

                <div class="result-card details-card">
                    <h2>Scan Details</h2>

                    <div class="detail-item">
                        <div class="detail-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V8.25A2.25 2.25 0 0 1 5.25 6h13.5A2.25 2.25 0 0 1 21 8.25v10.5M3 18.75A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75M3 18.75v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                        </div>
                        <div>
                            <h3>Timestamp</h3>
                            <p id="scanTimestamp">April 15, 2026 at 10:30 AM</p>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 12h7.5m-7.5 5.25h7.5M4.5 6.75h.008v.008H4.5V6.75Zm0 5.25h.008v.008H4.5V12Zm0 5.25h.008v.008H4.5v-.008Z" />
                            </svg>
                        </div>
                        <div>
                            <h3>Scan ID</h3>
                            <p id="scanIdDisplay">SCAN-000001</p>
                        </div>
                    </div>
                </div>

                <button type="button" class="result-action-btn primary-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M4.5 15.75v1.5A2.25 2.25 0 0 0 6.75 19.5h10.5a2.25 2.25 0 0 0 2.25-2.25v-1.5" />
                    </svg>
                    <span>Download Report</span>
                </button>

                <a href="{{ route('scan') }}" class="result-action-btn secondary-btn">
                    <span>New Scan</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async function() {
        const urlParams = new URLSearchParams(window.location.search);
        const scanId = urlParams.get('scan_id');

        if (!scanId) {
            alert('No scan ID provided');
            window.location.href = '/scan';
            return;
        }

        try {
            const response = await axios.get(`/api/scans/${scanId}`);
            const data = response.data;

            // Populate image
            document.getElementById('resultImage').src = `/storage/${data.scan.image_path}`;

            // Populate grade
            const grade = data.result.grade;
            document.getElementById('gradeDisplay').textContent = `Grade ${grade}`;
            document.getElementById('gradeSubtitle').textContent = getGradeSubtitle(grade);

            // Populate confidence
            const confidence = data.result.confidence * 100;
            document.getElementById('confidenceValue').textContent = `${confidence.toFixed(1)}%`;
            document.getElementById('confidenceFill').style.width = `${confidence}%`;

            // Populate details
            document.getElementById('scanIdDisplay').textContent = scanId;
            document.getElementById('scanTimestamp').textContent = new Date(data.scan.created_at).toLocaleString();

            // Populate indicators
            const indicators = document.querySelectorAll('.indicator-item');
            const details = data.result.details;
            if (indicators.length >= 3) {
                indicators[0].querySelector('p').textContent = details.color || 'N/A';
                indicators[1].querySelector('p').textContent = details.surface || 'N/A';
                indicators[2].querySelector('p').textContent = details.texture || 'N/A';
            }

            // Populate recommendation
            const recommendationCard = document.querySelector('.recommendation-card p');
            recommendationCard.textContent = getRecommendation(grade);

        } catch (error) {
            console.error('Error loading scan result:', error);
            alert('Error loading scan result');
        }
    });

    function getGradeSubtitle(grade) {
        const subtitles = {
            'A': 'Excellent Freshness',
            'B': 'Good Freshness',
            'C': 'Fair Freshness'
        };
        return subtitles[grade] || 'Unknown';
    }

    function getRecommendation(grade) {
        const recommendations = {
            'A': 'Excellent quality. Safe for immediate consumption or storage. Recommended for all cooking methods.',
            'B': 'Good quality. Suitable for consumption with proper cooking. Store refrigerated.',
            'C': 'Fair quality. Use soon or consider alternative options. Ensure thorough cooking.'
        };
        return recommendations[grade] || 'Quality assessment available. Consult guidelines.';
    }
</script>
@endsection