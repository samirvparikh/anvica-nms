@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Incidents List</h1>
        <p>Active and past service interruptions and device fault incidents.</p>
    </div>
    <a href="{{ route('incidents.create') }}" class="btn-add">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        + Create Incident
    </a>
</div>

<div class="card-table-container" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow);">
    <!-- Filtering toolbar -->
    <div class="table-toolbar" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
        <form action="{{ route('incidents.index') }}" method="GET" style="display: flex; gap: 0.75rem; align-items: center; width: 100%;">
            <div style="flex-grow: 1; position: relative;">
                <input type="text" name="search" placeholder="Search incidents..." value="{{ request('search') }}" class="form-control" style="padding-left: 2.5rem; height: 38px;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
            </div>
            
            <div>
                <select name="status" class="form-control" style="height: 38px; min-width: 120px;" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>New</option>
                    <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>

            <div>
                <select name="priority" class="form-control" style="height: 38px; min-width: 120px;" onchange="this.form.submit()">
                    <option value="">All Priorities</option>
                    <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                </select>
            </div>

            <button type="submit" class="btn-add" style="height: 38px; background-color: var(--sidebar-active); border: 1px solid var(--border-color); color: white;">Filter</button>
            @if(request()->anyFilled(['search', 'status', 'priority']))
                <a href="{{ route('incidents.index') }}" class="btn-secondary" style="height: 38px; display: inline-flex; align-items: center; padding: 0 1rem; border-radius: 6px; text-decoration: none; border: 1px solid var(--border-color); color: var(--text-muted); font-size: 0.85rem;">Clear</a>
            @endif
        </form>
    </div>

    <!-- Incident List Table -->
    <table class="data-table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                <th style="padding: 1rem 0.5rem;">
                    <a href="{{ route('incidents.index', array_merge(request()->query(), ['sort' => 'ticket_number', 'direction' => ($sort === 'ticket_number' && $dir === 'asc') ? 'desc' : 'asc'])) }}" style="text-decoration: none; color: var(--text-dark); font-weight: 700;">
                        Incident ID {!! $sort === 'ticket_number' ? ($dir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
                <th style="padding: 1rem 0.5rem;">
                    <a href="{{ route('incidents.index', array_merge(request()->query(), ['sort' => 'title', 'direction' => ($sort === 'title' && $dir === 'asc') ? 'desc' : 'asc'])) }}" style="text-decoration: none; color: var(--text-dark); font-weight: 700;">
                        Summary {!! $sort === 'title' ? ($dir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
                <th style="padding: 1rem 0.5rem;">
                    <a href="{{ route('incidents.index', array_merge(request()->query(), ['sort' => 'priority', 'direction' => ($sort === 'priority' && $dir === 'asc') ? 'desc' : 'asc'])) }}" style="text-decoration: none; color: var(--text-dark); font-weight: 700;">
                        Priority {!! $sort === 'priority' ? ($dir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
                <th style="padding: 1rem 0.5rem;">
                    <a href="{{ route('incidents.index', array_merge(request()->query(), ['sort' => 'status', 'direction' => ($sort === 'status' && $dir === 'asc') ? 'desc' : 'asc'])) }}" style="text-decoration: none; color: var(--text-dark); font-weight: 700;">
                        Status {!! $sort === 'status' ? ($dir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
                <th style="padding: 1rem 0.5rem;">Affected Device</th>
                <th style="padding: 1rem 0.5rem;">Assigned To</th>
                <th style="padding: 1rem 0.5rem;">
                    <a href="{{ route('incidents.index', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => ($sort === 'created_at' && $dir === 'asc') ? 'desc' : 'asc'])) }}" style="text-decoration: none; color: var(--text-dark); font-weight: 700;">
                        Created At {!! $sort === 'created_at' ? ($dir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($incidents as $inc)
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 1rem 0.5rem; font-weight: 700; color: var(--primary);">{{ $inc->ticket_number }}</td>
                <td style="padding: 1rem 0.5rem; font-weight: 600;">{{ $inc->title }}</td>
                <td style="padding: 1rem 0.5rem;">
                    <span class="status-badge {{ $inc->priority === 'critical' ? 'down' : ($inc->priority === 'high' ? 'warning' : 'active') }}" style="font-size: 0.7rem;">
                        {{ ucfirst($inc->priority) }}
                    </span>
                </td>
                <td style="padding: 1rem 0.5rem;">
                    <span class="status-badge {{ $inc->status === 'new' ? 'warning' : 'active' }}" style="font-size: 0.7rem;">
                        {{ ucfirst($inc->status) }}
                    </span>
                </td>
                <td style="padding: 1rem 0.5rem;">{{ $inc->device->name ?? 'None' }}</td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">{{ $inc->assignedTo->name ?? 'Unassigned' }}</td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">{{ $inc->created_at->format('d M Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
