@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Device Vendors</h1>
        <p>Manage vendors per device service type.</p>
    </div>
</div>

<div class="list-toolbar">
    <form method="GET" action="{{ route('vendors.index') }}" class="list-toolbar-filters monitoring-user-filter">
        <select name="service_id" id="vendorFilterService" class="form-control" onchange="document.getElementById('vendorFilterVendor').value=''; this.form.submit();">
            <option value="">All Services</option>
            @foreach($services as $service)
                <option value="{{ $service->id }}" {{ (int) $serviceId === $service->id ? 'selected' : '' }}>
                    {{ $service->name }}
                </option>
            @endforeach
        </select>
        <select name="vendor_id" id="vendorFilterVendor" class="form-control" onchange="this.form.submit()">
            <option value="">All Vendors</option>
            @foreach($vendorOptions as $vendorOption)
                <option value="{{ $vendorOption->id }}" {{ (int) $vendorId === $vendorOption->id ? 'selected' : '' }}>
                    {{ $vendorOption->name }}@if(! $serviceId) ({{ $vendorOption->service->name }})@endif
                </option>
            @endforeach
        </select>
    </form>
    <div class="list-toolbar-actions">
        <button class="btn-add" id="openAddVendorBtn">+ Add Vendor</button>
    </div>
</div>

<div class="card-table-container">
    <div class="table-toolbar">
        <div class="table-search">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="vendorSearchInput" placeholder="Search vendors...">
        </div>
    </div>

    <table class="data-table" id="vendorsTable">
        <thead>
            <tr>
                <th>Vendor</th>
                <th>Service</th>
                <th>Slug</th>
                <th>Status</th>
                <th class="col-actions" data-no-sort="true" style="text-align: center; width: 90px;">Script</th>
                <th class="col-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vendors as $vendor)
            <tr class="vendor-row"
                data-name="{{ strtolower($vendor->name) }}"
                data-service="{{ strtolower($vendor->service->name) }}"
                data-slug="{{ strtolower($vendor->slug) }}"
                data-status="{{ strtolower($vendor->status) }}">
                <td style="font-weight:700;">{{ $vendor->name }}</td>
                <td>{{ $vendor->service->name }}</td>
                <td>{{ $vendor->slug }}</td>
                <td><span class="status-badge {{ strtolower($vendor->status) }}">{{ $vendor->status }}</span></td>
                <td style="text-align: center;">
                    <a href="{{ route('vendors.script.edit', $vendor) }}"
                       class="btn-action script-btn {{ $vendor->script ? 'script-btn-active' : '' }}"
                       title="{{ $vendor->script ? 'Edit script template' : 'Create script template' }}">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                    </a>
                </td>
                <td class="col-actions">
                    <div class="table-actions">
                        <button type="button" class="btn-action edit-btn editVendorBtn" title="Edit"
                            data-id="{{ $vendor->id }}"
                            data-update-url="{{ route('vendors.update', $vendor) }}"
                            data-service-id="{{ $vendor->service_id }}"
                            data-name="{{ $vendor->name }}"
                            data-logo="{{ $vendor->logo }}"
                            data-status="{{ $vendor->status }}"
                            data-codes="{{ $vendor->servicePointCodes->map(fn ($c) => ['service_point_id' => $c->service_point_id, 'name' => $c->name, 'code' => $c->code])->values()->toJson() }}">
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
            <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);">No vendors found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="modal-overlay" id="vendorModal">
    <div class="modal-card modal-card-wide">
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
                <div class="form-group">
                    <label>Point &amp; Code</label>
                    <div id="vendorCodesContainer" class="points-container vendor-codes-container">
                        <div class="point-row-header vendor-code-row-header">
                            <span>Point</span>
                            <span>Code</span>
                        </div>
                        <p id="vendorCodesEmpty" class="vendor-codes-empty" style="display:none;color:var(--text-muted);font-size:0.85rem;margin:0;">
                            Select a service to load service points.
                        </p>
                    </div>
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
    const searchInput = document.getElementById('vendorSearchInput');
    const tableRows = document.querySelectorAll('.vendor-row');

    if (searchInput) {
        searchInput.addEventListener('keyup', function (e) {
            const query = e.target.value.toLowerCase().trim();
            tableRows.forEach(function (row) {
                const haystack = [
                    row.getAttribute('data-name'),
                    row.getAttribute('data-service'),
                    row.getAttribute('data-slug'),
                    row.getAttribute('data-status'),
                ].join(' ');
                row.style.display = haystack.includes(query) ? '' : 'none';
            });
        });
    }

    const modal = document.getElementById('vendorModal');
    const form = document.getElementById('vendorForm');
    const methodField = document.getElementById('vendorMethodField');
    const codesContainer = document.getElementById('vendorCodesContainer');
    const codesEmpty = document.getElementById('vendorCodesEmpty');
    const serviceSelect = document.getElementById('vendor_service_id');
    const servicePointsByService = @json($servicePointsByService);
    let codeRowIndex = 0;

    function escapeAttr(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;');
    }

    function clearCodeRows() {
        codesContainer.querySelectorAll('.point-row').forEach(row => row.remove());
        codeRowIndex = 0;
    }

    function createCodeRow(point, code = '') {
        const row = document.createElement('div');
        row.className = 'point-row vendor-code-row';
        row.innerHTML = `
            <input type="text" class="form-control" value="${escapeAttr(point.name)}" disabled readonly tabindex="-1">
            <input type="hidden" name="codes[${codeRowIndex}][service_point_id]" value="${escapeAttr(point.id)}">
            <input type="hidden" name="codes[${codeRowIndex}][name]" value="${escapeAttr(point.name)}">
            <input type="text" name="codes[${codeRowIndex}][code]" class="form-control" placeholder="Code" value="${escapeAttr(code)}">
        `;
        codesContainer.appendChild(row);
        codeRowIndex++;
    }

    function populateServicePoints(serviceId, existingCodes = []) {
        clearCodeRows();

        const points = servicePointsByService[String(serviceId)] || [];
        const codeMap = {};
        existingCodes.forEach(item => {
            const key = item.service_point_id || item.name;
            if (key) {
                codeMap[key] = item.code || '';
            }
        });

        if (! serviceId || points.length === 0) {
            codesEmpty.style.display = 'block';
            codesEmpty.textContent = serviceId
                ? 'No service points found for this service.'
                : 'Select a service to load service points.';
            return;
        }

        codesEmpty.style.display = 'none';
        points.forEach(point => createCodeRow(point, codeMap[point.id] || codeMap[point.name] || ''));
    }

    serviceSelect.addEventListener('change', function () {
        populateServicePoints(this.value);
    });

    function openModal(edit = false, data = {}) {
        document.getElementById('vendorModalTitle').textContent = edit ? 'Edit Vendor' : 'Add Vendor';
        methodField.innerHTML = edit ? '<input type="hidden" name="_method" value="PUT">' : '';
        form.action = edit ? data.updateUrl : '{{ route('vendors.store') }}';
        serviceSelect.value = data.serviceId || '';
        document.getElementById('vendor_name').value = data.name || '';
        document.getElementById('vendor_logo').value = data.logo || '';
        document.getElementById('vendor_status').value = data.status || 'Active';

        let codes = [];
        if (data.codes) {
            try {
                codes = typeof data.codes === 'string' ? JSON.parse(data.codes) : data.codes;
            } catch (e) {
                codes = [];
            }
        }

        populateServicePoints(serviceSelect.value, codes);
        modal.classList.add('open');
    }

    document.getElementById('openAddVendorBtn').onclick = () => openModal(false, {
        serviceId: '{{ $serviceId ?? '' }}',
    });
    document.getElementById('closeVendorModal').onclick = () => modal.classList.remove('open');
    document.getElementById('cancelVendorModal').onclick = () => modal.classList.remove('open');

    document.querySelectorAll('.editVendorBtn').forEach(btn => {
        btn.addEventListener('click', () => openModal(true, {
            updateUrl: btn.dataset.updateUrl,
            serviceId: btn.dataset.serviceId,
            name: btn.dataset.name,
            logo: btn.dataset.logo,
            status: btn.dataset.status,
            codes: btn.dataset.codes,
        }));
    });
});
</script>
<style>
.vendor-code-row-header,
.vendor-code-row {
    grid-template-columns: 1fr 1fr;
}
.vendor-codes-container input[disabled] {
    background-color: #f1f5f9;
    color: var(--text-dark);
    cursor: not-allowed;
}
</style>
@endsection
