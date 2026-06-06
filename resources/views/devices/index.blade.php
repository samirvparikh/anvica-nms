@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Devices</h1>
        <p>All monitored network devices.
            @if(!is_null($deviceLimit))
                <span style="color: var(--text-muted);">({{ $deviceCount }} / {{ $deviceLimit }} devices used)</span>
            @endif
        </p>
    </div>
    @if($canAddDevice)
    <button class="btn-add" id="openAddModalBtn">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Add Device
    </button>
    @else
    <button class="btn-add" disabled title="Device limit reached" style="opacity: 0.5; cursor: not-allowed;">
        Device Limit Reached
    </button>
    @endif
</div>

<!-- Devices Card and Table -->
<div class="card-table-container">
    <div class="table-toolbar">
        <div class="table-search">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="deviceSearchInput" placeholder="Search devices...">
        </div>
    </div>

    <table class="data-table" id="devicesTable">
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>IP Address</th>
                <th>Location</th>
                <th>Status</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($devices as $device)
            <tr class="device-row" data-name="{{ strtolower($device->name) }}" data-type="{{ strtolower($device->type) }}" data-ip="{{ $device->ip_address }}" data-loc="{{ strtolower($device->location) }}">
                <td style="font-weight: 700;">{{ $device->name }}</td>
                <td>{{ $device->type }}</td>
                <td>{{ $device->ip_address }}</td>
                <td>{{ $device->location }}</td>
                <td>
                    <span class="status-badge {{ strtolower($device->status) }}">
                        {{ $device->status }}
                    </span>
                </td>
                <td style="text-align: right;">
                    <!-- Edit Action Button -->
                    <button class="btn-action edit-btn editDeviceBtn" 
                            data-id="{{ $device->id }}"
                            data-name="{{ $device->name }}"
                            data-type="{{ $device->type }}"
                            data-ip="{{ $device->ip_address }}"
                            data-location="{{ $device->location }}"
                            data-status="{{ $device->status }}">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline-block; vertical-align:middle;">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                    </button>
                    
                    <!-- Delete Action Button -->
                    <form action="{{ route('devices.destroy', $device->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this device?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-action delete-btn">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline-block; vertical-align:middle;">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                <line x1="10" y1="11" x2="10" y2="17"/>
                                <line x1="14" y1="11" x2="14" y2="17"/>
                            </svg>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem 0;">No devices found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Add Device Modal -->
<div class="modal-overlay" id="addDeviceModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Add Monitored Device</h3>
            <button class="modal-close" id="closeAddModalBtn">&times;</button>
        </div>
        <form action="{{ route('devices.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="add_name" style="margin-bottom:0.25rem;">Name</label>
                    <input type="text" id="add_name" name="name" class="form-control" placeholder="e.g. Switch-Floor2" required>
                </div>
                <div class="form-group">
                    <label for="add_type" style="margin-bottom:0.25rem;">Type</label>
                    <select id="add_type" name="type" class="form-control" required>
                        <option value="">Select Type</option>
                        <option value="Switch">Switch</option>
                        <option value="Firewall">Firewall</option>
                        <option value="Router">Router</option>
                        <option value="Access Point">Access Point</option>
                        <option value="Server">Server</option>
                        <option value="CCTV">CCTV</option>
                        <option value="UPS">UPS</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="add_ip" style="margin-bottom:0.25rem;">IP Address</label>
                    <input type="text" id="add_ip" name="ip_address" class="form-control" placeholder="e.g. 192.168.1.50" style="padding-left:1rem;" required>
                </div>
                <div class="form-group">
                    <label for="add_location" style="margin-bottom:0.25rem;">Location</label>
                    <input type="text" id="add_location" name="location" class="form-control" placeholder="e.g. Rack A1" style="padding-left:1rem;" required>
                </div>
                <div class="form-group">
                    <label for="add_status" style="margin-bottom:0.25rem;">Status</label>
                    <select id="add_status" name="status" class="form-control" required>
                        <option value="Up">Up</option>
                        <option value="Warning">Warning</option>
                        <option value="Down">Down</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="cancelAddModalBtn">Cancel</button>
                <button type="submit" class="btn-primary" style="width:auto; padding: 0.5rem 1.5rem;">Save Device</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Device Modal -->
<div class="modal-overlay" id="editDeviceModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Edit Monitored Device</h3>
            <button class="modal-close" id="closeEditModalBtn">&times;</button>
        </div>
        <form action="" method="POST" id="editDeviceForm">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_name" style="margin-bottom:0.25rem;">Name</label>
                    <input type="text" id="edit_name" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_type" style="margin-bottom:0.25rem;">Type</label>
                    <select id="edit_type" name="type" class="form-control" required>
                        <option value="Switch">Switch</option>
                        <option value="Firewall">Firewall</option>
                        <option value="Router">Router</option>
                        <option value="Access Point">Access Point</option>
                        <option value="Server">Server</option>
                        <option value="CCTV">CCTV</option>
                        <option value="UPS">UPS</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_ip" style="margin-bottom:0.25rem;">IP Address</label>
                    <input type="text" id="edit_ip" name="ip_address" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_location" style="margin-bottom:0.25rem;">Location</label>
                    <input type="text" id="edit_location" name="location" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_status" style="margin-bottom:0.25rem;">Status</label>
                    <select id="edit_status" name="status" class="form-control" required>
                        <option value="Up">Up</option>
                        <option value="Warning">Warning</option>
                        <option value="Down">Down</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="cancelEditModalBtn">Cancel</button>
                <button type="submit" class="btn-primary" style="width:auto; padding: 0.5rem 1.5rem;">Update Device</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Table search filtering logic
        const searchInput = document.getElementById('deviceSearchInput');
        const tableRows = document.querySelectorAll('.device-row');

        searchInput.addEventListener('keyup', function(e) {
            const query = e.target.value.toLowerCase().trim();
            
            tableRows.forEach(row => {
                const name = row.getAttribute('data-name');
                const type = row.getAttribute('data-type');
                const ip = row.getAttribute('data-ip');
                const location = row.getAttribute('data-loc');

                if (name.includes(query) || type.includes(query) || ip.includes(query) || location.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Add Modal Open/Close
        const addModal = document.getElementById('addDeviceModal');
        const openAddBtn = document.getElementById('openAddModalBtn');
        const closeAddBtn = document.getElementById('closeAddModalBtn');
        const cancelAddBtn = document.getElementById('cancelAddModalBtn');

        function toggleAddModal(open) {
            if (open) {
                addModal.classList.add('open');
            } else {
                addModal.classList.remove('open');
            }
        }

        if (openAddBtn) {
            openAddBtn.addEventListener('click', () => toggleAddModal(true));
        }
        if (closeAddBtn) closeAddBtn.addEventListener('click', () => toggleAddModal(false));
        if (cancelAddBtn) cancelAddBtn.addEventListener('click', () => toggleAddModal(false));

        // Edit Modal Open/Close & Setup values
        const editModal = document.getElementById('editDeviceModal');
        const closeEditBtn = document.getElementById('closeEditModalBtn');
        const cancelEditBtn = document.getElementById('cancelEditModalBtn');
        const editForm = document.getElementById('editDeviceForm');

        const editNameInput = document.getElementById('edit_name');
        const editTypeInput = document.getElementById('edit_type');
        const editIpInput = document.getElementById('edit_ip');
        const editLocationInput = document.getElementById('edit_location');
        const editStatusInput = document.getElementById('edit_status');

        document.querySelectorAll('.editDeviceBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const type = this.getAttribute('data-type');
                const ip = this.getAttribute('data-ip');
                const location = this.getAttribute('data-location');
                const status = this.getAttribute('data-status');

                // set input values
                editNameInput.value = name;
                editTypeInput.value = type;
                editIpInput.value = ip;
                editLocationInput.value = location;
                editStatusInput.value = status;

                // set action url
                editForm.action = `/devices/${id}`;

                // open modal
                editModal.classList.add('open');
            });
        });

        function closeEditModal() {
            editModal.classList.remove('open');
        }

        closeEditBtn.addEventListener('click', closeEditModal);
        cancelEditBtn.addEventListener('click', closeEditModal);
    });
</script>
@endsection
