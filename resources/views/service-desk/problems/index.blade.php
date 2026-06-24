@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Problem Records</h1>
        <p>Investigate and resolve the root cause of recurring incidents and alarms.</p>
    </div>
</div>

<div class="card-table-container" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow);">
    <table class="data-table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                <th style="padding: 1rem 0.5rem;">Problem ID</th>
                <th style="padding: 1rem 0.5rem;">Title</th>
                <th style="padding: 1rem 0.5rem;">Priority</th>
                <th style="padding: 1rem 0.5rem;">Status</th>
                <th style="padding: 1rem 0.5rem;">Created At</th>
                <th style="padding: 1rem 0.5rem;">Root Cause</th>
            </tr>
        </thead>
        <tbody>
            @forelse($problems as $prob)
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 1rem 0.5rem; font-weight: 700; color: var(--primary);">{{ $prob->ticket_number }}</td>
                <td style="padding: 1rem 0.5rem; font-weight: 600;">{{ $prob->title }}</td>
                <td style="padding: 1rem 0.5rem;">
                    <span class="status-badge {{ $prob->priority === 'critical' ? 'down' : 'warning' }}" style="font-size: 0.7rem;">
                        {{ ucfirst($prob->priority) }}
                    </span>
                </td>
                <td style="padding: 1rem 0.5rem;">
                    <span class="status-badge warning" style="font-size: 0.7rem; background-color: var(--bg-warning); color: var(--status-warning);">
                        {{ ucfirst(str_replace('_', ' ', $prob->status)) }}
                    </span>
                </td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">{{ $prob->created_at->format('d M Y H:i') }}</td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">{{ $prob->description ?? 'Under Investigation' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="padding: 1.5rem 0.5rem; text-align: center; color: var(--text-muted);">No problems logged.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
