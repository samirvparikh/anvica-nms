@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Monitoring Data</h1>
        <p>
            @if($isAdmin)
                View polled metrics, interfaces, and alerts for all customers.
            @else
                Your devices — metrics, interfaces, and alerts.
            @endif
        </p>
    </div>
    @if($isAdmin)
    <form method="GET" action="{{ route('monitoring.index') }}" class="report-user-filter">
        <label for="monitoringUserFilter" class="report-user-filter-label">Select User</label>
        <select id="monitoringUserFilter" name="user_id" class="form-control report-user-filter-select" onchange="this.form.submit()">
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

@include('reports.partials.devices-section')

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
                    @if($isAdmin)<th>Customer</th>@endif
                    <th>IP Address</th>
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
                    @if($isAdmin)
                    <td>{{ $iface->device->user?->name ?? 'Admin / Unassigned' }}</td>
                    @endif
                    <td>{{ $iface->device->ip_address ?? '—' }}</td>
                    <td>{{ $iface->interface_name }}</td>
                    <td><span class="status-badge {{ strtolower($iface->status) }}">{{ ucfirst($iface->status) }}</span></td>
                    <td>{{ \App\Support\ByteFormatter::formatBytes($iface->rx) }}</td>
                    <td>{{ \App\Support\ByteFormatter::formatBytes($iface->tx) }}</td>
                    <td title="{{ number_format($iface->rx_packets) }} packets">{{ \App\Support\ByteFormatter::formatPackets($iface->rx_packets) }}</td>
                    <td title="{{ number_format($iface->tx_packets) }} packets">{{ \App\Support\ByteFormatter::formatPackets($iface->tx_packets) }}</td>
                    <td>{{ $iface->updated_at?->format('M d, H:i') ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $isAdmin ? 9 : 8 }}" style="text-align:center;padding:2rem;color:var(--text-muted);">
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
                    @if($isAdmin)<th>Customer</th>@endif
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
                    @if($isAdmin)
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
                    <td colspan="{{ $isAdmin ? 7 : 6 }}" style="text-align:center;padding:2rem;color:var(--text-muted);">
                        No alerts for this scope.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
