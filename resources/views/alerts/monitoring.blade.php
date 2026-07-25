@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Alerts</h1>
        <p>Monitoring alerts generated automatically from device metrics and health checks.</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif

@if($errors->any())
<div class="alert alert-danger" style="margin-bottom:1rem;">
    {{ $errors->first() }}
</div>
@endif

<div class="alarm-summary-cards">
    <div class="alarm-summary-card critical">
        <h4>Critical</h4>
        <div class="value">{{ $criticalCount }}</div>
    </div>
    <div class="alarm-summary-card warning">
        <h4>Warning</h4>
        <div class="value">{{ $warningCount }}</div>
    </div>
    <div class="alarm-summary-card acknowledged">
        <h4>Acknowledged</h4>
        <div class="value">{{ $ackCount }}</div>
    </div>
</div>

<div class="card-table-container">
    <div class="table-toolbar">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin: 0;">Monitoring Alerts</h3>
        <div class="table-search">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="alertSearchInput" placeholder="Search alerts...">
        </div>
    </div>

    <table class="data-table" id="alertsTable">
        <thead>
            <tr>
                <th style="width: 80px;">Severity</th>
                <th>Type</th>
                <th>Device</th>
                <th>Description</th>
                <th>Timestamp</th>
                <th class="col-actions" style="text-align: right; width: 220px;">Status / Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($alerts as $alert)
            @php
                $deviceName = $alert->device?->name ?? 'Unknown';
                $isOpen = $alert->status === \App\Models\Alert::STATUS_OPEN;
                $isAcknowledged = $alert->acknowledged_at !== null;
                $isConverted = $alert->converted_to_alarm_at !== null;
            @endphp
            <tr class="alert-row"
                data-device="{{ strtolower($deviceName) }}"
                data-msg="{{ strtolower($alert->message) }}"
                data-sev="{{ strtolower($alert->severity) }}"
                data-status="{{ strtolower($alert->status) }}">
                <td>
                    <span class="status-badge {{ $alert->severity === 'critical' ? 'down' : 'warning' }}" style="padding: 0.2rem 0.5rem; border-radius: 4px;">
                        {{ ucfirst($alert->severity) }}
                    </span>
                </td>
                <td style="font-weight: 600;">{{ $alert->alarm_type ?? 'Alert' }}</td>
                <td style="font-weight: 700;">{{ $deviceName }}</td>
                <td style="color: var(--text-muted);">{{ $alert->message }}</td>
                <td>{{ ($alert->started_at ?? $alert->created_at)->format('M d, Y h:i A') }}</td>
                <td style="text-align: right;">
                    <div style="display: inline-flex; align-items: center; gap: 0.5rem; justify-content: flex-end; flex-wrap: wrap;">
                        @if($isOpen && ! $isAcknowledged)
                            <button
                                type="button"
                                class="btn-action ack-btn open-alert-action-modal"
                                data-alert-id="{{ $alert->id }}"
                                data-ack-url="{{ route('alerts.ack', $alert) }}"
                                data-alert-device="{{ $deviceName }}"
                                data-alert-message="{{ $alert->message }}"
                                data-default-action="acknowledged"
                            >Acknowledge</button>
                        @elseif($isOpen && $isAcknowledged)
                            <span class="status-badge up">Acknowledged</span>
                            <button
                                type="button"
                                class="btn-action edit-btn open-alert-action-modal"
                                data-alert-id="{{ $alert->id }}"
                                data-ack-url="{{ route('alerts.ack', $alert) }}"
                                data-alert-device="{{ $deviceName }}"
                                data-alert-message="{{ $alert->message }}"
                                data-default-action="resolved"
                            >Update</button>
                        @elseif($isConverted)
                            <span class="status-badge down">Alarm</span>
                        @else
                            <span class="status-badge up">Closed</span>
                        @endif

                        <a href="{{ route('alerts.show', $alert) }}" class="btn-action view-btn" title="View activity history">History</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem 0;">No monitoring alerts yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="modal-overlay" id="alertActionModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 id="alertActionModalTitle">Update Alert Status</h3>
            <button type="button" class="modal-close" id="closeAlertActionModal">&times;</button>
        </div>
        <form method="POST" id="alertActionForm">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Device</label>
                    <input type="text" id="alertActionDevice" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="alertActionMessage" class="form-control" rows="2" readonly></textarea>
                </div>
                <div class="form-group">
                    <label for="alertActionStatus">Status <span style="color:#dc2626;">*</span></label>
                    <select name="action" id="alertActionStatus" class="form-control" required>
                        <option value="acknowledged">Acknowledged</option>
                        <option value="convert_to_alarm">Convert to Alarm</option>
                        <option value="resolved">Resolve</option>
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
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById('alertSearchInput');
        const tableRows = document.querySelectorAll('.alert-row');
        const modal = document.getElementById('alertActionModal');
        const form = document.getElementById('alertActionForm');
        const statusSelect = document.getElementById('alertActionStatus');
        const deviceInput = document.getElementById('alertActionDevice');
        const messageInput = document.getElementById('alertActionMessage');
        const remarksInput = document.getElementById('alertActionRemarks');
        const titleEl = document.getElementById('alertActionModalTitle');

        if (searchInput) {
            searchInput.addEventListener('keyup', function(e) {
                const query = e.target.value.toLowerCase().trim();
                tableRows.forEach(row => {
                    const device = row.getAttribute('data-device');
                    const message = row.getAttribute('data-msg');
                    const severity = row.getAttribute('data-sev');
                    const status = row.getAttribute('data-status');
                    row.style.display = (device.includes(query) || message.includes(query) || severity.includes(query) || status.includes(query)) ? '' : 'none';
                });
            });
        }

        function openActionModal(btn) {
            const defaultAction = btn.getAttribute('data-default-action') || 'acknowledged';
            form.action = btn.getAttribute('data-ack-url') || '';
            deviceInput.value = btn.getAttribute('data-alert-device') || '';
            messageInput.value = btn.getAttribute('data-alert-message') || '';
            remarksInput.value = '';
            statusSelect.value = defaultAction;
            titleEl.textContent = defaultAction === 'acknowledged' ? 'Acknowledge Alert' : 'Update Alert Status';
            modal.classList.add('open');
        }

        function closeActionModal() {
            modal.classList.remove('open');
        }

        document.querySelectorAll('.open-alert-action-modal').forEach(function (btn) {
            btn.addEventListener('click', function () {
                openActionModal(btn);
            });
        });

        document.getElementById('closeAlertActionModal').addEventListener('click', closeActionModal);
        document.getElementById('cancelAlertActionModal').addEventListener('click', closeActionModal);
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closeActionModal();
            }
        });
    });
</script>
@endsection
