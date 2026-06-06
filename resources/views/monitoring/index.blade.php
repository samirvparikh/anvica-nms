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
                    <th>Hostname</th>
                    @if(Auth::user()->isAdmin())<th>Customer</th>@endif
                    <th>Service</th>
                    <th>Status</th>
                    <th>Last Seen</th>
                    <th>CPU %</th>
                    <th>RAM %</th>
                    <th>Disk %</th>
                    <th>Temp °C</th>
                </tr>
            </thead>
            <tbody>
                @forelse($devices as $device)
                @php $m = $latestMetrics[$device->id] ?? []; @endphp
                <tr>
                    <td style="font-weight:700;">{{ $device->name }}</td>
                    <td>{{ $device->hostname ?? '—' }}</td>
                    @if(Auth::user()->isAdmin())
                    <td>{{ $device->user?->name ?? 'Admin / Unassigned' }}</td>
                    @endif
                    <td>{{ $device->service?->name ?? $device->type }}</td>
                    <td><span class="status-badge {{ strtolower($device->status) }}">{{ $device->status }}</span></td>
                    <td>{{ $device->last_seen?->format('M d, H:i') ?? '—' }}</td>
                    <td>{{ isset($m['cpu']) ? number_format($m['cpu']->metric_value, 1) : '—' }}</td>
                    <td>{{ isset($m['ram']) ? number_format($m['ram']->metric_value, 1) : '—' }}</td>
                    <td>{{ isset($m['disk']) ? number_format($m['disk']->metric_value, 1) : '—' }}</td>
                    <td>{{ isset($m['temperature']) ? number_format($m['temperature']->metric_value, 1) : '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ Auth::user()->isAdmin() ? 10 : 9 }}" style="text-align:center;padding:2rem;color:var(--text-muted);">
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
@endsection
