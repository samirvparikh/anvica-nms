@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Monitoring Data</h1>
        <p>
            @if(Auth::user()->isAdmin())
                View polled metrics, interfaces, and alerts for all customers.
            @else
                Your devices — metrics, interfaces, and alerts.
            @endif
        </p>
    </div>
    @if(Auth::user()->isAdmin())
    <form method="GET" action="{{ route('monitoring.index') }}" class="monitoring-user-filter">
        <select name="user_id" class="form-control" onchange="this.form.submit()" style="min-width:200px;padding-left:1rem;">
            <option value="">All Users</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" {{ (int) $customerId === $customer->id ? 'selected' : '' }}>
                    {{ $customer->name }} ({{ $customer->email }})
                </option>
            @endforeach
        </select>
    </form>
    @endif
</div>

<!-- Devices: status, last_seen, hostname -->
<div class="card-table-container" style="margin-bottom:1.5rem;">
    <div class="table-toolbar">
        <h3 style="font-size:1rem;font-weight:700;">Devices</h3>
    </div>
    <div class="table-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>IP Address</th>
                    @if(Auth::user()->isAdmin())<th>Customer</th>@endif
                    <th>Service</th>
                    <th>Health</th>
                    <th>Last Seen</th>
                    <th>CPU %</th>
                    <th>RAM %</th>
                    <th>Disk %</th>
                    <th>Temp °C</th>
                    <th style="text-align:right;">Show</th>
                </tr>
            </thead>
            <tbody>
                @forelse($devices as $device)
                @php
                    $m = $latestMetrics[$device->id] ?? [];
                    $health = $deviceHealth[$device->id] ?? 'Down';
                @endphp
                <tr>
                    <td style="font-weight:700;">{{ $device->name }}</td>
                    <td>{{ $device->ip_address ?? '—' }}</td>
                    @if(Auth::user()->isAdmin())
                    <td>{{ $device->user?->name ?? 'Admin / Unassigned' }}</td>
                    @endif
                    <td>{{ $device->service?->name ?? $device->type }}</td>
                    <td><span class="status-badge {{ strtolower($health) }}">{{ $health }}</span></td>
                    <td>{{ $device->last_seen?->format('M d, H:i') ?? '—' }}</td>
                    <td>{{ isset($m['cpu']) ? number_format($m['cpu']->metric_value, 1) : '—' }}</td>
                    <td>{{ isset($m['ram']) ? number_format($m['ram']->metric_value, 1) : '—' }}</td>
                    <td>{{ isset($m['disk']) ? number_format($m['disk']->metric_value, 1) : '—' }}</td>
                    <td>{{ isset($m['temperature']) ? number_format($m['temperature']->metric_value, 1) : '—' }}</td>
                    <td style="text-align:right;">
                        <button type="button"
                                class="btn-action view-btn showMetricsBtn"
                                title="Show metrics history"
                                data-device-name="{{ $device->name }}"
                                data-url="{{ route('monitoring.device.metrics', $device) }}{{ $customerId ? '?user_id='.$customerId : '' }}">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline-block;vertical-align:middle;">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ Auth::user()->isAdmin() ? 11 : 10 }}" style="text-align:center;padding:2rem;color:var(--text-muted);">
                        No devices found for this account.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- device_interfaces -->
<div class="card-table-container" style="margin-bottom:1.5rem;">
    <div class="table-toolbar">
        <h3 style="font-size:1rem;font-weight:700;">Interfaces (RX/TX, Packets, Status)</h3>
    </div>
    <div class="table-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Device</th>
                    @if(Auth::user()->isAdmin())<th>Customer</th>@endif
                    <th>Interface</th>
                    <th>Status</th>
                    <th>RX</th>
                    <th>TX</th>
                    <th>RX Packets</th>
                    <th>TX Packets</th>
                    <th>Updated</th>
                </tr>
            </thead>
            <tbody>
                @forelse($interfaces as $iface)
                <tr>
                    <td style="font-weight:600;">{{ $iface->device->name }}</td>
                    @if(Auth::user()->isAdmin())
                    <td>{{ $iface->device->user?->name ?? 'Admin / Unassigned' }}</td>
                    @endif
                    <td>{{ $iface->interface_name }}</td>
                    <td><span class="status-badge {{ strtolower($iface->status) }}">{{ ucfirst($iface->status) }}</span></td>
                    <td>{{ number_format($iface->rx) }}</td>
                    <td>{{ number_format($iface->tx) }}</td>
                    <td>{{ number_format($iface->rx_packets) }}</td>
                    <td>{{ number_format($iface->tx_packets) }}</td>
                    <td>{{ $iface->updated_at?->format('M d, H:i') ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ Auth::user()->isAdmin() ? 9 : 8 }}" style="text-align:center;padding:2rem;color:var(--text-muted);">
                        No interface data yet. Run the poller to collect data.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- alerts -->
<div class="card-table-container">
    <div class="table-toolbar">
        <h3 style="font-size:1rem;font-weight:700;">Alerts (Threshold Violations)</h3>
    </div>
    <div class="table-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Device</th>
                    @if(Auth::user()->isAdmin())<th>Customer</th>@endif
                    <th>Severity</th>
                    <th>Message</th>
                    <th>Service Point</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alerts as $alert)
                <tr>
                    <td style="font-weight:600;">{{ $alert->device->name }}</td>
                    @if(Auth::user()->isAdmin())
                    <td>{{ $alert->device->user?->name ?? 'Admin / Unassigned' }}</td>
                    @endif
                    <td><span class="status-badge {{ $alert->severity }}">{{ ucfirst($alert->severity) }}</span></td>
                    <td>{{ $alert->message }}</td>
                    <td>{{ $alert->servicePoint?->name ?? '—' }}</td>
                    <td><span class="status-badge {{ $alert->status === 'open' ? 'warning' : 'up' }}">{{ ucfirst($alert->status) }}</span></td>
                    <td>{{ $alert->created_at->format('M d, H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ Auth::user()->isAdmin() ? 7 : 6 }}" style="text-align:center;padding:2rem;color:var(--text-muted);">
                        No alerts for this scope.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Device Metrics History Modal -->
<div class="modal-overlay" id="metricsHistoryModal">
    <div class="modal-card modal-card-wide metrics-history-modal">
        <div class="modal-header">
            <h3 id="metricsHistoryTitle">Device Metrics History</h3>
            <button type="button" class="modal-close" id="closeMetricsHistoryModal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="table-scroll">
                <table class="data-table" id="metricsHistoryTable">
                    <thead>
                        <tr>
                            <th>Recorded At</th>
                            <th>Metric</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody id="metricsHistoryBody">
                        <tr>
                            <td colspan="3" style="text-align:center;padding:2rem;color:var(--text-muted);">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" id="cancelMetricsHistoryModal">Close</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('metricsHistoryModal');
    const title = document.getElementById('metricsHistoryTitle');
    const tbody = document.getElementById('metricsHistoryBody');
    const closeBtn = document.getElementById('closeMetricsHistoryModal');
    const cancelBtn = document.getElementById('cancelMetricsHistoryModal');

    function openModal() {
        modal.classList.add('open');
    }

    function closeModal() {
        modal.classList.remove('open');
    }

    function renderMetrics(metrics) {
        if (!metrics.length) {
            tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:2rem;color:var(--text-muted);">No metrics recorded yet.</td></tr>';
            return;
        }

        tbody.innerHTML = metrics.map(function (metric) {
            return '<tr>'
                + '<td style="white-space:nowrap;color:var(--text-muted);">' + metric.recorded_at + '</td>'
                + '<td style="font-weight:600;">' + metric.metric_slug + '</td>'
                + '<td class="cell-mono">' + metric.metric_value + '</td>'
                + '</tr>';
        }).join('');
    }

    document.querySelectorAll('.showMetricsBtn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const url = this.getAttribute('data-url');
            const deviceName = this.getAttribute('data-device-name');

            title.textContent = deviceName + ' — Metrics History';
            tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:2rem;color:var(--text-muted);">Loading...</td></tr>';
            openModal();

            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Failed to load metrics');
                    }
                    return response.json();
                })
                .then(function (data) {
                    renderMetrics(data.metrics || []);
                })
                .catch(function () {
                    tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:2rem;color:var(--status-down);">Unable to load metrics history.</td></tr>';
                });
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
