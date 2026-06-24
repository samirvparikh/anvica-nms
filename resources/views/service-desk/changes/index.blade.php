@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Change Requests (RFC)</h1>
        <p>Manage, schedule, and review Request for Changes (RFC) in the infrastructure.</p>
    </div>
    <a href="{{ route('changes.create') }}" class="btn-add">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        + Raise Change Request
    </a>
</div>

<div class="card-table-container" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow);">
    <table class="data-table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                <th style="padding: 1rem 0.5rem;">
                    <a href="{{ route('changes.index', array_merge(request()->query(), ['sort' => 'ticket_number', 'direction' => ($sort === 'ticket_number' && $dir === 'asc') ? 'desc' : 'asc'])) }}" style="text-decoration: none; color: var(--text-dark); font-weight: 700;">
                        Change ID {!! $sort === 'ticket_number' ? ($dir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
                <th style="padding: 1rem 0.5rem;">
                    <a href="{{ route('changes.index', array_merge(request()->query(), ['sort' => 'title', 'direction' => ($sort === 'title' && $dir === 'asc') ? 'desc' : 'asc'])) }}" style="text-decoration: none; color: var(--text-dark); font-weight: 700;">
                        Change Title {!! $sort === 'title' ? ($dir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
                <th style="padding: 1rem 0.5rem;">
                    <a href="{{ route('changes.index', array_merge(request()->query(), ['sort' => 'priority', 'direction' => ($sort === 'priority' && $dir === 'asc') ? 'desc' : 'asc'])) }}" style="text-decoration: none; color: var(--text-dark); font-weight: 700;">
                        Priority {!! $sort === 'priority' ? ($dir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
                <th style="padding: 1rem 0.5rem;">
                    <a href="{{ route('changes.index', array_merge(request()->query(), ['sort' => 'status', 'direction' => ($sort === 'status' && $dir === 'asc') ? 'desc' : 'asc'])) }}" style="text-decoration: none; color: var(--text-dark); font-weight: 700;">
                        Status {!! $sort === 'status' ? ($dir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
                <th style="padding: 1rem 0.5rem;">Planned Start</th>
                <th style="padding: 1rem 0.5rem;">Planned End</th>
                <th style="padding: 1rem 0.5rem;">Downtime</th>
            </tr>
        </thead>
        <tbody>
            @foreach($changes as $chg)
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 1rem 0.5rem; font-weight: 700; color: var(--primary);">{{ $chg->ticket_number }}</td>
                <td style="padding: 1rem 0.5rem; font-weight: 600;">{{ $chg->title }}</td>
                <td style="padding: 1rem 0.5rem;">
                    <span class="status-badge {{ $chg->priority === 'critical' ? 'down' : ($chg->priority === 'high' ? 'warning' : 'active') }}" style="font-size: 0.7rem;">
                        {{ ucfirst($chg->priority) }}
                    </span>
                </td>
                <td style="padding: 1rem 0.5rem;">
                    <span class="status-badge {{ $chg->status === 'new' ? 'warning' : 'active' }}" style="font-size: 0.7rem;">
                        {{ ucfirst($chg->status) }}
                    </span>
                </td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">{{ $chg->change_planned_start ? $chg->change_planned_start->format('d M Y H:i') : '—' }}</td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">{{ $chg->change_planned_end ? $chg->change_planned_end->format('d M Y H:i') : '—' }}</td>
                <td style="padding: 1rem 0.5rem; text-align: center;">
                    <span class="status-badge {{ $chg->planned_downtime ? 'inactive' : 'active' }}" style="font-size: 0.7rem;">
                        {{ $chg->planned_downtime ? 'Yes' : 'No' }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
