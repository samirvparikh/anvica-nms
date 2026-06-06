@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Dashboard</h1>
        <p>Real-time overview of your network infrastructure.</p>
    </div>
</div>

<!-- Summary Cards -->
<div class="summary-grid">
    <!-- Total Devices -->
    <div class="summary-card">
        <div class="card-icon devices">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="2" y="2" width="20" height="8" rx="2" ry="2"/>
                <rect x="2" y="14" width="20" height="8" rx="2" ry="2"/>
                <line x1="6" y1="6" x2="6.01" y2="6"/>
                <line x1="6" y1="18" x2="6.01" y2="18"/>
            </svg>
        </div>
        <div class="card-info">
            <div class="label">Total Devices</div>
            <div class="value">{{ $totalDevices }}</div>
            <div class="subtext">Online <span class="bold">{{ $upDevices }}</span> - Offline <span class="bold">{{ $downDevices + $warningDevices }}</span></div>
        </div>
    </div>

    <!-- Up Devices -->
    <div class="summary-card">
        <div class="card-icon up">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="16 12 12 8 8 12"/>
                <line x1="12" y1="16" x2="12" y2="8"/>
            </svg>
        </div>
        <div class="card-info">
            <div class="label">Up Devices</div>
            <div class="value">{{ $upDevices }}</div>
            <div class="subtext"><span class="bold" style="color: var(--status-up)">{{ $totalDevices > 0 ? round(($upDevices / $totalDevices) * 100, 2) : 0 }}%</span> of total</div>
        </div>
    </div>

    <!-- Down Devices -->
    <div class="summary-card">
        <div class="card-icon down">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="8 12 12 16 16 12"/>
                <line x1="12" y1="8" x2="12" y2="16"/>
            </svg>
        </div>
        <div class="card-info">
            <div class="label">Down Devices</div>
            <div class="value">{{ $downDevices }}</div>
            <div class="subtext"><span class="bold" style="color: var(--status-down)">{{ $totalDevices > 0 ? round(($downDevices / $totalDevices) * 100, 2) : 0 }}%</span> of total</div>
        </div>
    </div>

    <!-- Alarms -->
    <div class="summary-card">
        <div class="card-icon alarms">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
        </div>
        <div class="card-info">
            <div class="label">Active Alerts</div>
            <div class="value">{{ $totalAlarms }}</div>
            <div class="subtext">Critical <span class="bold">{{ $criticalAlarms }}</span> - Warning <span class="bold">{{ $warningAlarms }}</span></div>
        </div>
    </div>
</div>

<!-- Charts Grid -->
<div class="charts-grid">
    <!-- Bandwidth Utilization Chart -->
    <div class="chart-card">
        <div class="chart-header">
            <div class="chart-title">
                <h3>Bandwidth Utilization</h3>
            </div>
        </div>
        <div class="chart-body bandwidth">
            <canvas id="bandwidthChart"></canvas>
        </div>
    </div>

    <!-- Device Status Doughnut Chart -->
    <div class="chart-card">
        <div class="chart-header">
            <div class="chart-title">
                <h3>Device Status</h3>
            </div>
        </div>
        <div class="chart-body donut-container">
            <canvas id="deviceStatusChart"></canvas>
        </div>
    </div>
</div>

<!-- Bottom Row: Top Interfaces & Recent Alerts -->
<div class="dashboard-bottom-grid">
    <!-- Top Interfaces -->
    <div class="dashboard-list-card">
        <div class="list-header">
            <h3>Top Interfaces by Utilization</h3>
        </div>
        <div class="interfaces-list">
            @foreach($topInterfaces as $interface)
            <div class="interface-row">
                <div class="interface-info">
                    <span class="name">{{ $interface['name'] }}</span>
                    <span class="percent">{{ $interface['utilization'] }}%</span>
                </div>
                <div class="progress-bar-bg">
                    <div class="progress-bar-fill" style="width: {{ $interface['utilization'] }}%;"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Recent Alerts -->
    <div class="dashboard-list-card">
        <div class="list-header">
            <h3>Recent Alerts</h3>
        </div>
        <div class="alerts-list">
            @forelse($recentAlerts as $alert)
            <div class="alert-row-item">
                <div class="alert-details">
                    <div class="alert-pulse-dot {{ strtolower($alert->severity) }}"></div>
                    <div class="alert-meta-text">
                        <span class="device-name">{{ optional($alert->device)->name ?? $alert->device_name ?? 'Unknown' }}</span>
                        <span class="message">{{ $alert->message }}</span>
                    </div>
                </div>
                @php
                    $isClosed = in_array($alert->status, ['Acknowledged', 'closed'], true);
                    $severity = strtolower($alert->severity ?? 'warning');
                @endphp
                <span class="alert-time-badge {{ $isClosed ? 'ack' : $severity }}">
                    @if($isClosed)
                        CLOSED
                    @else
                        {{ $alert->created_at->format('h:i A') }}
                    @endif
                </span>
            </div>
            @empty
            <p style="text-align: center; color: var(--text-muted); font-size: 0.9rem; padding: 2rem 0;">No recent alerts.</p>
            @endforelse
        </div>
    </div>
</div>

@if($healthScores->isNotEmpty())
<div class="dashboard-list-card" style="margin-top: 1.5rem;">
    <div class="list-header"><h3>Device Health Score</h3></div>
    <div class="interfaces-list">
        @foreach($healthScores as $health)
        <div class="interface-row">
            <div class="interface-info">
                <span class="name">{{ $health['name'] }}</span>
                <span class="percent">{{ $health['score'] }}%</span>
            </div>
            <div class="progress-bar-bg">
                <div class="progress-bar-fill" style="width: {{ $health['score'] }}%;"></div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Initialize Charts -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Bandwidth Chart Setup
        const ctxBandwidth = document.getElementById('bandwidthChart').getContext('2d');
        
        // Setup gradients
        const gradientIn = ctxBandwidth.createLinearGradient(0, 0, 0, 250);
        gradientIn.addColorStop(0, 'rgba(116, 198, 43, 0.3)');
        gradientIn.addColorStop(1, 'rgba(116, 198, 43, 0.0)');

        const gradientOut = ctxBandwidth.createLinearGradient(0, 0, 0, 250);
        gradientOut.addColorStop(0, 'rgba(148, 163, 184, 0.15)');
        gradientOut.addColorStop(1, 'rgba(148, 163, 184, 0.0)');

        const bandwidthChart = new Chart(ctxBandwidth, {
            type: 'line',
            data: {
                labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', '24:00'],
                datasets: [
                    {
                        label: 'in',
                        data: [35, 45, 78, 98, 92, 60, 48],
                        borderColor: '#74C62B',
                        borderWidth: 2,
                        backgroundColor: gradientIn,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#74C62B',
                        pointHoverRadius: 6
                    },
                    {
                        label: 'out',
                        data: [20, 28, 60, 72, 68, 40, 30],
                        borderColor: '#94a3b8',
                        borderWidth: 2,
                        backgroundColor: gradientOut,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#94a3b8',
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        padding: 10,
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#cbd5e1',
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ' : ' + context.raw + ' Mbps';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        ticks: {
                            stepSize: 25,
                            color: '#64748b',
                            font: {
                                family: 'Inter',
                                size: 10
                            }
                        },
                        grid: {
                            color: 'rgba(226, 232, 240, 0.5)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#64748b',
                            font: {
                                family: 'Inter',
                                size: 10
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Device Status Donut Chart Setup
        const ctxStatus = document.getElementById('deviceStatusChart').getContext('2d');
        const deviceStatusChart = new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Up', 'Down', 'Warning', 'Unknown'],
                datasets: [{
                    data: [{{ $upDevices }}, {{ $downDevices }}, {{ $warningDevices }}, 0],
                    backgroundColor: ['#22c55e', '#ef4444', '#f97316', '#cbd5e1'],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 15,
                            color: '#1e293b',
                            font: {
                                family: 'Inter',
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        padding: 10,
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.label + ': ' + context.raw + ' devices';
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
