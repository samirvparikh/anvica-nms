@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Service Points</h1>
        <p>Monitoring metrics and collection methods per service.</p>
    </div>
</div>

<div class="list-toolbar">
    <form method="GET" action="{{ route('service-points.index') }}" class="list-toolbar-filters monitoring-user-filter">
        <select name="service_id" id="servicePointFilterService" class="form-control" onchange="this.form.submit()">
            <option value="">All Services</option>
            @foreach($services as $service)
                <option value="{{ $service->id }}" {{ (int) $serviceId === $service->id ? 'selected' : '' }}>
                    {{ $service->name }}
                </option>
            @endforeach
        </select>
    </form>
    <div class="list-toolbar-actions">
        <button class="btn-add" id="openAddPointBtn">+ Add Service Point</button>
    </div>
</div>

<div class="card-table-container">
    <div class="table-toolbar">
        <div class="table-search">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="servicePointSearchInput" placeholder="Search service points...">
        </div>
    </div>

    <table class="data-table" id="servicePointsTable">
        <thead>
            <tr>
                <th>Name</th>
                <th>Service</th>
                <th>Method</th>
                <th>Unit</th>
                <th>Warning</th>
                <th>Critical</th>
                <th>Status</th>
                <th class="col-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($servicePoints as $point)
            <tr class="service-point-row"
                data-name="{{ strtolower($point->name) }}"
                data-service="{{ strtolower($point->service->name) }}"
                data-method="{{ strtolower($point->method) }}"
                data-status="{{ strtolower($point->status) }}">
                <td style="font-weight:700;">{{ $point->name }}</td>
                <td>{{ $point->service->name }}</td>
                <td>{{ $point->method }}</td>
                <td>{{ $point->unit ?? '—' }}</td>
                <td>{{ $point->warning_threshold ?? '—' }}</td>
                <td>{{ $point->critical_threshold ?? '—' }}</td>
                <td><span class="status-badge {{ strtolower($point->status) }}">{{ $point->status }}</span></td>
                <td class="col-actions">
                    <div class="table-actions">
                        <button type="button" class="btn-action edit-btn editPointBtn" title="Edit"
                            data-id="{{ $point->id }}"
                            data-update-url="{{ route('service-points.update', $point) }}"
                            data-service-id="{{ $point->service_id }}"
                            data-name="{{ $point->name }}"
                            data-method="{{ $point->method }}"
                            data-unit="{{ $point->unit }}"
                            data-warning="{{ $point->warning_threshold }}"
                            data-critical="{{ $point->critical_threshold }}"
                            data-status="{{ $point->status }}">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </button>
                        <form action="{{ route('service-points.destroy', $point) }}" method="POST" onsubmit="return confirm('Delete service point?');">
                            @csrf @method('DELETE')
                            @if($serviceId)
                                <input type="hidden" name="redirect_service_id" value="{{ $serviceId }}">
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
            <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted);">No service points found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="modal-overlay" id="pointModal">
    <div class="modal-card modal-card-wide">
        <div class="modal-header">
            <h3 id="pointModalTitle">Add Service Point</h3>
            <button class="modal-close" id="closePointModal">&times;</button>
        </div>
        <form action="{{ route('service-points.store') }}" method="POST" id="pointForm">
            @csrf
            @if($serviceId)
                <input type="hidden" name="redirect_service_id" value="{{ $serviceId }}">
            @endif
            <div id="pointMethodField"></div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Service</label>
                    <select name="service_id" id="point_service_id" class="form-control" required>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" id="point_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Method</label>
                    <input type="text" name="method" id="point_method" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Unit</label>
                    <input type="text" name="unit" id="point_unit" class="form-control">
                </div>
                <div class="form-group">
                    <label>Warning Threshold</label>
                    <input type="number" step="0.01" name="warning_threshold" id="point_warning" class="form-control">
                </div>
                <div class="form-group">
                    <label>Critical Threshold</label>
                    <input type="number" step="0.01" name="critical_threshold" id="point_critical" class="form-control">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="point_status" class="form-control" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="cancelPointModal">Cancel</button>
                <button type="submit" class="btn-primary" style="width:auto;padding:0.5rem 1.5rem;">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('servicePointSearchInput');
    const tableRows = document.querySelectorAll('.service-point-row');

    if (searchInput) {
        searchInput.addEventListener('keyup', function (e) {
            const query = e.target.value.toLowerCase().trim();
            tableRows.forEach(function (row) {
                const haystack = [
                    row.getAttribute('data-name'),
                    row.getAttribute('data-service'),
                    row.getAttribute('data-method'),
                    row.getAttribute('data-status'),
                ].join(' ');
                row.style.display = haystack.includes(query) ? '' : 'none';
            });
        });
    }

    const modal = document.getElementById('pointModal');
    const form = document.getElementById('pointForm');
    const methodField = document.getElementById('pointMethodField');

    function openModal(edit = false, data = {}) {
        document.getElementById('pointModalTitle').textContent = edit ? 'Edit Service Point' : 'Add Service Point';
        methodField.innerHTML = edit ? '<input type="hidden" name="_method" value="PUT">' : '';
        form.action = edit ? data.updateUrl : '{{ route('service-points.store') }}';
        document.getElementById('point_service_id').value = data.serviceId || '';
        document.getElementById('point_name').value = data.name || '';
        document.getElementById('point_method').value = data.method || '';
        document.getElementById('point_unit').value = data.unit || '';
        document.getElementById('point_warning').value = data.warning || '';
        document.getElementById('point_critical').value = data.critical || '';
        document.getElementById('point_status').value = data.status || 'Active';
        modal.classList.add('open');
    }

    document.getElementById('openAddPointBtn').onclick = () => openModal(false, {
        serviceId: '{{ $serviceId ?? '' }}',
    });
    document.getElementById('closePointModal').onclick = () => modal.classList.remove('open');
    document.getElementById('cancelPointModal').onclick = () => modal.classList.remove('open');

    document.querySelectorAll('.editPointBtn').forEach(btn => {
        btn.addEventListener('click', () => openModal(true, {
            updateUrl: btn.dataset.updateUrl,
            serviceId: btn.dataset.serviceId,
            name: btn.dataset.name,
            method: btn.dataset.method,
            unit: btn.dataset.unit,
            warning: btn.dataset.warning,
            critical: btn.dataset.critical,
            status: btn.dataset.status,
        }));
    });
});
</script>
@endsection
