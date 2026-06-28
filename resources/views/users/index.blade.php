@extends('layouts.app')

@section('content')
@php
    $today = now()->format('Y-m-d');
    $oneYearLater = now()->addYear()->format('Y-m-d');
    $editUserId = session('edit_user_id');
@endphp

<div class="page-header">
    <div class="page-title">
        <h1>Users</h1>
        <p>Manage admin and user accounts, device limits, and service access.</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="{{ route('users.create') }}" class="btn-add" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 0.25rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add User
        </a>
        @if(auth()->user()->isSuperAdmin())
        <button class="btn-add" id="openAddAdminModalBtn" style="background-color: var(--text-dark); color: white; display: inline-flex; align-items: center; justify-content: center; gap: 0.25rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add Admin
        </button>
        @endif
    </div>
</div>

<div class="card-table-container">
    <div class="table-toolbar">
        <div class="table-search">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="userSearchInput" placeholder="Search users...">
        </div>
    </div>

    <table class="data-table" id="usersTable">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Mobile</th>
                <th>Device Limit</th>
                <th>Start Date</th>
                <th>Expire Date</th>
                <th>Services</th>
                <th class="col-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr class="user-row"
                data-name="{{ strtolower($user->name) }}"
                data-email="{{ strtolower($user->email) }}"
                data-mobile="{{ strtolower($user->mobile ?? '') }}">
                <td style="font-weight: 700;">{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <span class="status-badge {{ $user->isAdmin() ? 'active' : 'inactive' }}">
                        {{ $user->roleLabel() }}
                    </span>
                </td>
                <td>
                    <span class="status-badge {{ strtolower($user->status ?? 'Active') }}">
                        {{ $user->status ?? 'Active' }}
                    </span>
                </td>
                <td>{{ $user->mobile ?? '—' }}</td>
                <td>
                    @if($user->isAdmin())
                        —
                    @else
                        {{ $user->deviceCount() }} / {{ $user->device_limit }}
                    @endif
                </td>
                <td>{{ $user->isAdmin() ? '—' : ($user->start_date?->format('d M Y') ?? '—') }}</td>
                <td>
                    @if($user->isAdmin())
                        —
                    @elseif($user->expire_date && $user->expire_date->isPast())
                        <span class="status-badge down">{{ $user->expire_date->format('d M Y') }}</span>
                    @else
                        {{ $user->expire_date?->format('d M Y') ?? '—' }}
                    @endif
                </td>
                <td>
                    @if($user->isAdmin())
                        —
                    @elseif($user->services->isEmpty())
                        <span style="color: var(--text-muted);">None</span>
                    @else
                        {{ $user->services->pluck('name')->join(', ') }}
                    @endif
                </td>
                <td style="text-align: right;">
                    @if($user->isAdmin())
                    <button class="btn-action edit-btn editUserBtn"
                            data-id="{{ $user->id }}"
                            data-update-url="{{ route('users.update', $user) }}"
                            data-name="{{ $user->name }}"
                            data-email="{{ $user->email }}"
                            data-mobile="{{ $user->mobile }}"
                            data-role-id="{{ $user->role_id }}"
                            data-assignable-roles="{{ json_encode(\App\Models\User::assignableRolesForEditor(auth()->user(), $user)->map(fn ($role) => ['id' => $role->id, 'name' => $role->name])->values()) }}"
                            data-status="{{ $user->status ?? 'Active' }}"
                            data-device-limit=""
                            data-start-date=""
                            data-expire-date=""
                            data-services="">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline-block; vertical-align:middle;">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                    </button>
                    @else
                    <a href="{{ route('users.edit', $user) }}" class="btn-action edit-btn" style="display: inline-flex; align-items: center; justify-content: center; vertical-align: middle;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                    </a>
                    @endif
                    @if($user->id !== auth()->id())
                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this account?');">
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
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 2rem 0;">No users found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Add Admin Modal -->
<div class="modal-overlay" id="addAdminModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Add Admin Account</h3>
            <button class="modal-close" id="closeAddAdminModalBtn">&times;</button>
        </div>
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <input type="hidden" name="status" value="Active">
                <div class="form-group">
                    <label for="add_admin_role">Role</label>
                    <select id="add_admin_role" name="role_id" class="form-control" required>
                        @foreach($assignableRoles as $role)
                            @if($role->slug === \App\Models\Role::SLUG_ADMIN)
                                <option value="{{ $role->id }}" {{ (string) old('role_id', $role->id) === (string) $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="add_admin_name">Name</label>
                    <input type="text" id="add_admin_name" name="name" class="form-control" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_admin_email">Email</label>
                        <input type="email" id="add_admin_email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="add_admin_mobile">Mobile</label>
                        <input type="text" id="add_admin_mobile" name="mobile" class="form-control">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_admin_password">Password</label>
                        <input type="password" id="add_admin_password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="add_admin_password_confirmation">Confirm Password</label>
                        <input type="password" id="add_admin_password_confirmation" name="password_confirmation" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="cancelAddAdminModalBtn">Cancel</button>
                <button type="submit" class="btn-primary" style="width:auto; padding: 0.5rem 1.5rem;">Create Admin</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal-overlay" id="editUserModal">
    <div class="modal-card modal-card-wide">
        <div class="modal-header">
            <h3>Edit Account</h3>
            <button class="modal-close" id="closeEditUserModalBtn">&times;</button>
        </div>
        <form action="{{ $editUserId ? route('users.update', $editUserId) : '' }}" method="POST" id="editUserForm">
            @csrf
            @method('PUT')
            <div class="modal-body">
                @if($errors->any() && $editUserId)
                <div class="form-alert form-alert-error" style="margin-bottom: 1rem;">
                    {{ $errors->first() }}
                </div>
                @endif
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_user_role">Role</label>
                        <select id="edit_user_role" name="role_id" class="form-control role-select" required>
                            @foreach($assignableRoles as $role)
                                <option value="{{ $role->id }}" {{ (string) old('role_id') === (string) $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group user-device-limit-field" id="editDeviceLimitField">
                        <label for="edit_user_device_limit">Device Limit</label>
                        <input type="number" id="edit_user_device_limit" name="device_limit" class="form-control user-only-input" min="1" value="{{ old('device_limit') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_user_name">Name</label>
                    <input type="text" id="edit_user_name" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_user_email">Email</label>
                        <input type="email" id="edit_user_email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_user_mobile">Mobile</label>
                        <input type="text" id="edit_user_mobile" name="mobile" class="form-control" value="{{ old('mobile') }}">
                    </div>
                </div>
                <div class="user-only-fields" id="editUserOnlyFields">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_user_start_date">Start Date</label>
                            <input type="date" id="edit_user_start_date" name="start_date" class="form-control user-only-input" value="{{ old('start_date') }}">
                        </div>
                        <div class="form-group">
                            <label for="edit_user_expire_date">Expire Date</label>
                            <input type="date" id="edit_user_expire_date" name="expire_date" class="form-control user-only-input" value="{{ old('expire_date') }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Services</label>
                        <div class="checkbox-group" id="editUserServices">
                            @foreach($services as $service)
                                <label class="checkbox-label">
                                    <input type="checkbox" name="services[]" value="{{ $service->id }}"
                                           class="edit-service-checkbox user-only-input"
                                           {{ in_array($service->id, old('services', [])) ? 'checked' : '' }}>
                                    {{ $service->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_user_status">Status</label>
                    <select id="edit_user_status" name="status" class="form-control" required>
                        <option value="Active" {{ old('status', 'Active') === 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ old('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_user_password">New Password <span style="color: var(--text-muted); font-weight: 400;">(leave blank to keep)</span></label>
                        <input type="password" id="edit_user_password" name="password" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_user_password_confirmation">Confirm Password</label>
                        <input type="password" id="edit_user_password_confirmation" name="password_confirmation" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="cancelEditUserModalBtn">Cancel</button>
                <button type="submit" class="btn-primary" style="width:auto; padding: 0.5rem 1.5rem;">Update Account</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('userSearchInput');
    const tableRows = document.querySelectorAll('.user-row');

    searchInput.addEventListener('keyup', function (e) {
        const query = e.target.value.toLowerCase().trim();
        tableRows.forEach(row => {
            const name = row.getAttribute('data-name');
            const email = row.getAttribute('data-email');
            const mobile = row.getAttribute('data-mobile');
            row.style.display = (name.includes(query) || email.includes(query) || mobile.includes(query)) ? '' : 'none';
        });
    });

    const staffRoleIds = @json($staffRoleIds->map(fn ($id) => (string) $id));

    function toggleUserFields(roleSelect, fieldsContainer, deviceLimitField) {
        const isStaff = staffRoleIds.includes(String(roleSelect.value));
        fieldsContainer.style.display = isStaff ? '' : 'none';

        if (deviceLimitField) {
            deviceLimitField.style.display = isStaff ? '' : 'none';
        }

        fieldsContainer.querySelectorAll('.user-only-input').forEach(input => {
            if (input.type === 'checkbox') {
                input.disabled = !isStaff;
                if (!isStaff) {
                    input.checked = false;
                }
            } else {
                input.required = isStaff && input.name !== 'services[]';
                input.disabled = !isStaff;
            }
        });

        const deviceLimitInput = deviceLimitField?.querySelector('.user-only-input');
        if (deviceLimitInput) {
            deviceLimitInput.required = isStaff;
            deviceLimitInput.disabled = !isStaff;
        }
    }

    const editRoleSelect = document.getElementById('edit_user_role');
    const editUserOnlyFields = document.getElementById('editUserOnlyFields');
    const editDeviceLimitField = document.getElementById('editDeviceLimitField');
    editRoleSelect.addEventListener('change', () => toggleUserFields(editRoleSelect, editUserOnlyFields, editDeviceLimitField));

    const addAdminModal = document.getElementById('addAdminModal');
    if (addAdminModal) {
        document.getElementById('openAddAdminModalBtn')?.addEventListener('click', () => addAdminModal.classList.add('open'));
        document.getElementById('closeAddAdminModalBtn')?.addEventListener('click', () => addAdminModal.classList.remove('open'));
        document.getElementById('cancelAddAdminModalBtn')?.addEventListener('click', () => addAdminModal.classList.remove('open'));
    }

    const editModal = document.getElementById('editUserModal');
    const editForm = document.getElementById('editUserForm');

    document.querySelectorAll('.editUserBtn').forEach(btn => {
        btn.addEventListener('click', function () {
            const allowedRoles = JSON.parse(this.getAttribute('data-assignable-roles') || '[]');
            editRoleSelect.innerHTML = '';
            allowedRoles.forEach(role => {
                const option = document.createElement('option');
                option.value = role.id;
                option.textContent = role.name;
                editRoleSelect.appendChild(option);
            });

            document.getElementById('edit_user_name').value = this.getAttribute('data-name');
            document.getElementById('edit_user_email').value = this.getAttribute('data-email');
            document.getElementById('edit_user_mobile').value = this.getAttribute('data-mobile') || '';
            editRoleSelect.value = this.getAttribute('data-role-id') || '';
            document.getElementById('edit_user_status').value = this.getAttribute('data-status') || 'Active';
            document.getElementById('edit_user_device_limit').value = this.getAttribute('data-device-limit') || '10';
            document.getElementById('edit_user_start_date').value = this.getAttribute('data-start-date') || '';
            document.getElementById('edit_user_expire_date').value = this.getAttribute('data-expire-date') || '';
            document.getElementById('edit_user_password').value = '';
            document.getElementById('edit_user_password_confirmation').value = '';

            const serviceIds = (this.getAttribute('data-services') || '').split(',').filter(Boolean);
            document.querySelectorAll('.edit-service-checkbox').forEach(cb => {
                cb.checked = serviceIds.includes(cb.value);
            });

            toggleUserFields(editRoleSelect, editUserOnlyFields, editDeviceLimitField);
            editForm.action = this.getAttribute('data-update-url');
            editModal.classList.add('open');
        });
    });

    document.getElementById('closeEditUserModalBtn').addEventListener('click', () => editModal.classList.remove('open'));
    document.getElementById('cancelEditUserModalBtn').addEventListener('click', () => editModal.classList.remove('open'));

    @if($editUserId)
    toggleUserFields(editRoleSelect, editUserOnlyFields, editDeviceLimitField);
    editModal.classList.add('open');
    @endif
});
</script>
@endsection
