@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Preventive Maintenance Schedules</h1>
        <p>List of planned network downtime windows, upgrades, and scheduled preventive maintenance tasks.</p>
    </div>
    <a href="{{ route('maintenance.preventive.create') }}" class="btn-add">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Schedule Maintenance
    </a>
</div>

<div class="card-table-container" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow);">
    <table class="data-table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                <th style="padding: 1rem 0.5rem;">
                    <a href="{{ route('maintenance.preventive.index', ['sort' => 'maintenance_id', 'direction' => ($sort === 'maintenance_id' && $dir === 'asc') ? 'desc' : 'asc']) }}" style="text-decoration: none; color: var(--text-dark); font-weight: 700;">
                        Maintenance ID {!! $sort === 'maintenance_id' ? ($dir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
                <th style="padding: 1rem 0.5rem;">
                    <a href="{{ route('maintenance.preventive.index', ['sort' => 'title', 'direction' => ($sort === 'title' && $dir === 'asc') ? 'desc' : 'asc']) }}" style="text-decoration: none; color: var(--text-dark); font-weight: 700;">
                        Title {!! $sort === 'title' ? ($dir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
                <th style="padding: 1rem 0.5rem;">Device</th>
                <th style="padding: 1rem 0.5rem;">
                    <a href="{{ route('maintenance.preventive.index', ['sort' => 'start_time', 'direction' => ($sort === 'start_time' && $dir === 'asc') ? 'desc' : 'asc']) }}" style="text-decoration: none; color: var(--text-dark); font-weight: 700;">
                        Start Time {!! $sort === 'start_time' ? ($dir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
                <th style="padding: 1rem 0.5rem;">
                    <a href="{{ route('maintenance.preventive.index', ['sort' => 'end_time', 'direction' => ($sort === 'end_time' && $dir === 'asc') ? 'desc' : 'asc']) }}" style="text-decoration: none; color: var(--text-dark); font-weight: 700;">
                        End Time {!! $sort === 'end_time' ? ($dir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
                <th style="padding: 1rem 0.5rem;">Expected Downtime</th>
                <th style="padding: 1rem 0.5rem;">Exclude from SLA</th>
                <th style="padding: 1rem 0.5rem;">
                    <a href="{{ route('maintenance.preventive.index', ['sort' => 'status', 'direction' => ($sort === 'status' && $dir === 'asc') ? 'desc' : 'asc']) }}" style="text-decoration: none; color: var(--text-dark); font-weight: 700;">
                        Status {!! $sort === 'status' ? ($dir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($maintenances as $maintenance)
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 1rem 0.5rem; font-weight: 700; color: var(--primary);">{{ $maintenance->maintenance_id }}</td>
                <td style="padding: 1rem 0.5rem; font-weight: 600;">{{ $maintenance->title }}</td>
                <td style="padding: 1rem 0.5rem;">{{ $maintenance->device->name ?? '—' }}</td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">
                    {{ is_string($maintenance->start_time) ? Carbon\Carbon::parse($maintenance->start_time)->format('d M Y H:i') : $maintenance->start_time->format('d M Y H:i') }}
                </td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">
                    {{ is_string($maintenance->end_time) ? Carbon\Carbon::parse($maintenance->end_time)->format('d M Y H:i') : $maintenance->end_time->format('d M Y H:i') }}
                </td>
                <td style="padding: 1rem 0.5rem;">{{ $maintenance->expected_downtime_minutes }} Minutes</td>
                <td style="padding: 1rem 0.5rem; text-align: center;">
                    <span class="status-badge {{ $maintenance->exclude_sla ? 'active' : 'inactive' }}" style="font-size: 0.75rem;">
                        {{ $maintenance->exclude_sla ? 'Yes' : 'No' }}
                    </span>
                </td>
                <td style="padding: 1rem 0.5rem;">
                    <span class="status-badge {{ $maintenance->status === 'completed' ? 'active' : ($maintenance->status === 'scheduled' ? 'warning' : 'inactive') }}" style="font-size: 0.7rem;">
                        {{ ucfirst($maintenance->status) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
