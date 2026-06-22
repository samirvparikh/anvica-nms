@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Alarms</h1>
        <p>Operational alarms — acknowledge issues across your infrastructure.</p>
    </div>
    @if($isAdmin)
    <button class="btn-add" type="button" id="openAddAlarmBtn">+ Add Alarm</button>
    @endif
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
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
        <h3 style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin: 0;">Active &amp; Acknowledged Alarms</h3>
        <div class="table-search">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="alarmSearchInput" placeholder="Search alarms...">
        </div>
    </div>

    <table class="data-table" id="alarmsTable">
        <thead>
            <tr>
                <th style="width: 80px;">Severity</th>
                <th>Device</th>
                <th>Description</th>
                <th>Status</th>
                <th>Timestamp</th>
                <th class="col-actions" style="text-align: right; width: {{ $isAdmin ? '220px' : '150px' }};">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($alarms as $alarm)
            <tr class="alarm-row"
                data-device="{{ strtolower($alarm->device_name) }}"
                data-msg="{{ strtolower($alarm->message) }}"
                data-sev="{{ strtolower($alarm->severity) }}"
                data-status="{{ strtolower($alarm->status) }}">
                <td>
                    <span class="status-badge {{ $alarm->severity === 'Critical' ? 'down' : 'warning' }}" style="padding: 0.2rem 0.5rem; border-radius: 4px;">
                        {{ $alarm->severity }}
                    </span>
                </td>
                <td style="font-weight: 700;">{{ $alarm->device_name }}</td>
                <td style="color: var(--text-muted);">{{ $alarm->message }}</td>
                <td>
                    <span class="status-badge {{ $alarm->status === 'Open' ? 'warning' : 'up' }}">{{ $alarm->status }}</span>
                </td>
                <td>{{ $alarm->created_at->format('M d, Y h:i A') }}</td>
                <td style="text-align: right; white-space: nowrap;">
                    @if($alarm->status === 'Open')
                    <form action="{{ route('alarms.ack', $alarm) }}" method="POST" style="display: inline-block;">
                        @csrf
                        <button type="submit" class="btn-action ack-btn">Acknowledge</button>
                    </form>
                    @endif
                    @if($isAdmin)
                    <button type="button" class="btn-action edit-btn editAlarmBtn"
                        data-id="{{ $alarm->id }}"
                        data-device-name="{{ $alarm->device_name }}"
                        data-severity="{{ $alarm->severity }}"
                        data-message="{{ $alarm->message }}"
                        data-status="{{ $alarm->status }}">Edit</button>
                    <form action="{{ route('alarms.destroy', $alarm) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Delete this alarm?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-action delete-btn">Delete</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr class="no-sort-row">
                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem 0;">No alarms recorded.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($isAdmin)
<div class="modal-overlay" id="alarmModal">
    <div class="modal-card modal-card-wide">
        <div class="modal-header">
            <h3 id="alarmModalTitle">Add Alarm</h3>
            <button class="modal-close" type="button" id="closeAlarmModal">&times;</button>
        </div>
        <form action="{{ route('alarms.store') }}" method="POST" id="alarmForm">
            @csrf
            <div id="alarmMethodField"></div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="alarm_device_name">Device</label>
                    <input type="text" name="device_name" id="alarm_device_name" class="form-control" list="alarmDeviceList" required>
                    <datalist id="alarmDeviceList">
                        @foreach($devices as $device)
                            <option value="{{ $device->name }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="form-group">
                    <label for="alarm_severity">Severity</label>
                    <select name="severity" id="alarm_severity" class="form-control" required>
                        <option value="Critical">Critical</option>
                        <option value="Warning">Warning</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="alarm_message">Message</label>
                    <textarea name="message" id="alarm_message" class="form-control" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="alarm_status">Status</label>
                    <select name="status" id="alarm_status" class="form-control" required>
                        <option value="Open">Open</option>
                        <option value="Acknowledged">Acknowledged</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="cancelAlarmModal">Cancel</button>
                <button type="submit" class="btn-primary" style="width:auto;padding:0.5rem 1.5rem;">Save</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById('alarmSearchInput');
        const tableRows = document.querySelectorAll('.alarm-row');

        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const query = searchInput.value.toLowerCase().trim();
                tableRows.forEach(row => {
                    const device = row.getAttribute('data-device') || '';
                    const message = row.getAttribute('data-msg') || '';
                    const severity = row.getAttribute('data-sev') || '';
                    const status = row.getAttribute('data-status') || '';
                    row.style.display = (device.includes(query) || message.includes(query) || severity.includes(query) || status.includes(query)) ? '' : 'none';
                });
            });
        }

        const modal = document.getElementById('alarmModal');
        const form = document.getElementById('alarmForm');
        const methodField = document.getElementById('alarmMethodField');
        const openBtn = document.getElementById('openAddAlarmBtn');
        const closeBtn = document.getElementById('closeAlarmModal');
        const cancelBtn = document.getElementById('cancelAlarmModal');

        function openModal(edit, data) {
            if (!modal || !form) return;

            document.getElementById('alarmModalTitle').textContent = edit ? 'Edit Alarm' : 'Add Alarm';
            methodField.innerHTML = edit ? '<input type="hidden" name="_method" value="PUT">' : '';
            form.action = edit ? ('{{ url('/alarms') }}/' + data.id) : '{{ route('alarms.store') }}';
            document.getElementById('alarm_device_name').value = data.deviceName || '';
            document.getElementById('alarm_severity').value = data.severity || 'Warning';
            document.getElementById('alarm_message').value = data.message || '';
            document.getElementById('alarm_status').value = data.status || 'Open';
            modal.classList.add('open');
        }

        function closeModal() {
            if (modal) modal.classList.remove('open');
        }

        if (openBtn) openBtn.addEventListener('click', () => openModal(false, {}));
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
        if (modal) modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

        document.querySelectorAll('.editAlarmBtn').forEach(btn => {
            btn.addEventListener('click', () => openModal(true, {
                id: btn.dataset.id,
                deviceName: btn.dataset.deviceName,
                severity: btn.dataset.severity,
                message: btn.dataset.message,
                status: btn.dataset.status,
            }));
        });
    });
</script>
@endsection
