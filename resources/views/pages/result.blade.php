@extends('layouts.app')

@section('content')
{{-- Prepare result display values --}}
@php
    $isEsp32 = $result['source'] === 'esp32';
    $resultClass = $result['prediction_class'] ?: 'unknown';
@endphp

{{-- Analysis result page --}}
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
            <p>{{ $result['source_label'] }} &middot; {{ $result['analysis_type'] }}</p>
        </div>

        {{-- Result details grid --}}
        <div class="result-grid">
            <div class="result-left">
                @unless ($isEsp32)
                    <div class="result-card">
                        <h2>Sample Image</h2>

                        <div class="result-image-wrap">
                            <img src="{{ $result['image_url'] }}" alt="Pork sample used for analysis" data-fallback-image="/images/Porky%20Logo.png">
                        </div>
                    </div>
                @endunless

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

                    <p id="aiInsightText" class="insight-text">
                        {{ $result['ai_insight'] }}
                    </p>
                    <p id="aiInsightNote" class="insight-note" hidden></p>

                    <div class="detected-section">
                        <h3>Analysis Summary</h3>

                        <div class="indicator-list">
                            <div class="indicator-item">
                                <span class="indicator-dot blue"></span>
                                <div>
                                    <h4>Source</h4>
                                    <p>{{ $result['source_label'] }}</p>
                                </div>
                            </div>

                            <div class="indicator-item">
                                <span class="indicator-dot purple"></span>
                                <div>
                                    <h4>Prediction</h4>
                                    <p>{{ $result['prediction_label'] }}</p>
                                </div>
                            </div>

                            <div class="indicator-item">
                                <span class="indicator-dot green"></span>
                                <div>
                                    <h4>Analysis Type</h4>
                                    <p>{{ $result['analysis_type'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="result-card recommendation-card {{ $resultClass }}">
                    <h2>Recommendation</h2>
                    <p>{{ $result['recommendation'] }}</p>
                </div>
            </div>

            <div class="result-right">
                <div class="result-card freshness-card {{ $resultClass }}">
                    <div class="freshness-icon-box">
                        @if ($result['prediction'] === 'not_fresh')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        @endif
                    </div>

                    <h2>{{ $result['prediction_label'] }}</h2>
                    <p class="freshness-subtitle">{{ $result['result_message'] }}</p>

                    <div class="freshness-divider"></div>

                    <p class="freshness-description">
                        Confidence: {{ $result['confidence_label'] }}
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
                        <span class="confidence-value">{{ $result['confidence_label'] }}</span>
                        <span class="confidence-label">Confidence</span>
                    </div>

                    <div class="confidence-bar">
                        <div class="confidence-fill {{ $resultClass }}" style="width: {{ min(100, max(0, $result['confidence'])) }}%;"></div>
                    </div>

                    <p class="confidence-note">
                        Confidence comes from the classifier output saved with this history record.
                    </p>
                </div>

                <div class="result-card details-card">
                    <h2>Scan Details</h2>

                    @if ($isEsp32)
                        <div class="detail-item">
                            <div>
                                <h3>Gas</h3>
                                <p>{{ $result['gas'] ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="detail-item">
                            <div>
                                <h3>Temperature</h3>
                                <p>
                                    @if ($result['temperature'] !== null)
                                        {{ $result['temperature'] }}&deg;C
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="detail-item">
                            <div>
                                <h3>Humidity</h3>
                                <p>
                                    @if (is_numeric($result['humidity']))
                                        {{ $result['humidity'] }}%
                                    @else
                                        {{ $result['humidity'] ?? 'N/A' }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="detail-item">
                            <div>
                                <h3>Timestamp</h3>
                                <p>{{ $result['date_label'] }}</p>
                            </div>
                        </div>

                        <div class="detail-item">
                            <div>
                                <h3>Device ID</h3>
                                <p>{{ $result['device_id'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                    @else
                        <p class="no-sensor-note">
                            Scan details are not available because this result was generated from an uploaded image.
                        </p>

                        <div class="detail-item">
                            <div>
                                <h3>Source</h3>
                                <p>Uploaded Image</p>
                            </div>
                        </div>

                        <div class="detail-item">
                            <div>
                                <h3>Analysis Type</h3>
                                <p>Image Classification Only</p>
                            </div>
                        </div>
                    @endif
                </div>

                <a href="{{ route('scan') }}" class="result-action-btn secondary-btn">
                    <span>New Scan</span>
                </a>
            </div>
        </div>
    </div>
</div>
<script>
    // Requests an AI insight for this scan result.
    document.addEventListener('DOMContentLoaded', async () => {
        const insightText = document.getElementById('aiInsightText');
        const insightNote = document.getElementById('aiInsightNote');

        try {
            insightNote.hidden = false;
            insightNote.textContent = 'Generating AI insight...';

            const response = await fetch("{{ route('result.ai-insight', ['historyId' => $historyId]) }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({}),
            });

            const data = await response.json();

            if (response.status === 429) {
                insightNote.textContent = data.message || 'AI insight limit reached. Showing local insight.';
                return;
            }

            if (!response.ok || !data.insight) {
                insightNote.textContent = 'AI insight unavailable. Showing local insight.';
                return;
            }

            insightText.textContent = data.insight;
            insightNote.hidden = true;
            insightNote.textContent = '';
        } catch (error) {
            console.error(error);
            insightNote.hidden = false;
            insightNote.textContent = 'AI insight unavailable. Showing local insight.';
        }
    });
</script>
@endsection
