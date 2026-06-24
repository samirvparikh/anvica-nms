@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>SLA Maintenance Windows</h1>
        <p>List of scheduled preventive downtime windows and their exclusion status on service level targets.</p>
    </div>
</div>

<div class="card-table-container" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow);">
    <table class="data-table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                <th style="padding: 1rem 0.5rem;">Maintenance ID</th>
                <th style="padding: 1rem 0.5rem;">Title</th>
                <th style="padding: 1rem 0.5rem;">Primary Device</th>
                <th style="padding: 1rem 0.5rem;">Start Time</th>
                <th style="padding: 1rem 0.5rem;">End Time</th>
                <th style="padding: 1rem 0.5rem;">Exclude from SLA</th>
                <th style="padding: 1rem 0.5rem;">SLA Impact Status</th>
                <th style="padding: 1rem 0.5rem;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($maintenanceWindows as $window)
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 1rem 0.5rem; font-weight: 700; color: var(--primary);">{{ $window->maintenance_id }}</td>
                <td style="padding: 1rem 0.5rem; font-weight: 600;">{{ $window->title }}</td>
                <td style="padding: 1rem 0.5rem;">{{ $window->device->name ?? '—' }}</td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">{{ $window->start_time->format('d M Y H:i') }}</td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">{{ $window->end_time->format('d M Y H:i') }}</td>
                <td style="padding: 1rem 0.5rem; text-align: center;">
                    <span class="status-badge {{ $window->exclude_sla ? 'active' : 'inactive' }}" style="font-size: 0.75rem;">
                        {{ $window->exclude_sla ? 'Yes' : 'No' }}
                    </span>
                </td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">{{ $window->sla_impact ?? 'No Impact' }}</td>
                <td style="padding: 1rem 0.5rem;">
                    <span class="status-badge {{ $window->status === 'completed' ? 'active' : ($window->status === 'scheduled' ? 'warning' : 'inactive') }}" style="font-size: 0.7rem;">
                        {{ ucfirst($window->status) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 1rem 0.5rem; font-weight: 700; color: var(--primary);">PM-2026-00045</td>
                <td style="padding: 1rem 0.5rem; font-weight: 600;">Core Router Firmware Upgrade</td>
                <td style="padding: 1rem 0.5rem;">Core-Router-01</td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">26 Jun 2026 23:00</td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">27 Jun 2026 01:00</td>
                <td style="padding: 1rem 0.5rem; text-align: center;">
                    <span class="status-badge active" style="font-size: 0.75rem;">Yes</span>
                </td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">No Breach (Maintenance)</td>
                <td style="padding: 1rem 0.5rem;">
                    <span class="status-badge warning" style="font-size: 0.7rem;">Scheduled</span>
                </td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 1rem 0.5rem; font-weight: 700; color: var(--primary);">PM-2026-00048</td>
                <td style="padding: 1rem 0.5rem; font-weight: 600;">Firewall Rules Clean-up & Sync</td>
                <td style="padding: 1rem 0.5rem;">Firewall-02</td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">29 Jun 2026 22:00</td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">29 Jun 2026 23:00</td>
                <td style="padding: 1rem 0.5rem; text-align: center;">
                    <span class="status-badge active" style="font-size: 0.75rem;">Yes</span>
                </td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">No Breach (Maintenance)</td>
                <td style="padding: 1rem 0.5rem;">
                    <span class="status-badge warning" style="font-size: 0.7rem;">Scheduled</span>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
