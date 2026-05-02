@extends('layouts.app')

@section('content')
<div class="dashboard-page">
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Dashboard</h1>
            <p>Monitor real-time pork freshness analytics</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon blue">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7V5a2 2 0 0 1 2-2h2M3 17v2a2 2 0 0 0 2 2h2M17 3h2a2 2 0 0 1 2 2v2M17 21h2a2 2 0 0 0 2-2v-2M7 7h.01M7 12h.01M7 17h.01M12 7h.01M12 12h.01M12 17h.01M17 7h.01M17 12h.01M17 17h.01" />
                        </svg>
                    </div>
                    <span class="stat-badge green">All time</span>
                </div>
                <h2>{{ $totalScans }}</h2>
                <p>Total Scans</p>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon mint">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h3l3 8 4-16 3 8h5" />
                        </svg>
                    </div>
                    <span class="stat-badge blue">Today</span>
                </div>
                <h2>{{ $scansToday }}</h2>
                <p>Scans Today</p>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon purple">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 7-7" />
                        </svg>
                    </div>
                    <span class="stat-badge purple-text">Average</span>
                </div>
                <h2>{{ number_format($averageConfidence, 1) }}%</h2>
                <p>Avg. Confidence</p>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon yellow">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <span class="stat-badge gray">Live</span>
                </div>
                <h2 class="status-active">&bull; Active</h2>
                <p>System Status</p>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <h3>Freshness Distribution</h3>
                <div class="chart-canvas-wrap">
                    <canvas id="predictionBarChart"></canvas>
                </div>

                <div class="chart-summary">
                    @foreach ($predictionSummary as $summary)
                        <div>
                            <h4>{{ $summary['count'] }}</h4>
                            <p>{{ $summary['label'] }}</p>
                            <span>{{ $summary['percent'] }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="chart-card">
                <h3>Quality Breakdown</h3>
                <div class="chart-canvas-wrap pie-wrap">
                    <canvas id="qualityPieChart"></canvas>
                </div>

                <div class="pie-legend">
                    <span><i class="legend-dot green"></i> Fresh: {{ $predictionCounts[0] }}</span>
                    <span><i class="legend-dot red"></i> Not Fresh: {{ $predictionCounts[1] }}</span>
                </div>
            </div>
        </div>

        <div class="recent-card">
            <div class="recent-header">
                <h3>Recent Activity</h3>
                <a href="{{ route('history') }}">View all</a>
            </div>

            @forelse ($recentActivities as $activity)
                <a href="{{ route('result', ['historyId' => $activity['id']]) }}" class="activity-row">
                    <div class="activity-left">
                        <img src="{{ $activity['image_url'] }}" alt="Pork sample">

                        <div class="activity-info">
                            <div class="activity-top">
                                <span class="prediction-tag {{ $activity['prediction_class'] }}">{{ $activity['prediction_label'] }}</span>
                                <span class="confidence">{{ $activity['confidence_label'] }} confidence</span>
                            </div>
                            <p>{{ $activity['source_label'] }} &bull; {{ $activity['date_label'] }}</p>
                        </div>
                    </div>

                    <div class="activity-arrow">&rsaquo;</div>
                </a>
            @empty
                <div class="dashboard-empty-state">
                    <h4>No scan activity yet</h4>
                    <p>Upload an image or use the ESP32-CAM scanner to populate your dashboard.</p>
                    <a href="{{ route('scan') }}">Start a scan</a>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartLabels = @json($predictionLabels);
    const chartData = @json($predictionCounts);
    const commonColors = ['#22c55e', '#ef4444'];

    const barCtx = document.getElementById('predictionBarChart');
    if (barCtx) {
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [{
                    data: chartData,
                    backgroundColor: commonColors,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (context) => `${context.label}: ${context.raw}`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#64748b',
                            font: { family: 'Inter' }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            color: '#64748b',
                            precision: 0,
                            font: { family: 'Inter' }
                        },
                        grid: { color: '#e2e8f0' },
                        border: { display: false }
                    }
                }
            }
        });
    }

    const pieCtx = document.getElementById('qualityPieChart');
    if (pieCtx) {
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: chartLabels,
                datasets: [{
                    data: chartData,
                    backgroundColor: commonColors,
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((sum, value) => sum + value, 0);
                                const value = context.raw;
                                const percent = total > 0 ? ((value / total) * 100).toFixed(0) : 0;

                                return `${context.label}: ${value} (${percent}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
</script>
@endsection
