@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>SLA Targets Configuration</h1>
        <p>Define targets, resolution deadlines, daily ticket limits, and escalation rules for customer tiers.</p>
    </div>
</div>

<div class="card-table-container" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow);">
    <table class="data-table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                <th style="padding: 1rem 0.5rem;">SLA Name</th>
                <th style="padding: 1rem 0.5rem;">Description</th>
                <th style="padding: 1rem 0.5rem;">Response Target</th>
                <th style="padding: 1rem 0.5rem;">Resolution Target</th>
                <th style="padding: 1rem 0.5rem;">Escalation Deadline</th>
                <th style="padding: 1rem 0.5rem;">Max Tickets / Day</th>
                <th style="padding: 1rem 0.5rem;">Max Changes / Week</th>
            </tr>
        </thead>
        <tbody>
            @forelse($policies as $policy)
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 1rem 0.5rem; font-weight: 700; color: var(--text-dark);">{{ $policy->name }}</td>
                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">{{ $policy->description ?? '—' }}</td>
                <td style="padding: 1rem 0.5rem;"><span class="status-badge active" style="background-color: var(--bg-up); color: var(--status-up);">{{ $policy->response_time_minutes }} Mins</span></td>
                <td style="padding: 1rem 0.5rem;"><span class="status-badge active" style="background-color: var(--bg-up); color: var(--status-up);">{{ $policy->resolution_time_minutes }} Mins</span></td>
                <td style="padding: 1rem 0.5rem; font-weight: 600;">{{ $policy->escalation_time_minutes }} Mins</td>
                <td style="padding: 1rem 0.5rem;">{{ $policy->max_tickets_per_day }}</td>
                <td style="padding: 1rem 0.5rem;">{{ $policy->max_changes_per_week }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="padding: 2rem 0.5rem; text-align: center; color: var(--text-muted);">No policies found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
