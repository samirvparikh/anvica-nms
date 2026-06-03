@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Alarms</h1>
        <p>Active alerts across your infrastructure.</p>
    </div>
</div>

<!-- Alarm Summary Cards -->
<div class="alarm-summary-cards">
    <!-- Critical Alarms Card -->
    <div class="alarm-summary-card critical">
        <h4>Critical</h4>
        <div class="value">{{ $criticalCount }}</div>
    </div>
    
    <!-- Warning Alarms Card -->
    <div class="alarm-summary-card warning">
        <h4>Warning</h4>
        <div class="value">{{ $warningCount }}</div>
    </div>
    
    <!-- Acknowledged Card -->
    <div class="alarm-summary-card acknowledged">
        <h4>Acknowledged</h4>
        <div class="value">{{ $ackCount }}</div>
    </div>
</div>

<!-- Alarms Table Card -->
<div class="card-table-container">
    <div class="table-toolbar">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin: 0;">Active & Acknowledged Alerts</h3>
        <div class="table-search">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="alarmSearchInput" placeholder="Search alarms...">
        </div>
    </div>

    <table class="data-table" id="alarmsTable">
        <thead>
            <tr>
                <th style="width: 80px;">Severity</th>
                <th>Device</th>
                <th>Description</th>
                <th>Timestamp</th>
                <th style="text-align: right; width: 150px;">Status / Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($alarms as $alarm)
            <tr class="alarm-row" data-device="{{ strtolower($alarm->device_name) }}" data-msg="{{ strtolower($alarm->message) }}" data-sev="{{ strtolower($alarm->severity) }}" data-status="{{ strtolower($alarm->status) }}">
                <td>
                    <span class="status-badge {{ $alarm->severity == 'Critical' ? 'down' : 'warning' }}" style="padding: 0.2rem 0.5rem; border-radius: 4px;">
                        {{ $alarm->severity }}
                    </span>
                </td>
                <td style="font-weight: 700;">{{ $alarm->device_name }}</td>
                <td style="color: var(--text-muted);">{{ $alarm->message }}</td>
                <td>{{ $alarm->created_at->format('M d, Y h:i A') }}</td>
                <td style="text-align: right;">
                    @if($alarm->status == 'Open')
                        <form action="{{ route('alarms.ack', $alarm->id) }}" method="POST" style="display: inline-block;">
                            @csrf
                            <button type="submit" class="btn-action ack-btn">Acknowledge</button>
                        </form>
                    @else
                        <span class="status-badge up">
                            Acknowledged
                        </span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem 0;">No alarms recorded.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Table search filtering logic for alarms
        const searchInput = document.getElementById('alarmSearchInput');
        const tableRows = document.querySelectorAll('.alarm-row');

        searchInput.addEventListener('keyup', function(e) {
            const query = e.target.value.toLowerCase().trim();
            
            tableRows.forEach(row => {
                const device = row.getAttribute('data-device');
                const message = row.getAttribute('data-msg');
                const severity = row.getAttribute('data-sev');
                const status = row.getAttribute('data-status');

                if (device.includes(query) || message.includes(query) || severity.includes(query) || status.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
</script>
@endsection
