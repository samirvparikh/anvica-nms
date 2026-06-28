@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Application Master</h1>
        <p>Manage static dropdown values used across inventory, SLA, service desk and assets.</p>
    </div>
    <button type="button" class="btn-add" id="openAddMasterBtn">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Add Master Value
    </button>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;padding:0.85rem 1rem;border-radius:10px;background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;">
    {{ session('success') }}
</div>
@endif

<div class="master-layout">
    <aside class="master-type-panel card-table-container">
        <div class="table-toolbar">
            <h3 style="font-size:0.95rem;font-weight:700;margin:0;">Master Types</h3>
        </div>
        <div class="master-type-list">
            @foreach($typeSummaries as $summary)
            <a href="{{ route('master.application-masters.index', ['type' => $summary['type']]) }}"
               class="master-type-link {{ $selectedType === $summary['type'] ? 'is-active' : '' }}">
                <span>{{ $summary['label'] }}</span>
                <span class="master-type-count">{{ $summary['count'] }}</span>
            </a>
            @endforeach
        </div>
    </aside>

    <div class="master-content card-table-container">
        <div class="table-toolbar">
            <div>
                <h3 style="font-size:1rem;font-weight:700;margin:0 0 0.25rem;">{{ $types[$selectedType] ?? 'Master Values' }}</h3>
                <p style="margin:0;font-size:0.82rem;color:var(--text-muted);">{{ $items->count() }} values · Type key: <code>{{ $selectedType }}</code></p>
            </div>
            <div class="table-search" style="min-width:220px;">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" id="masterSearchInput" placeholder="Search values...">
            </div>
        </div>

        <div class="table-scroll">
            <table class="data-table" id="masterValuesTable">
                <thead>
                    <tr>
                        <th style="width:70px;">#</th>
                        <th>Display Name</th>
                        <th>Stored Value</th>
                        <th>Description</th>
                        <th>Sort</th>
                        <th>Status</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr class="master-row" data-search="{{ strtolower($item->name.' '.$item->value.' '.($item->description ?? '')) }}">
                        <td style="font-weight:700;">{{ $item->id }}</td>
                        <td style="font-weight:600;">{{ $item->name }}</td>
                        <td class="cell-mono">{{ $item->value }}</td>
                        <td style="color:var(--text-muted);">{{ $item->description ?: '—' }}</td>
                        <td>{{ $item->sort_order }}</td>
                        <td><span class="status-badge {{ strtolower($item->status) }}">{{ $item->status }}</span></td>
                        <td class="col-actions">
                            <div class="table-actions">
                                <button type="button"
                                        class="btn-action edit-btn editMasterBtn"
                                        title="Edit"
                                        data-id="{{ $item->id }}"
                                        data-type="{{ $item->type }}"
                                        data-name="{{ $item->name }}"
                                        data-value="{{ $item->value }}"
                                        data-description="{{ $item->description }}"
                                        data-sort-order="{{ $item->sort_order }}"
                                        data-status="{{ $item->status }}">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>
                                <form action="{{ route('master.application-masters.destroy', $item) }}" method="POST" onsubmit="return confirm('Delete this master value?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action delete-btn" title="Delete">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="no-sort-row">
                        <td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">
                            No values for this master type yet. Click <strong>Add Master Value</strong> to create one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-overlay" id="masterModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 id="masterModalTitle">Add Master Value</h3>
            <button type="button" class="modal-close" id="closeMasterModal">&times;</button>
        </div>
        <form id="masterForm" method="POST" action="{{ route('master.application-masters.store') }}">
            @csrf
            <div id="masterMethodField"></div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="master_type">Master Type</label>
                    <select name="type" id="master_type" class="form-control" required>
                        @foreach($types as $typeKey => $typeLabel)
                            <option value="{{ $typeKey }}" {{ $selectedType === $typeKey ? 'selected' : '' }}>{{ $typeLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="master_name">Display Name</label>
                    <input type="text" name="name" id="master_name" class="form-control" required maxlength="191" placeholder="e.g. Cisco">
                </div>
                <div class="form-group">
                    <label for="master_value">Stored Value <small style="color:var(--text-muted);">(optional — defaults to display name)</small></label>
                    <input type="text" name="value" id="master_value" class="form-control" maxlength="190" placeholder="e.g. Cisco">
                </div>
                <div class="form-group">
                    <label for="master_description">Description</label>
                    <textarea name="description" id="master_description" class="form-control" rows="2" maxlength="1000" placeholder="Optional notes"></textarea>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label for="master_sort_order">Sort Order</label>
                        <input type="number" name="sort_order" id="master_sort_order" class="form-control" min="0" max="9999" value="0">
                    </div>
                    <div class="form-group">
                        <label for="master_status">Status</label>
                        <select name="status" id="master_status" class="form-control" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="cancelMasterModal">Cancel</button>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('masterModal');
    const form = document.getElementById('masterForm');
    const title = document.getElementById('masterModalTitle');
    const methodField = document.getElementById('masterMethodField');
    const storeUrl = @json(route('master.application-masters.store'));
    const updateUrlTemplate = @json(route('master.application-masters.update', ['applicationMaster' => '__ID__']));

    function openModal() { modal.classList.add('open'); }
    function closeModal() { modal.classList.remove('open'); }

    function resetForm() {
        form.action = storeUrl;
        methodField.innerHTML = '';
        title.textContent = 'Add Master Value';
        document.getElementById('master_name').value = '';
        document.getElementById('master_value').value = '';
        document.getElementById('master_description').value = '';
        document.getElementById('master_sort_order').value = '0';
        document.getElementById('master_status').value = 'Active';
    }

    document.getElementById('openAddMasterBtn').addEventListener('click', function () {
        resetForm();
        openModal();
    });

    document.querySelectorAll('.editMasterBtn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            form.action = updateUrlTemplate.replace('__ID__', id);
            methodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';
            title.textContent = 'Edit Master Value';
            document.getElementById('master_type').value = this.getAttribute('data-type');
            document.getElementById('master_name').value = this.getAttribute('data-name');
            document.getElementById('master_value').value = this.getAttribute('data-value');
            document.getElementById('master_description').value = this.getAttribute('data-description') || '';
            document.getElementById('master_sort_order').value = this.getAttribute('data-sort-order') || '0';
            document.getElementById('master_status').value = this.getAttribute('data-status') || 'Active';
            openModal();
        });
    });

    document.getElementById('closeMasterModal').addEventListener('click', closeModal);
    document.getElementById('cancelMasterModal').addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });

    const searchInput = document.getElementById('masterSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.master-row').forEach(function (row) {
                row.style.display = !q || row.getAttribute('data-search').includes(q) ? '' : 'none';
            });
        });
    }
});
</script>
@endsection
