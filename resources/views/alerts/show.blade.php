@extends('layouts.app')

@section('content')
@php
    $deviceName = $alert->device?->name ?? 'Unknown';
    $isConverted = $alert->converted_to_alarm_at !== null;
    $statusLabel = $isConverted
        ? 'Alarm'
        : ($alert->status === \App\Models\Alert::STATUS_OPEN
            ? ($alert->acknowledged_at ? 'Acknowledged' : 'Open')
            : 'Resolved');
    $statusClass = $isConverted
        ? 'down'
        : ($alert->status === \App\Models\Alert::STATUS_OPEN
            ? ($alert->acknowledged_at ? 'up' : 'warning')
            : 'up');
@endphp

<div class="page-header">
    <div class="page-title">
        <h1>Alert #{{ $alert->id }}</h1>
        <p>Alert details and activity history.</p>
    </div>
    <a href="{{ route('alerts.index') }}" class="btn-secondary" style="text-decoration:none;display:inline-flex;align-items:center;">Back to Alerts</a>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif

@if($errors->any())
<div class="alert alert-danger" style="margin-bottom:1rem;">
    {{ $errors->first() }}
</div>
@endif

<div class="card-table-container" style="margin-bottom: 1.25rem;">
    <div class="table-toolbar">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin: 0;">Alert Details</h3>
        @if($alert->status === \App\Models\Alert::STATUS_OPEN && ! $isConverted)
            <button
                type="button"
                class="btn-action ack-btn"
                id="openShowActionModal"
                data-alert-id="{{ $alert->id }}"
                data-default-action="{{ $alert->acknowledged_at ? 'resolved' : 'acknowledged' }}"
            >Update Status</button>
        @endif
    </div>

    <table class="data-table">
        <tbody>
            <tr>
                <th style="width: 180px;">Device</th>
                <td style="font-weight: 700;">{{ $deviceName }}</td>
            </tr>
            <tr>
                <th>Type</th>
                <td>{{ $alert->alarm_type ?? 'Alert' }}</td>
            </tr>
            <tr>
                <th>Severity</th>
                <td>
                    <span class="status-badge {{ $alert->severity === 'critical' ? 'down' : 'warning' }}">
                        {{ ucfirst($alert->severity) }}
                    </span>
                </td>
            </tr>
            <tr>
                <th>Status</th>
                <td><span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
            </tr>
            <tr>
                <th>Description</th>
                <td style="color: var(--text-muted);">{{ $alert->message }}</td>
            </tr>
            <tr>
                <th>Started At</th>
                <td>{{ ($alert->started_at ?? $alert->created_at)->format('M d, Y h:i A') }}</td>
            </tr>
            <tr>
                <th>Acknowledged At</th>
                <td>
                    @if($alert->acknowledged_at)
                        {{ $alert->acknowledged_at->format('M d, Y h:i A') }}
                        @if($alert->acknowledgedBy)
                            <span style="color: var(--text-muted);">by {{ $alert->acknowledgedBy->name }}</span>
                        @endif
                    @else
                        —
                    @endif
                </td>
            </tr>
            <tr>
                <th>Resolved At</th>
                <td>{{ $alert->resolved_at?->format('M d, Y h:i A') ?? '—' }}</td>
            </tr>
            <tr>
                <th>Converted to Alarm</th>
                <td>{{ $alert->converted_to_alarm_at?->format('M d, Y h:i A') ?? '—' }}</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="card-table-container">
    <div class="table-toolbar">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin: 0;">Activity History</h3>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 60px;">#</th>
                <th>Action</th>
                <th>Status</th>
                <th>Remarks</th>
                <th>By</th>
                <th>Date / Time</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $index => $activity)
                <tr>
                    <td style="text-align:center;font-weight:700;">{{ $activities->count() - $index }}</td>
                    <td style="font-weight:600;">{{ $activity->actionLabel() }}</td>
                    <td>
                        @if($activity->status)
                            <span class="status-badge {{ $activity->action === \App\Models\AlertActivity::ACTION_CONVERTED_TO_ALARM ? 'down' : 'up' }}">
                                {{ ucfirst(str_replace('_', ' ', $activity->status)) }}
                            </span>
                        @else
                            —
                        @endif
                    </td>
                    <td style="color: var(--text-muted);">{{ $activity->remarks ?: '—' }}</td>
                    <td>{{ $activity->user?->name ?? 'System' }}</td>
                    <td style="white-space: nowrap;">{{ $activity->created_at->format('M d, Y h:i A') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:var(--text-muted);padding:2rem 0;">
                        No activity recorded yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($alert->status === \App\Models\Alert::STATUS_OPEN && ! $isConverted)
<div class="modal-overlay" id="alertActionModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Update Alert Status</h3>
            <button type="button" class="modal-close" id="closeAlertActionModal">&times;</button>
        </div>
        <form method="POST" action="{{ route('alerts.ack', $alert) }}" id="alertActionForm">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="alertActionStatus">Status <span style="color:#dc2626;">*</span></label>
                    <select name="action" id="alertActionStatus" class="form-control" required>
                        <option value="acknowledged" @selected(! $alert->acknowledged_at)>Acknowledged</option>
                        <option value="convert_to_alarm">Convert to Alarm</option>
                        <option value="resolved" @selected($alert->acknowledged_at)>Resolve</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="alertActionRemarks">Remarks</label>
                    <textarea name="remarks" id="alertActionRemarks" class="form-control" rows="3" maxlength="2000" placeholder="Add remarks / notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="cancelAlertActionModal">Cancel</button>
                <button type="submit" class="btn-primary" style="width:auto;padding:0.5rem 1.5rem;">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('alertActionModal');
    const openBtn = document.getElementById('openShowActionModal');
    if (!modal || !openBtn) return;

    const close = () => modal.classList.remove('open');
    openBtn.addEventListener('click', () => modal.classList.add('open'));
    document.getElementById('closeAlertActionModal').addEventListener('click', close);
    document.getElementById('cancelAlertActionModal').addEventListener('click', close);
    modal.addEventListener('click', (e) => { if (e.target === modal) close(); });
});
</script>
@endif
@endsection
