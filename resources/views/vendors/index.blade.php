@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Device Vendors</h1>
        <p>Manage vendors per device service type.</p>
    </div>
    <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
        <form method="GET" action="{{ route('vendors.index') }}" class="monitoring-user-filter" style="display:flex;gap:0.75rem;flex-wrap:wrap;">
            <select name="service_id" id="vendorFilterService" class="form-control" onchange="document.getElementById('vendorFilterVendor').value=''; this.form.submit();" style="min-width:180px;padding-left:1rem;">
                <option value="">All Services</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}" {{ (int) $serviceId === $service->id ? 'selected' : '' }}>
                        {{ $service->name }}
                    </option>
                @endforeach
            </select>
            <select name="vendor_id" id="vendorFilterVendor" class="form-control" onchange="this.form.submit()" style="min-width:180px;padding-left:1rem;">
                <option value="">All Vendors</option>
                @foreach($vendorOptions as $vendorOption)
                    <option value="{{ $vendorOption->id }}" {{ (int) $vendorId === $vendorOption->id ? 'selected' : '' }}>
                        {{ $vendorOption->name }}@if(! $serviceId) ({{ $vendorOption->service->name }})@endif
                    </option>
                @endforeach
            </select>
        </form>
        <button class="btn-add" id="openAddVendorBtn">+ Add Vendor</button>
    </div>
</div>

<div class="card-table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Vendor</th>
                <th>Service</th>
                <th>Slug</th>
                <th>Status</th>
                <th class="col-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vendors as $vendor)
            <tr>
                <td style="font-weight:700;">{{ $vendor->name }}</td>
                <td>{{ $vendor->service->name }}</td>
                <td>{{ $vendor->slug }}</td>
                <td><span class="status-badge {{ strtolower($vendor->status) }}">{{ $vendor->status }}</span></td>
                <td class="col-actions">
                    <div class="table-actions">
                        <button type="button" class="btn-action edit-btn editVendorBtn" title="Edit"
                            data-id="{{ $vendor->id }}"
                            data-service-id="{{ $vendor->service_id }}"
                            data-name="{{ $vendor->name }}"
                            data-logo="{{ $vendor->logo }}"
                            data-status="{{ $vendor->status }}">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </button>
                        <form action="{{ route('vendors.destroy', $vendor) }}" method="POST" onsubmit="return confirm('Delete vendor?');">
                            @csrf @method('DELETE')
                            @if($serviceId)
                                <input type="hidden" name="redirect_service_id" value="{{ $serviceId }}">
                            @endif
                            @if($vendorId)
                                <input type="hidden" name="redirect_vendor_id" value="{{ $vendorId }}">
                            @endif
                            <button type="submit" class="btn-action delete-btn" title="Delete">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    <line x1="10" y1="11" x2="10" y2="17"/>
                                    <line x1="14" y1="11" x2="14" y2="17"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted);">No vendors found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="modal-overlay" id="vendorModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 id="vendorModalTitle">Add Vendor</h3>
            <button class="modal-close" id="closeVendorModal">&times;</button>
        </div>
        <form action="{{ route('vendors.store') }}" method="POST" id="vendorForm">
            @csrf
            @if($serviceId)
                <input type="hidden" name="redirect_service_id" value="{{ $serviceId }}">
            @endif
            @if($vendorId)
                <input type="hidden" name="redirect_vendor_id" value="{{ $vendorId }}">
            @endif
            <div id="vendorMethodField"></div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Service</label>
                    <select name="service_id" id="vendor_service_id" class="form-control" required>
                        <option value="">Select Service</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Vendor Name</label>
                    <input type="text" name="name" id="vendor_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Logo URL</label>
                    <input type="text" name="logo" id="vendor_logo" class="form-control">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="vendor_status" class="form-control" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="cancelVendorModal">Cancel</button>
                <button type="submit" class="btn-primary" style="width:auto;padding:0.5rem 1.5rem;">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('vendorModal');
    const form = document.getElementById('vendorForm');
    const methodField = document.getElementById('vendorMethodField');

    function openModal(edit = false, data = {}) {
        document.getElementById('vendorModalTitle').textContent = edit ? 'Edit Vendor' : 'Add Vendor';
        methodField.innerHTML = edit ? '<input type="hidden" name="_method" value="PUT">' : '';
        form.action = edit ? `/vendors/${data.id}` : '{{ route('vendors.store') }}';
        document.getElementById('vendor_service_id').value = data.serviceId || '';
        document.getElementById('vendor_name').value = data.name || '';
        document.getElementById('vendor_logo').value = data.logo || '';
        document.getElementById('vendor_status').value = data.status || 'Active';
        modal.classList.add('open');
    }

    document.getElementById('openAddVendorBtn').onclick = () => openModal(false, {
        serviceId: '{{ $serviceId ?? '' }}',
    });
    document.getElementById('closeVendorModal').onclick = () => modal.classList.remove('open');
    document.getElementById('cancelVendorModal').onclick = () => modal.classList.remove('open');

    document.querySelectorAll('.editVendorBtn').forEach(btn => {
        btn.addEventListener('click', () => openModal(true, {
            id: btn.dataset.id,
            serviceId: btn.dataset.serviceId,
            name: btn.dataset.name,
            logo: btn.dataset.logo,
            status: btn.dataset.status,
        }));
    });
});
</script>
@endsection
