@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>All Maintenance Windows</h1>
        <p>List of active, scheduled, and past maintenance windows configured on assets.</p>
    </div>
    <a href="{{ route('maintenance.preventive.create') }}" class="btn-add">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        + Schedule Maintenance
    </a>
</div>

<div class="card-table-container" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow);">
    <table class="data-table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                <th style="padding: 1rem 0.5rem;">Window ID</th>
                <th style="padding: 1rem 0.5rem;">Title</th>
                <th style="padding: 1rem 0.5rem;">Device</th>
                <th style="padding: 1rem 0.5rem;">Start Time</th>
                <th style="padding: 1rem 0.5rem;">End Time</th>
                <th style="padding: 1rem 0.5rem;">Expected Downtime</th>
                <th style="padding: 1rem 0.5rem;">Approval Status</th>
                <th style="padding: 1rem 0.5rem;">SLA Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($windows as $win)
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 1rem 0.5rem; font-weight: 700; color: var(--primary);">{{ $win->maintenance_id }}</td>
                <td style="padding: 1rem 0.5rem; font-weight: 600;">{{ $win->title }}</td>
                <td style="padding: 1rem 0.5rem;">{{ $win->device->name ?? '—' }}</td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">
                    {{ is_string($win->start_time) ? Carbon\Carbon::parse($win->start_time)->format('d M Y H:i') : $win->start_time->format('d M Y H:i') }}
                </td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">
                    {{ is_string($win->end_time) ? Carbon\Carbon::parse($win->end_time)->format('d M Y H:i') : $win->end_time->format('d M Y H:i') }}
                </td>
                <td style="padding: 1rem 0.5rem;">{{ $win->expected_downtime_minutes }}m</td>
                <td style="padding: 1rem 0.5rem;">
                    <span class="status-badge {{ $win->customer_approval === 'Approved' ? 'active' : 'warning' }}" style="font-size: 0.7rem;">
                        {{ $win->customer_approval }}
                    </span>
                </td>
                <td style="padding: 1rem 0.5rem;">
                    <span class="status-badge {{ $win->exclude_sla ? 'active' : 'inactive' }}" style="font-size: 0.75rem;">
                        {{ $win->exclude_sla ? 'SLA Excluded' : 'SLA Impacted' }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
