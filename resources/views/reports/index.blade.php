@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Report Dashboard</h1>
        <p>
            @if($isAdmin)
                @if($selectedCustomer)
                    Reports for <strong>{{ $selectedCustomer->name }}</strong> — devices and interfaces.
                @else
                    Device and interface reports for all customers.
                @endif
            @else
                Reports for your assigned devices and interfaces, {{ Auth::user()->name }}.
            @endif
        </p>
    </div>
    @if($isAdmin)
    <form method="GET" action="{{ route('reports.index') }}" class="report-user-filter">
        <label for="reportUserFilter" class="report-user-filter-label">Select User</label>
        <select id="reportUserFilter" name="user_id" class="form-control report-user-filter-select" onchange="this.form.submit()">
            <option value="">All Users</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" {{ (int) $customerId === (int) $customer->id ? 'selected' : '' }}>
                    {{ $customer->name }} ({{ $customer->email }})
                </option>
            @endforeach
        </select>
    </form>    
    @endif
</div>

@if($devices->isEmpty())
<div class="card-table-container">
    <p style="text-align:center;padding:2rem;color:var(--text-muted);margin:0;">
        @if($isAdmin)
            @if($selectedCustomer)
                No devices found for {{ $selectedCustomer->name }}.
            @else
                No devices found in the system.
            @endif
        @else
            No devices are assigned to your account yet.
        @endif
    </p>
</div>
@else
<div class="report-cards-grid">
    @foreach($devices as $device)
    @php
        $m = $latestMetrics[$device->id] ?? [];
        $health = $deviceHealth[$device->id] ?? 'Down';
        $healthClass = strtolower($health);
        $cpuMetric = $m['CPU'] ?? $m['cpu'] ?? null;
        $cpuPct = $cpuMetric ? (float) $cpuMetric->metric_value : null;
        $ramUsed = (float) (($m['Ram_Used'] ?? $m['ram_uses'] ?? null)?->metric_value ?? 0);
        $ramTotal = (float) (($m['Total_Ram'] ?? $m['total_ram'] ?? null)?->metric_value ?? 0);
        $ramPct = isset($m['ram']) ? (float) $m['ram']->metric_value : ($ramTotal > 0 ? round(($ramUsed / $ramTotal) * 100, 1) : null);
        $diskMetric = $m['disk'] ?? null;
        $diskPct = $diskMetric ? (float) $diskMetric->metric_value : null;
        $tempMetric = $m['CPU_Temp'] ?? $m['MB_Temp'] ?? $m['Board_Temp'] ?? $m['temperature'] ?? null;
        $tempVal = $tempMetric ? (float) $tempMetric->metric_value : null;
        $logUrl = route('reports.device.show', $device) . ($customerId ? '?user_id=' . $customerId : '');
        $deviceInterfaces = $scopedInterfaces->get($device->id, collect());

        $lastSeen = $device->last_seen;
        $lastSeenClass = 'never';
        $lastSeenRelative = 'No activity yet';
        $lastSeenFull = 'Device has not reported in';

        if ($lastSeen) {
            $lastSeenFull = $lastSeen->format('M d, Y') . ' at ' . $lastSeen->format('h:i A');
            $lastSeenRelative = $lastSeen->diffForHumans(null, true) . ' ago';
            $minutesAgo = $lastSeen->diffInMinutes(now());

            if ($minutesAgo <= 5) {
                $lastSeenClass = 'fresh';
                $lastSeenRelative = $minutesAgo < 1 ? 'Just now' : $lastSeenRelative;
            } elseif ($minutesAgo <= 60) {
                $lastSeenClass = 'recent';
            } else {
                $lastSeenClass = 'stale';
            }
        }

        $metricLevel = function (?float $value, float $warn = 70, float $crit = 90): string {
            if ($value === null) {
                return 'neutral';
            }
            if ($value >= $crit) {
                return 'critical';
            }
            if ($value >= $warn) {
                return 'warning';
            }

            return 'healthy';
        };
    @endphp
    <article class="report-device-card report-device-card--{{ $healthClass }}">
        <div class="report-card-accent"></div>

        <div class="report-card-top">
            <div class="report-card-identity">
                <div class="report-card-icon">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="2" y="2" width="20" height="8" rx="2" ry="2"/>
                        <rect x="2" y="14" width="20" height="8" rx="2" ry="2"/>
                        <line x1="6" y1="6" x2="6.01" y2="6"/>
                        <line x1="6" y1="18" x2="6.01" y2="18"/>
                    </svg>
                </div>
                <div class="report-card-meta">
                    <div class="report-card-name-row">
                        <h3>{{ $device->name }}</h3>
                        <span class="status-badge {{ $healthClass }}">{{ $health }}</span>
                    </div>
                    <p class="report-card-subtitle">
                        {{ $device->service?->name ?? $device->type ?? 'Unknown service' }}
                        @if($device->vendor?->name)
                            <span class="report-card-dot">•</span> {{ $device->vendor->name }}
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="report-metrics-row">
            <div class="report-metric-tile report-metric-tile--{{ $metricLevel($cpuPct) }}">
                <span class="report-metric-label">CPU</span>
                <span class="report-metric-value">{{ $cpuPct !== null ? number_format($cpuPct, 1) . '%' : '—' }}</span>
                <span class="report-metric-bar"><span style="width:{{ min(100, $cpuPct ?? 0) }}%"></span></span>
            </div>
            <div class="report-metric-tile report-metric-tile--{{ $metricLevel($ramPct) }}">
                <span class="report-metric-label">RAM</span>
                <span class="report-metric-value">{{ $ramPct !== null ? number_format($ramPct, 1) . '%' : '—' }}</span>
                <span class="report-metric-bar"><span style="width:{{ min(100, $ramPct ?? 0) }}%"></span></span>
            </div>
            <div class="report-metric-tile report-metric-tile--{{ $metricLevel($diskPct) }}">
                <span class="report-metric-label">Disk</span>
                <span class="report-metric-value">{{ $diskPct !== null ? number_format($diskPct, 1) . '%' : '—' }}</span>
                <span class="report-metric-bar"><span style="width:{{ min(100, $diskPct ?? 0) }}%"></span></span>
            </div>
            <div class="report-metric-tile report-metric-tile--{{ $metricLevel($tempVal, 70, 85) }}">
                <span class="report-metric-label">Temp</span>
                <span class="report-metric-value">{{ $tempVal !== null ? number_format($tempVal, 1) . '°C' : '—' }}</span>
                <span class="report-metric-bar"><span style="width:{{ min(100, $tempVal ?? 0) }}%"></span></span>
            </div>
        </div>

        <div class="report-info-panel">
            <div class="report-detail-item">
                <span class="report-detail-label">IP Address</span>
                <span class="report-detail-value cell-mono">{{ $device->ip_address ?? '—' }}</span>
            </div>
            <div class="report-detail-item">
                <span class="report-detail-label">Hostname</span>
                <span class="report-detail-value">{{ $device->hostname ?? '—' }}</span>
            </div>
            @if($isAdmin)
            <div class="report-detail-item">
                <span class="report-detail-label">Customer</span>
                <span class="report-detail-value">{{ $device->user?->name ?? 'Admin / Unassigned' }}</span>
            </div>
            @endif
            <div class="report-detail-item">
                <span class="report-detail-label">Location</span>
                <span class="report-detail-value">{{ $device->location ?? '—' }}</span>
            </div>
        </div>

        <div class="report-interfaces-section">
            <div class="report-section-head">
                <h4>Interfaces</h4>
                <span class="report-count-badge">{{ $deviceInterfaces->count() }}</span>
            </div>

            @if($deviceInterfaces->isEmpty())
                <div class="report-empty-state">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M4 7h16M4 12h16M4 17h10"/>
                    </svg>
                    <p>No interface data recorded yet.</p>
                </div>
            @else
            <div class="report-iface-list">
                @foreach($deviceInterfaces as $iface)
                <div class="report-iface-item">
                    <div class="report-iface-main">
                        <span class="report-iface-name">{{ $iface->interface_name }}</span>
                        <span class="status-badge {{ strtolower($iface->status) }}">{{ ucfirst($iface->status) }}</span>
                    </div>
                    <div class="report-iface-stats">
                        <span><strong>RX</strong> {{ number_format($iface->rx) }}</span>
                        <span><strong>TX</strong> {{ number_format($iface->tx) }}</span>
                        <span><strong>Pkts</strong> {{ number_format($iface->rx_packets + $iface->tx_packets) }}</span>
                        <span class="report-iface-time">{{ $iface->updated_at?->format('M d, H:i') ?? '—' }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <div class="report-card-footer">
            <div class="report-last-seen report-last-seen--{{ $lastSeenClass }}" title="{{ $lastSeenFull }}">
                <div class="report-last-seen-icon" aria-hidden="true">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <div class="report-last-seen-content">
                    <span class="report-last-seen-label">Last Seen</span>
                    <span class="report-last-seen-value">{{ $lastSeenRelative }}</span>
                    @if($lastSeen)
                    <span class="report-last-seen-date">{{ $lastSeenFull }}</span>
                    @endif
                </div>
                @if($lastSeenClass === 'fresh')
                <span class="report-last-seen-live" aria-label="Online">Live</span>
                @endif
            </div>
            <div>
            <a href="{{ $logUrl }}" class="btn-primary report-view-link">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                View Report
            </a>
            </div>
        </div>
    </article>
    @endforeach
</div>
@endif
@endsection
