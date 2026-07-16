@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Alerts</h1>
        <p>Monitoring alerts generated automatically from device metrics and health checks.</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif

<div class="alarm-summary-cards">
    <div class="alarm-summary-card critical">
        <h4>Critical</h4>
        <div class="value">{{ $criticalCount }}</div>
    </div>
    <div class="alarm-summary-card warning">
        <h4>Warning</h4>
        <div class="value">{{ $warningCount }}</div>
    </div>
    <div class="alarm-summary-card acknowledged">
        <h4>Acknowledged</h4>
        <div class="value">{{ $ackCount }}</div>
    </div>
</div>

<div class="card-table-container">
    <div class="table-toolbar">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin: 0;">Monitoring Alerts</h3>
        <div class="table-search">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="alertSearchInput" placeholder="Search alerts...">
        </div>
    </div>

    <table class="data-table" id="alertsTable">
        <thead>
            <tr>
                <th style="width: 80px;">Severity</th>
                <th>Type</th>
                <th>Device</th>
                <th>Description</th>
                <th>Timestamp</th>
                <th class="col-actions" style="text-align: right; width: 150px;">Status / Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($alerts as $alert)
            @php
                $deviceName = $alert->device?->name ?? 'Unknown';
                $isOpen = $alert->status === \App\Models\Alert::STATUS_OPEN;
                $isAcknowledged = $alert->acknowledged_at !== null;
            @endphp
            <tr class="alert-row"
                data-device="{{ strtolower($deviceName) }}"
                data-msg="{{ strtolower($alert->message) }}"
                data-sev="{{ strtolower($alert->severity) }}"
                data-status="{{ strtolower($alert->status) }}">
                <td>
                    <span class="status-badge {{ $alert->severity === 'critical' ? 'down' : 'warning' }}" style="padding: 0.2rem 0.5rem; border-radius: 4px;">
                        {{ ucfirst($alert->severity) }}
                    </span>
                </td>
                <td style="font-weight: 600;">{{ $alert->alarm_type ?? 'Alert' }}</td>
                <td style="font-weight: 700;">{{ $deviceName }}</td>
                <td style="color: var(--text-muted);">{{ $alert->message }}</td>
                <td>{{ ($alert->started_at ?? $alert->created_at)->format('M d, Y h:i A') }}</td>
                <td style="text-align: right;">
                    @if($isOpen && ! $isAcknowledged)
                        <form action="{{ route('alerts.ack', $alert) }}" method="POST" style="display: inline-block;">
                            @csrf
                            <button type="submit" class="btn-action ack-btn">Acknowledge</button>
                        </form>
                    @elseif($isOpen && $isAcknowledged)
                        <div style="display: inline-flex; align-items: center; gap: 0.5rem; justify-content: flex-end;">
                            <span class="status-badge up">Acknowledged</span>
                            <form action="{{ route('alerts.close', $alert) }}" method="POST" style="display: inline-block;">
                                @csrf
                                <button type="submit" class="btn-action edit-btn" title="Close / Resolve">Resolve</button>
                            </form>
                        </div>
                    @else
                        <span class="status-badge up">Closed</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem 0;">No monitoring alerts yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById('alertSearchInput');
        const tableRows = document.querySelectorAll('.alert-row');

        if (!searchInput) return;

        searchInput.addEventListener('keyup', function(e) {
            const query = e.target.value.toLowerCase().trim();
            tableRows.forEach(row => {
                const device = row.getAttribute('data-device');
                const message = row.getAttribute('data-msg');
                const severity = row.getAttribute('data-sev');
                const status = row.getAttribute('data-status');
                row.style.display = (device.includes(query) || message.includes(query) || severity.includes(query) || status.includes(query)) ? '' : 'none';
            });
        });
    });
</script>
@endsection
