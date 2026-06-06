@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Services</h1>
        <p>Manage monitoring services with points and collection methods.</p>
    </div>
    <button class="btn-add" id="openAddServiceModalBtn">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Add Service
    </button>
</div>

<div class="card-table-container">
    <div class="table-toolbar">
        <div class="table-search">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="serviceSearchInput" placeholder="Search services...">
        </div>
    </div>

    <table class="data-table" id="servicesTable">
        <thead>
            <tr>
                <th>Service Name</th>
                <th>Status</th>
                <th>Points & Methods</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($services as $service)
            <tr class="service-row" data-name="{{ strtolower($service->name) }}">
                <td style="font-weight: 700;">{{ $service->name }}</td>
                <td>
                    <span class="status-badge {{ strtolower($service->status) }}">
                        {{ $service->status }}
                    </span>
                </td>
                <td>
                    @foreach($service->points as $point)
                        <span class="service-point-tag">{{ $point->point }} <small>({{ $point->method }})</small></span>
                    @endforeach
                </td>
                <td style="text-align: right;">
                    <button class="btn-action edit-btn editServiceBtn"
                            data-id="{{ $service->id }}"
                            data-name="{{ $service->name }}"
                            data-status="{{ $service->status }}"
                            data-points='@json($service->points->map(fn ($p) => ["point" => $p->point, "method" => $p->method]))'>
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline-block; vertical-align:middle;">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                    </button>
                    <form action="{{ route('services.destroy', $service->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this service?');">
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
                <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem 0;">No services found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Add Service Modal -->
<div class="modal-overlay" id="addServiceModal">
    <div class="modal-card modal-card-wide">
        <div class="modal-header">
            <h3>Add Service</h3>
            <button class="modal-close" id="closeAddServiceModalBtn">&times;</button>
        </div>
        <form action="{{ route('services.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="add_service_name">Service Name</label>
                    <input type="text" id="add_service_name" name="name" class="form-control" placeholder="e.g. Network Monitoring" required>
                </div>
                <div class="form-group">
                    <label for="add_service_status">Status</label>
                    <select id="add_service_status" name="status" class="form-control" required>
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Point & Method <span style="color: var(--text-muted); font-weight: 400;">(add multiple rows)</span></label>
                    <div id="addPointsContainer" class="points-container">
                        <div class="point-row-header">
                            <span>Point</span>
                            <span>Method</span>
                            <span></span>
                        </div>
                        <div class="point-row">
                            <input type="text" name="points[0][point]" class="form-control" placeholder="Point" required>
                            <input type="text" name="points[0][method]" class="form-control" placeholder="Method" required>
                            <button type="button" class="btn-remove-row" disabled>&times;</button>
                        </div>
                    </div>
                    <button type="button" class="btn-add-row" id="addPointRowBtn">+ Add Point & Method</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="cancelAddServiceModalBtn">Cancel</button>
                <button type="submit" class="btn-primary" style="width:auto; padding: 0.5rem 1.5rem;">Save Service</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Service Modal -->
<div class="modal-overlay" id="editServiceModal">
    <div class="modal-card modal-card-wide">
        <div class="modal-header">
            <h3>Edit Service</h3>
            <button class="modal-close" id="closeEditServiceModalBtn">&times;</button>
        </div>
        <form action="" method="POST" id="editServiceForm">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_service_name">Service Name</label>
                    <input type="text" id="edit_service_name" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_service_status">Status</label>
                    <select id="edit_service_status" name="status" class="form-control" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Point & Method <span style="color: var(--text-muted); font-weight: 400;">(add multiple rows)</span></label>
                    <div id="editPointsContainer" class="points-container">
                        <div class="point-row-header">
                            <span>Point</span>
                            <span>Method</span>
                            <span></span>
                        </div>
                    </div>
                    <button type="button" class="btn-add-row" id="editAddPointRowBtn">+ Add Point & Method</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="cancelEditServiceModalBtn">Cancel</button>
                <button type="submit" class="btn-primary" style="width:auto; padding: 0.5rem 1.5rem;">Update Service</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('serviceSearchInput');
    const tableRows = document.querySelectorAll('.service-row');

    searchInput.addEventListener('keyup', function (e) {
        const query = e.target.value.toLowerCase().trim();
        tableRows.forEach(row => {
            row.style.display = row.getAttribute('data-name').includes(query) ? '' : 'none';
        });
    });

    function escapeAttr(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;');
    }

    function createPointRow(container, index, point = '', method = '') {
        const row = document.createElement('div');
        row.className = 'point-row';
        row.innerHTML = `
            <input type="text" name="points[${index}][point]" class="form-control" placeholder="Point" value="${escapeAttr(point)}" required>
            <input type="text" name="points[${index}][method]" class="form-control" placeholder="Method" value="${escapeAttr(method)}" required>
            <button type="button" class="btn-remove-row">&times;</button>
        `;
        container.appendChild(row);
        bindRemoveButtons(container);
    }

    function bindRemoveButtons(container) {
        const rows = container.querySelectorAll('.point-row');
        rows.forEach((row, idx) => {
            const removeBtn = row.querySelector('.btn-remove-row');
            removeBtn.disabled = rows.length <= 1;
            removeBtn.onclick = () => {
                if (rows.length > 1) {
                    row.remove();
                    reindexPoints(container);
                    bindRemoveButtons(container);
                }
            };
        });
    }

    function reindexPoints(container) {
        container.querySelectorAll('.point-row').forEach((row, idx) => {
            const inputs = row.querySelectorAll('input');
            inputs[0].name = `points[${idx}][point]`;
            inputs[1].name = `points[${idx}][method]`;
        });
    }

    const addPointsContainer = document.getElementById('addPointsContainer');
    let addPointIndex = 1;

    document.getElementById('addPointRowBtn').addEventListener('click', () => {
        createPointRow(addPointsContainer, addPointIndex++);
    });
    bindRemoveButtons(addPointsContainer);

    const addModal = document.getElementById('addServiceModal');
    document.getElementById('openAddServiceModalBtn').addEventListener('click', () => addModal.classList.add('open'));
    document.getElementById('closeAddServiceModalBtn').addEventListener('click', () => addModal.classList.remove('open'));
    document.getElementById('cancelAddServiceModalBtn').addEventListener('click', () => addModal.classList.remove('open'));

    const editModal = document.getElementById('editServiceModal');
    const editForm = document.getElementById('editServiceForm');
    const editPointsContainer = document.getElementById('editPointsContainer');
    let editPointIndex = 0;

    document.getElementById('editAddPointRowBtn').addEventListener('click', () => {
        createPointRow(editPointsContainer, editPointIndex++);
    });

    document.querySelectorAll('.editServiceBtn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('edit_service_name').value = this.getAttribute('data-name');
            document.getElementById('edit_service_status').value = this.getAttribute('data-status') || 'Active';
            editPointsContainer.querySelectorAll('.point-row').forEach(row => row.remove());
            editPointIndex = 0;

            const points = JSON.parse(this.getAttribute('data-points') || '[]');
            if (points.length === 0) {
                createPointRow(editPointsContainer, editPointIndex++);
            } else {
                points.forEach(p => {
                    createPointRow(editPointsContainer, editPointIndex++, p.point, p.method);
                });
            }

            editForm.action = `/services/${this.getAttribute('data-id')}`;
            editModal.classList.add('open');
        });
    });

    document.getElementById('closeEditServiceModalBtn').addEventListener('click', () => editModal.classList.remove('open'));
    document.getElementById('cancelEditServiceModalBtn').addEventListener('click', () => editModal.classList.remove('open'));
});
</script>
@endsection
