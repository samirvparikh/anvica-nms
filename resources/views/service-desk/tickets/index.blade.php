@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>All Service Desk Tickets</h1>
        <p>Unified list of incidents, problems, and change requests raised in the system.</p>
    </div>
    <div class="page-actions" style="display: flex; gap: 0.75rem;">
        <a href="{{ route('incidents.create') }}" class="btn-add">
            <i class="fa-solid fa-circle-plus" style="margin-right: 0.5rem;"></i>Create Incident
        </a>
        <a href="{{ route('changes.create') }}" class="btn-add" style="background-color: #3b82f6; border-color: #2563eb;">
            <i class="fa-solid fa-code-branch" style="margin-right: 0.5rem;"></i> Raise Change Request
        </a>
    </div>
</div>

<div class="card-table-container" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow);">
    <table class="data-table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                <th style="padding: 1rem 0.5rem;">Ticket Number</th>
                <th style="padding: 1rem 0.5rem;">Type</th>
                <th style="padding: 1rem 0.5rem;">Title</th>
                <th style="padding: 1rem 0.5rem;">Priority</th>
                <th style="padding: 1rem 0.5rem;">Status</th>
                <th style="padding: 1rem 0.5rem;">Created At</th>
                <th style="padding: 1rem 0.5rem;">Assigned To</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tickets as $ticket)
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 1rem 0.5rem; font-weight: 700; color: var(--primary);">{{ $ticket->ticket_number }}</td>
                <td style="padding: 1rem 0.5rem;">
                    <span class="status-badge" style="font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">
                        {{ $ticket->type }}
                    </span>
                </td>
                <td style="padding: 1rem 0.5rem; font-weight: 600;">{{ $ticket->title }}</td>
                <td style="padding: 1rem 0.5rem;">
                    <span class="status-badge {{ $ticket->priority === 'critical' ? 'down' : 'warning' }}" style="font-size: 0.7rem;">
                        {{ ucfirst($ticket->priority) }}
                    </span>
                </td>
                <td style="padding: 1rem 0.5rem;">
                    <span class="status-badge {{ $ticket->status === 'new' ? 'warning' : 'active' }}" style="font-size: 0.7rem;">
                        {{ ucfirst($ticket->status) }}
                    </span>
                </td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">{{ $ticket->created_at->format('d M Y H:i') }}</td>
                <td style="padding: 1rem 0.5rem;">{{ $ticket->assignedTo->name ?? 'Unassigned' }}</td>
            </tr>
            @empty
            <!-- Display placeholder rows if empty -->
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 1rem 0.5rem; font-weight: 700; color: var(--primary);">INC-1024</td>
                <td style="padding: 1rem 0.5rem;"><span class="status-badge down" style="font-size: 0.7rem;">Incident</span></td>
                <td style="padding: 1rem 0.5rem; font-weight: 600;">VPN Connectivity Issue - Mumbai DC</td>
                <td style="padding: 1rem 0.5rem;"><span class="status-badge down" style="font-size: 0.7rem;">Critical</span></td>
                <td style="padding: 1rem 0.5rem;"><span class="status-badge warning" style="font-size: 0.7rem;">In Progress</span></td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">24 Jun 2026 08:30</td>
                <td style="padding: 1rem 0.5rem;">Vijay Kumar</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 1rem 0.5rem; font-weight: 700; color: var(--primary);">CHG-2026-0001</td>
                <td style="padding: 1rem 0.5rem;"><span class="status-badge active" style="font-size: 0.7rem;">Change</span></td>
                <td style="padding: 1rem 0.5rem; font-weight: 600;">Core Router-01 Firmware Upgrade</td>
                <td style="padding: 1rem 0.5rem;"><span class="status-badge warning" style="font-size: 0.7rem;">High</span></td>
                <td style="padding: 1rem 0.5rem;"><span class="status-badge warning" style="font-size: 0.7rem;">Scheduled</span></td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">22 Jun 2026 15:45</td>
                <td style="padding: 1rem 0.5rem;">Unassigned</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
