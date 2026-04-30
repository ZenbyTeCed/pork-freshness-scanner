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

                    <p class="grade-description" id="classificationDescription">
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
                            <h3>Classification</h3>
                            <p id="classificationLabel">N/A</p>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 12h7.5m-7.5 5.25h7.5M4.5 6.75h.008v.008H4.5V6.75Zm0 5.25h.008v.008H4.5V12Zm0 5.25h.008v.008H4.5v-.008Z" />
                            </svg>
                        </div>
                        <div>
                            <h3>MQ135</h3>
                            <p id="mq135Value">N/A</p>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75v8.25m0 0a3.75 3.75 0 1 1-2.25 6.75m2.25-6.75a3.75 3.75 0 0 0 2.25 6.75M9.75 18.75V5.25a2.25 2.25 0 1 1 4.5 0v13.5" />
                            </svg>
                        </div>
                        <div>
                            <h3>Temperature</h3>
                            <p id="temperatureValue">N/A</p>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75s5.25 5.625 5.25 10.125a5.25 5.25 0 1 1-10.5 0C6.75 9.375 12 3.75 12 3.75Z" />
                            </svg>
                        </div>
                        <div>
                            <h3>Humidity</h3>
                            <p id="humidityValue">N/A</p>
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
    async function loadLatestScan() {
        try {
            const placeholderImage = @json(asset('images/Porky Logo.png'));
            const response = await fetch('/api/latest-scan', {
                cache: 'no-store',
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            console.log(data);

            // Populate image
            document.getElementById('resultImage').src = data.image_url || placeholderImage;

            // Populate classification and grade
            const classification = data.classification;
            const grade = data.grade || getGradeFromClassification(classification);
            document.getElementById('gradeDisplay').textContent = `Grade ${grade}`;
            document.getElementById('gradeSubtitle').textContent = getGradeSubtitle(grade);
            document.getElementById('classificationLabel').textContent = formatClassification(classification);
            document.getElementById('classificationDescription').textContent = getClassificationDescription(classification, grade);

            // Populate confidence
            const confidence = normalizeConfidence(data.confidence);
            document.getElementById('confidenceValue').textContent = `${confidence.toFixed(1)}%`;
            document.getElementById('confidenceFill').style.width = `${confidence}%`;

            // Populate details
            const temperatureUnit = `${String.fromCharCode(176)}C`;
            document.getElementById('scanTimestamp').textContent = formatTimestamp(data.created_at);
            document.getElementById('mq135Value').textContent = formatValue(data.mq135);
            document.getElementById('temperatureValue').textContent = formatValue(data.temperature, temperatureUnit);
            document.getElementById('humidityValue').textContent = formatValue(data.humidity, '%');

            // Populate indicators
            const indicators = document.querySelectorAll('.indicator-item');
            if (indicators.length >= 3) {
                indicators[0].querySelector('p').textContent = formatClassification(classification);
                indicators[1].querySelector('p').textContent = `MQ135: ${formatValue(data.mq135)}`;
                indicators[2].querySelector('p').textContent = `DHT22: ${formatValue(data.temperature, temperatureUnit)} / ${formatValue(data.humidity, '%')}`;
            }

            // Populate recommendation
            const recommendationCard = document.querySelector('.recommendation-card p');
            recommendationCard.textContent = getRecommendation(grade);

        } catch (error) {
            console.error('Error loading scan result:', error);
            alert('Error loading scan result');
        }
    }

    document.addEventListener('DOMContentLoaded', loadLatestScan);

    window.addEventListener('load', function() {
        setTimeout(loadLatestScan, 250);
    });

    function normalizeConfidence(confidence) {
        const value = Number(confidence);

        if (Number.isNaN(value)) {
            return 0;
        }

        return value <= 1 ? value * 100 : value;
    }

    function formatTimestamp(timestamp) {
        if (!timestamp) return 'N/A';

        // Convert seconds → milliseconds
        const numericTimestamp = Number(timestamp);
        const date = Number.isNaN(numericTimestamp)
            ? new Date(timestamp)
            : new Date(numericTimestamp < 10000000000 ? numericTimestamp * 1000 : numericTimestamp);

        if (isNaN(date.getTime())) return 'N/A';

        return date.toLocaleDateString(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    function formatValue(value, suffix = '') {
        if (value === null || value === undefined || value === '') {
            return 'N/A';
        }

        return `${value}${suffix}`;
    }

    function formatClassification(classification) {
        const labels = {
            'fresh': 'Fresh',
            'half_fresh': 'Half Fresh',
            'spoiled': 'Spoiled'
        };

        return labels[classification] || 'N/A';
    }

    function getGradeFromClassification(classification) {
        const grades = {
            'fresh': 'A',
            'half_fresh': 'B',
            'spoiled': 'C'
        };

        return grades[classification] || 'N/A';
    }

    function getClassificationDescription(classification, grade) {
        const descriptions = {
            'fresh': 'Edge Impulse classified this sample as Fresh.',
            'half_fresh': 'Edge Impulse classified this sample as Half Fresh.',
            'spoiled': 'Edge Impulse classified this sample as Spoiled.'
        };

        return descriptions[classification] || `Classification unavailable for Grade ${grade}.`;
    }

    function getGradeSubtitle(grade) {
        const subtitles = {
            'A': 'Excellent Freshness',
            'B': 'Good Freshness',
            'C': 'Fair Freshness'
        };
        return subtitles[grade] || 'Unknown';
    }

    function getGradeColorIndicator(grade) {
        const indicators = {
            'A': 'Light pink to reddish',
            'B': 'Acceptable color',
            'C': 'Visible quality changes'
        };
        return indicators[grade] || 'N/A';
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
