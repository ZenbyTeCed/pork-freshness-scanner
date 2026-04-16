@extends('layouts.app')

@section('content')
<div class="dashboard-page">
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Dashboard</h1>
            <p>Monitor real-time pork freshness grading analytics</p>
        </div>

        <div class="stats-grid">

            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon blue">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 7V5a2 2 0 012-2h2M3 17v2a2 2 0 002 2h2M17 3h2a2 2 0 012 2v2M17 21h2a2 2 0 002-2v-2M7 7h.01M7 12h.01M7 17h.01M12 7h.01M12 12h.01M12 17h.01M17 7h.01M17 12h.01M17 17h.01"/>
                        </svg>
                    </div>
                    <span class="stat-badge green">All time</span>
                </div>
                <h2>6</h2>
                <p>Total Scans</p>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon mint">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 12h3l3 8 4-16 3 8h5"/>
                        </svg>
                    </div>
                    <span class="stat-badge blue">Today</span>
                </div>
                <h2>3</h2>
                <p>Scans Today</p>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon purple">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 17l6-6 4 4 7-7"/>
                        </svg>
                    </div>
                    <span class="stat-badge purple-text">Average</span>
                </div>
                <h2>92.9%</h2>
                <p>Avg. Confidence</p>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon yellow">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6v6l4 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="stat-badge gray">Live</span>
                </div>
                <h2 class="status-active">• Active</h2>
                <p>System Status</p>
            </div>
        </div>

        @php
            $gradeCounts = [3, 2, 1];
            $gradeLabels = ['Grade A', 'Grade B', 'Grade C'];

            $activities = [
                ['grade' => 'Grade A', 'confidence' => '96.5%', 'time' => '1d ago', 'class' => 'a'],
                ['grade' => 'Grade B', 'confidence' => '88.3%', 'time' => '1d ago', 'class' => 'b'],
                ['grade' => 'Grade A', 'confidence' => '94.2%', 'time' => '1d ago', 'class' => 'a'],
                ['grade' => 'Grade C', 'confidence' => '91.7%', 'time' => '1d ago', 'class' => 'c'],
                ['grade' => 'Grade B', 'confidence' => '89.1%', 'time' => '1d ago', 'class' => 'b'],
            ];
        @endphp

        <div class="charts-grid">
            <div class="chart-card">
                <h3>Grade Distribution</h3>
                <div class="chart-canvas-wrap">
                    <canvas id="gradeBarChart"></canvas>
                </div>

                <div class="chart-summary">
                    <div>
                        <h4>3</h4>
                        <p>Grade A</p>
                        <span>50%</span>
                    </div>
                    <div>
                        <h4>2</h4>
                        <p>Grade B</p>
                        <span>33%</span>
                    </div>
                    <div>
                        <h4>1</h4>
                        <p>Grade C</p>
                        <span>17%</span>
                    </div>
                </div>
            </div>

            <div class="chart-card">
                <h3>Quality Breakdown</h3>
                <div class="chart-canvas-wrap pie-wrap">
                    <canvas id="qualityPieChart"></canvas>
                </div>

                <div class="pie-legend">
                    <span><i class="legend-dot green"></i> 3</span>
                    <span><i class="legend-dot orange"></i> 2</span>
                    <span><i class="legend-dot red"></i> 1</span>
                </div>
            </div>
        </div>

        <div class="recent-card">
            <div class="recent-header">
                <h3>Recent Activity</h3>
                <a href="#">View all →</a>
            </div>

            @foreach ($activities as $activity)
                <div class="activity-row">
                    <div class="activity-left">
                        <img src="https://images.unsplash.com/photo-1603048297172-c92544798d5a?auto=format&fit=crop&w=100&q=80" alt="Pork image">
                        <div class="activity-info">
                            <div class="activity-top">
                                <span class="grade-tag {{ $activity['class'] }}">{{ $activity['grade'] }}</span>
                                <span class="confidence">{{ $activity['confidence'] }} confidence</span>
                            </div>
                            <p>{{ $activity['time'] }}</p>
                        </div>
                    </div>
                    <div class="activity-arrow">→</div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartLabels = @json($gradeLabels);
    const chartData = @json($gradeCounts);

    const commonColors = ['#10b981', '#f59e0b', '#ef4444'];

    const barCtx = document.getElementById('gradeBarChart');
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
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.raw}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                family: 'Inter'
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            color: '#64748b',
                            font: {
                                family: 'Inter'
                            }
                        },
                        grid: {
                            color: '#e2e8f0'
                        },
                        border: {
                            display: false
                        }
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
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const value = context.raw;
                                const percent = ((value / total) * 100).toFixed(0);
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