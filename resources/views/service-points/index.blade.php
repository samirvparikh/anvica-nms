@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Service Points</h1>
        <p>Monitoring metrics and collection methods per service.</p>
    </div>
    <div style="display:flex;align-items:center;gap:0.75rem;">
        <form method="GET" action="{{ route('service-points.index') }}" class="monitoring-user-filter">
            <select name="service_id" class="form-control" onchange="this.form.submit()" style="min-width:200px;padding-left:1rem;">
                <option value="">All Services</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}" {{ (int) $serviceId === $service->id ? 'selected' : '' }}>
                        {{ $service->name }}
                    </option>
                @endforeach
            </select>
        </form>
        <button class="btn-add" id="openAddPointBtn">+ Add Service Point</button>
    </div>
</div>

<div class="card-table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Service</th>
                <th>Method</th>
                <th>Unit</th>
                <th>Warning</th>
                <th>Critical</th>
                <th>Status</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($servicePoints as $point)
            <tr>
                <td style="font-weight:700;">{{ $point->name }}</td>
                <td>{{ $point->service->name }}</td>
                <td>{{ $point->method }}</td>
                <td>{{ $point->unit ?? '—' }}</td>
                <td>{{ $point->warning_threshold ?? '—' }}</td>
                <td>{{ $point->critical_threshold ?? '—' }}</td>
                <td><span class="status-badge {{ strtolower($point->status) }}">{{ $point->status }}</span></td>
                <td style="text-align:right;">
                    <button class="btn-action edit-btn editPointBtn"
                        data-id="{{ $point->id }}"
                        data-service-id="{{ $point->service_id }}"
                        data-name="{{ $point->name }}"
                        data-method="{{ $point->method }}"
                        data-unit="{{ $point->unit }}"
                        data-warning="{{ $point->warning_threshold }}"
                        data-critical="{{ $point->critical_threshold }}"
                        data-status="{{ $point->status }}">Edit</button>
                    <form action="{{ route('service-points.destroy', $point) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete service point?');">
                        @csrf @method('DELETE')
                        @if($serviceId)
                            <input type="hidden" name="redirect_service_id" value="{{ $serviceId }}">
                        @endif
                        <button type="submit" class="btn-action delete-btn">Delete</button>
                    </form>
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
    const modal = document.getElementById('pointModal');
    const form = document.getElementById('pointForm');
    const methodField = document.getElementById('pointMethodField');

    function openModal(edit = false, data = {}) {
        document.getElementById('pointModalTitle').textContent = edit ? 'Edit Service Point' : 'Add Service Point';
        methodField.innerHTML = edit ? '<input type="hidden" name="_method" value="PUT">' : '';
        form.action = edit ? `/service-points/${data.id}` : '{{ route('service-points.store') }}';
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
            id: btn.dataset.id,
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
