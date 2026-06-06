@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Device Vendors</h1>
        <p>Manage vendors per device service type.</p>
    </div>
    <button class="btn-add" id="openAddVendorBtn">+ Add Vendor</button>
</div>

<div class="card-table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Vendor</th>
                <th>Service</th>
                <th>Slug</th>
                <th>Status</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vendors as $vendor)
            <tr>
                <td style="font-weight:700;">{{ $vendor->name }}</td>
                <td>{{ $vendor->service->name }}</td>
                <td>{{ $vendor->slug }}</td>
                <td><span class="status-badge {{ strtolower($vendor->status) }}">{{ $vendor->status }}</span></td>
                <td style="text-align:right;">
                    <button class="btn-action edit-btn editVendorBtn"
                        data-id="{{ $vendor->id }}"
                        data-service-id="{{ $vendor->service_id }}"
                        data-name="{{ $vendor->name }}"
                        data-logo="{{ $vendor->logo }}"
                        data-status="{{ $vendor->status }}">Edit</button>
                    <form action="{{ route('vendors.destroy', $vendor) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete vendor?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-action delete-btn">Delete</button>
                    </form>
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

    document.getElementById('openAddVendorBtn').onclick = () => openModal(false);
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
