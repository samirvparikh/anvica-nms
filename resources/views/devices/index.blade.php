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
                @if($isAdmin)<th>Customer</th>@endif
                <th>Service</th>
                <th>Vendor</th>
                <th>IP Address</th>
                <th>Location</th>
                <th>Status</th>
                @if($canAddDevice)<th class="col-actions" data-no-sort="true" style="text-align: center; width: 110px;">Add IP</th>@endif
                @if($isAdmin)<th class="col-actions" data-no-sort="true" style="text-align: center; width: 90px;">Script</th>@endif
                <th class="col-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($devices as $device)
            <tr class="device-row" data-name="{{ strtolower($device->name) }}" data-type="{{ strtolower($device->type) }}" data-ip="{{ $device->ip_address }}" data-loc="{{ strtolower($device->location) }}">
                <td style="font-weight: 700;">{{ $device->name }}</td>
                @if($isAdmin)<td>{{ $device->user?->name ?? 'Unassigned' }}</td>@endif
                <td>{{ $device->service?->name ?? $device->type }}</td>
                <td>{{ $device->vendorDisplayName() ?? '—' }}</td>
                <td>{{ $device->ip_address }}</td>
                <td>{{ $device->location }}</td>
                <td>
                    <span class="status-badge {{ $device->status === 'active' ? 'active' : 'inactive' }}">
                        {{ $device->status === 'active' ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                @if($canAddDevice)
                @php
                    $rowCanAddIp = ! $isAdmin || ! $device->user_id || ($device->user && $device->user->canAddDevice());
                @endphp
                <td style="text-align: center;">
                    @if($rowCanAddIp)
                    <button type="button"
                            class="btn-add-ip cloneDeviceBtn"
                            title="Add new IP with same device details"
                            data-name="{{ $device->name }}"
                            data-user-id="{{ $device->user_id }}"
                            data-service-id="{{ $device->service_id }}"
                            data-vendor-id="{{ $device->vendor_id }}"
                            data-hostname="{{ $device->hostname }}"
                            data-type="{{ $device->type }}"
                            data-location="{{ $device->location }}"
                            data-snmp-community="{{ $device->snmp_community }}">
                        <svg class="btn-add-ip-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        <span>Add IP</span>
                    </button>
                    @else
                    <span class="device-limit-badge" title="Device limit reached for {{ $device->user?->name ?? 'this customer' }}">Limit</span>
                    @endif
                </td>
                @endif
                @if($isAdmin)
                <td style="text-align: center;">
                    <a href="{{ route('devices.script.edit', $device) }}"
                       class="btn-action script-btn {{ $device->script ? 'script-btn-active' : '' }}"
                       title="{{ $device->script ? 'Edit published script' : 'Create MikroTik script' }}">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                    </a>
                </td>
                @endif
                <td style="text-align: right;">
                    <!-- Edit Action Button -->
                    <button class="btn-action edit-btn editDeviceBtn" 
                            data-id="{{ $device->id }}"
                            data-name="{{ $device->name }}"
                            data-user-id="{{ $device->user_id }}"
                            data-service-id="{{ $device->service_id }}"
                            data-vendor-id="{{ $device->vendor_id }}"
                            data-hostname="{{ $device->hostname }}"
                            data-type="{{ $device->type }}"
                            data-ip="{{ $device->ip_address }}"
                            data-location="{{ $device->location }}"
                            data-snmp-version="{{ $device->snmp_version }}"
                            data-snmp-port="{{ $device->snmp_port }}"
                            data-snmp-community="{{ $device->snmp_community }}"
                            data-api-url="{{ $device->api_url }}"
                            data-api-username="{{ $device->api_username }}"
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
                <td colspan="{{ $isAdmin ? ($canAddDevice ? 11 : 10) : ($canAddDevice ? 9 : 8) }}" style="text-align: center; color: var(--text-muted); padding: 2rem 0;">No devices found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Add Device Modal -->
<div class="modal-overlay" id="addDeviceModal">
    <div class="modal-card modal-card-wide">
        <div class="modal-header">
            <h3>Add Monitored Device</h3>
            <button class="modal-close" id="closeAddModalBtn">&times;</button>
        </div>
        <form action="{{ route('devices.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                @if($errors->has('device_limit') || $errors->has('account'))
                <div class="form-alert form-alert-error" style="margin-bottom: 1rem;">
                    {{ $errors->first('device_limit') ?: $errors->first('account') }}
                </div>
                @endif

                @if($isAdmin)
                <div class="form-group">
                    <label for="add_user_id">Customer (User)</label>
                    <select id="add_user_id" name="user_id" class="form-control">
                        <option value="">Unassigned (Admin)</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ (string) old('user_id') === (string) $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                                @if($customer->device_limit !== null)
                                    ({{ $customer->devices_count }}/{{ $customer->device_limit }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                
                <div class="form-group">
                    <label for="add_name">Device Name(identity)</label>
                    <input type="text" id="add_name" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label for="add_hostname">Hostname</label>
                    <input type="text" id="add_hostname" name="hostname" class="form-control" value="{{ old('hostname') }}">
                </div>
                
                <div class="form-group">
                    <label for="add_service_id">Service</label>
                    <select id="add_service_id" name="service_id" class="form-control" required>
                        <option value="">Select Service</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ (string) old('service_id') === (string) $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="add_vendor_id">Vendor</label>
                    <select id="add_vendor_id" name="vendor_id" class="form-control">
                        <option value="">Select Vendor</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" data-service="{{ $vendor->service_id }}" {{ (string) old('vendor_id') === (string) $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <input type="hidden" id="add_type" name="type" value="{{ old('type') }}">
                <div class="form-group">
                    <label for="add_ip" style="margin-bottom:0.25rem;">IP Address</label>
                    <input type="text" id="add_ip" name="ip_address" class="form-control" placeholder="e.g. 192.168.1.50" style="padding-left:1rem;" value="{{ old('ip_address') }}" required>
                </div>
                <div class="form-group">
                    <label for="add_location" style="margin-bottom:0.25rem;">Location</label>
                    <input type="text" id="add_location" name="location" class="form-control" placeholder="e.g. Rack A1" style="padding-left:1rem;" value="{{ old('location') }}" required>
                </div>
                <div class="form-group">
                    <label for="add_snmp_community">SNMP Community</label>
                    <input type="text" id="add_snmp_community" name="snmp_community" class="form-control" value="{{ old('snmp_community', 'Anvica_NMS') }}">
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
    <div class="modal-card modal-card-wide">
        <div class="modal-header">
            <h3>Edit Monitored Device</h3>
            <button class="modal-close" id="closeEditModalBtn">&times;</button>
        </div>
        <form action="" method="POST" id="editDeviceForm">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_name">Device Name</label>
                    <input type="text" id="edit_name" name="name" class="form-control" required>
                </div>
                @if($isAdmin)
                <div class="form-group">
                    <label for="edit_user_id">Customer (User)</label>
                    <select id="edit_user_id" name="user_id" class="form-control">
                        <option value="">Unassigned (Admin)</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="form-group">
                    <label for="edit_service_id">Service</label>
                    <select id="edit_service_id" name="service_id" class="form-control" required>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_vendor_id">Vendor</label>
                    <select id="edit_vendor_id" name="vendor_id" class="form-control">
                        <option value="">Select Vendor</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" data-service="{{ $vendor->service_id }}">{{ $vendor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_hostname">Hostname</label>
                    <input type="text" id="edit_hostname" name="hostname" class="form-control">
                </div>
                <input type="hidden" id="edit_type" name="type" value="">
                <div class="form-group">
                    <label for="edit_ip" style="margin-bottom:0.25rem;">IP Address</label>
                    <input type="text" id="edit_ip" name="ip_address" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_location" style="margin-bottom:0.25rem;">Location</label>
                    <input type="text" id="edit_location" name="location" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_snmp_community">SNMP Community</label>
                    <input type="text" id="edit_snmp_community" name="snmp_community" class="form-control" placeholder="Enter SNMP Community" value="Anvica_NMS">
                </div>
                <div class="form-group">
                    <label for="edit_status" style="margin-bottom:0.25rem;">Status</label>
                    <select id="edit_status" name="status" class="form-control" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
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

        const addModal = document.getElementById('addDeviceModal');
        const openAddBtn = document.getElementById('openAddModalBtn');
        const closeAddBtn = document.getElementById('closeAddModalBtn');
        const cancelAddBtn = document.getElementById('cancelAddModalBtn');
        const editModal = document.getElementById('editDeviceModal');
        const closeEditBtn = document.getElementById('closeEditModalBtn');
        const cancelEditBtn = document.getElementById('cancelEditModalBtn');
        const editForm = document.getElementById('editDeviceForm');

        const serviceTypeMap = @json($services->pluck('name', 'id'));
        const addService = document.getElementById('add_service_id');
        const addType = document.getElementById('add_type');
        const addVendor = document.getElementById('add_vendor_id');
        const addName = document.getElementById('add_name');
        const addHostname = document.getElementById('add_hostname');
        const addIp = document.getElementById('add_ip');
        const addLocation = document.getElementById('add_location');
        const addSnmpCommunity = document.getElementById('add_snmp_community');
        const addUserId = document.getElementById('add_user_id');
        const editService = document.getElementById('edit_service_id');
        const editType = document.getElementById('edit_type');
        const editVendor = document.getElementById('edit_vendor_id');

        function toggleAddModal(open) {
            if (open) {
                addModal.classList.add('open');
            } else {
                addModal.classList.remove('open');
            }
        }

        function resetAddForm() {
            if (addName) addName.value = '';
            if (addUserId) addUserId.value = '';
            if (addService) addService.value = '';
            if (addVendor) addVendor.value = '';
            if (addHostname) addHostname.value = '';
            if (addType) addType.value = '';
            if (addIp) addIp.value = '';
            if (addLocation) addLocation.value = '';
            if (addSnmpCommunity) addSnmpCommunity.value = 'Anvica_NMS';
            filterVendors(addService, addVendor, false);
        }

        function fillAddFormFromRow(btn) {
            if (addName) addName.value = btn.getAttribute('data-name') || '';
            if (addUserId) addUserId.value = btn.getAttribute('data-user-id') || '';
            if (addService) addService.value = btn.getAttribute('data-service-id') || '';
            syncType(addService, addType);
            filterVendors(addService, addVendor, false);
            if (addVendor) addVendor.value = btn.getAttribute('data-vendor-id') || '';
            if (addHostname) addHostname.value = btn.getAttribute('data-hostname') || '';
            if (addType) addType.value = btn.getAttribute('data-type') || '';
            if (addIp) addIp.value = '';
            if (addLocation) addLocation.value = btn.getAttribute('data-location') || '';
            if (addSnmpCommunity) addSnmpCommunity.value = btn.getAttribute('data-snmp-community') || 'Anvica_NMS';
        }

        function syncType(select, typeInput) {
            typeInput.value = serviceTypeMap[select.value] || '';
        }

        function filterVendors(serviceSelect, vendorSelect, preserveValue) {
            if (!serviceSelect || !vendorSelect) return;

            const serviceId = serviceSelect.value;
            const currentValue = preserveValue ? vendorSelect.value : '';

            Array.from(vendorSelect.options).forEach((option, index) => {
                if (index === 0) {
                    option.hidden = false;
                    option.disabled = false;
                    return;
                }

                const matches = serviceId && option.dataset.service === serviceId;
                option.hidden = !matches;
                option.disabled = !matches;
            });

            const selectedOption = vendorSelect.querySelector('option[value="' + currentValue + '"]');
            if (selectedOption && !selectedOption.disabled) {
                vendorSelect.value = currentValue;
            } else {
                vendorSelect.value = '';
            }
        }

        function onServiceChange(serviceSelect, typeInput, vendorSelect) {
            syncType(serviceSelect, typeInput);
            filterVendors(serviceSelect, vendorSelect, false);
        }

        addService?.addEventListener('change', () => onServiceChange(addService, addType, addVendor));
        editService?.addEventListener('change', () => onServiceChange(editService, editType, editVendor));

        if (openAddBtn) {
            openAddBtn.addEventListener('click', () => {
                resetAddForm();
                toggleAddModal(true);
            });
        }
        if (closeAddBtn) closeAddBtn.addEventListener('click', () => toggleAddModal(false));
        if (cancelAddBtn) cancelAddBtn.addEventListener('click', () => toggleAddModal(false));

        document.querySelectorAll('.cloneDeviceBtn').forEach(btn => {
            btn.addEventListener('click', function () {
                fillAddFormFromRow(this);
                toggleAddModal(true);
                setTimeout(function () {
                    if (addIp) {
                        addIp.focus();
                    }
                }, 100);
            });
        });

        document.querySelectorAll('.editDeviceBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                document.getElementById('edit_name').value = this.getAttribute('data-name');
                const editUserId = document.getElementById('edit_user_id');
                if (editUserId) editUserId.value = this.getAttribute('data-user-id') || '';
                document.getElementById('edit_service_id').value = this.getAttribute('data-service-id') || '';
                filterVendors(editService, editVendor, false);
                document.getElementById('edit_vendor_id').value = this.getAttribute('data-vendor-id') || '';
                document.getElementById('edit_hostname').value = this.getAttribute('data-hostname') || '';
                document.getElementById('edit_type').value = this.getAttribute('data-type');
                document.getElementById('edit_ip').value = this.getAttribute('data-ip');
                document.getElementById('edit_location').value = this.getAttribute('data-location');
                document.getElementById('edit_snmp_community').value = this.getAttribute('data-snmp-community') || '';
                document.getElementById('edit_status').value = this.getAttribute('data-status');

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

        @if($errors->any() && old('_token'))
        toggleAddModal(true);
        if (addService && addService.value) {
            syncType(addService, addType);
            filterVendors(addService, addVendor, true);
        }
        @endif
    });
</script>
@endsection
