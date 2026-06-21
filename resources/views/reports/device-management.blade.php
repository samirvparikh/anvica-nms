@extends('layouts.app')

@section('content')
@php
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

<div class="page-header">
    <div class="page-title">
        <a href="{{ route('reports.index') }}" class="report-back-link">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Back to Reports Dashboard
        </a>
        <h1>Device Management Report</h1>
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
    <form method="GET" action="{{ route('reports.device-management') }}" class="report-user-filter">
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

<div class="card-table-container">
    <div class="table-toolbar">
        <h3 style="font-size:1rem;font-weight:700;margin:0;">Devices</h3>
    </div>

    <div class="table-scroll">
        <table class="data-table" id="reportsDevicesTable">
            <thead>
                <tr>
                    <th>Device</th>
                    <th>IP Address</th>
                    @if($isAdmin)<th>Customer</th>@endif
                    <th>Service</th>
                    <th>Health</th>
                    <th>Last Seen</th>
                    <th>CPU %</th>
                    <th>RAM %</th>
                    <th>Interfaces</th>
                    <th class="col-actions" data-no-sort="true" style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($devices as $device)
                @php
                    $m = $latestMetrics[$device->id] ?? [];
                    $health = $deviceHealth[$device->id] ?? 'Down';
                    $healthClass = strtolower($health);
                    $cpuMetric = $m['CPU'] ?? $m['cpu'] ?? null;
                    $cpuPct = $cpuMetric ? (float) $cpuMetric->metric_value : null;
                    $ramUsed = (float) (($m['Ram_Used'] ?? $m['ram_uses'] ?? null)?->metric_value ?? 0);
                    $ramTotal = (float) (($m['Total_Ram'] ?? $m['total_ram'] ?? null)?->metric_value ?? 0);
                    $ramPct = isset($m['ram']) ? (float) $m['ram']->metric_value : ($ramTotal > 0 ? round(($ramUsed / $ramTotal) * 100, 1) : null);
                    $deviceInterfaces = $scopedInterfaces->get($device->id, collect());
                    $diskMetric = $m['disk'] ?? null;
                    $diskPct = $diskMetric ? (float) $diskMetric->metric_value : null;
                    $tempMetric = $m['CPU_Temp'] ?? $m['MB_Temp'] ?? $m['Board_Temp'] ?? $m['temperature'] ?? null;
                    $tempVal = $tempMetric ? (float) $tempMetric->metric_value : null;
                    $reportUrl = route('reports.device.show', $device) . ($customerId ? '?user_id=' . $customerId : '');

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
                @endphp
                <tr>
                    <td style="font-weight:700;">{{ $device->name }}</td>
                    <td class="cell-mono">{{ $device->ip_address ?? '—' }}</td>
                    @if($isAdmin)
                    <td>{{ $device->user?->name ?? 'Admin / Unassigned' }}</td>
                    @endif
                    <td>{{ $device->service?->name ?? $device->type ?? '—' }}</td>
                    <td><span class="status-badge {{ $healthClass }}">{{ $health }}</span></td>
                    <td style="color:var(--text-muted);white-space:nowrap;">{{ $device->last_seen?->format('M d, Y H:i') ?? '—' }}</td>
                    <td>{{ $cpuPct !== null ? number_format($cpuPct, 1) : '—' }}</td>
                    <td>{{ $ramPct !== null ? number_format($ramPct, 1) : '—' }}</td>
                    <td>{{ $deviceInterfaces->count() }}</td>
                    <td class="col-actions">
                        <div class="table-actions">
                            <button type="button"
                                    class="btn-action view-btn preview-report-btn"
                                    title="Preview device report"
                                    data-device-name="{{ $device->name }}"
                                    data-card-target="report-card-{{ $device->id }}">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                            <a href="{{ $reportUrl }}"
                               class="btn-action edit-btn report-open-btn"
                               title="Open full report">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <line x1="16" y1="13" x2="8" y2="13"/>
                                    <line x1="16" y1="17" x2="8" y2="17"/>
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr class="no-sort-row">
                    <td colspan="{{ $isAdmin ? 10 : 9 }}" style="text-align:center;padding:2rem;color:var(--text-muted);">
                        @if($isAdmin)
                            @if($selectedCustomer ?? null)
                                No devices found for {{ $selectedCustomer->name }}.
                            @else
                                No devices found in the system.
                            @endif
                        @else
                            No devices are assigned to your account yet.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

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
@endphp
<div id="report-card-{{ $device->id }}" class="report-card-modal-source" hidden>
    @include('reports.partials.device-card')
</div>
@endforeach

<div class="modal-overlay" id="reportPreviewModal">
    <div class="modal-card modal-card-wide report-preview-modal">
        <div class="modal-header">
            <h3 id="reportPreviewTitle">Device Report Preview</h3>
            <button type="button" class="modal-close" id="closeReportPreviewModal">&times;</button>
        </div>
        <div class="modal-body" id="reportPreviewBody"></div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" id="cancelReportPreviewModal">Close</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('reportPreviewModal');
    const title = document.getElementById('reportPreviewTitle');
    const body = document.getElementById('reportPreviewBody');
    const closeBtn = document.getElementById('closeReportPreviewModal');
    const cancelBtn = document.getElementById('cancelReportPreviewModal');

    function openModal() {
        modal.classList.add('open');
    }

    function closeModal() {
        modal.classList.remove('open');
        body.innerHTML = '';
    }

    document.querySelectorAll('.preview-report-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-card-target');
            const deviceName = this.getAttribute('data-device-name');
            const source = document.getElementById(targetId);

            if (!source) {
                return;
            }

            title.textContent = deviceName + ' — Device Preview';
            body.innerHTML = source.innerHTML;
            openModal();
        });
    });

    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            closeModal();
        }
    });
});
</script>
@endsection
