@extends('layouts.app')

@section('content')
@php
    $today = now()->format('Y-m-d');
    $oneYearLater = now()->addYear()->format('Y-m-d');
@endphp

<div class="page-header">
    <div class="page-title">
        <h1>Users</h1>
        <p>Manage application users, device limits, and service access.</p>
    </div>
    <button class="btn-add" id="openAddUserModalBtn">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Add User
    </button>
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
                <th>Mobile</th>
                <th>Device Limit</th>
                <th>Start Date</th>
                <th>Expire Date</th>
                <th>Services</th>
                <th style="text-align: right;">Actions</th>
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
                <td>{{ $user->mobile ?? '—' }}</td>
                <td>{{ $user->deviceCount() }} / {{ $user->device_limit }}</td>
                <td>{{ $user->start_date?->format('d M Y') ?? '—' }}</td>
                <td>
                    @if($user->expire_date && $user->expire_date->isPast())
                        <span class="status-badge down">{{ $user->expire_date->format('d M Y') }}</span>
                    @else
                        {{ $user->expire_date?->format('d M Y') ?? '—' }}
                    @endif
                </td>
                <td>
                    @if($user->services->isEmpty())
                        <span style="color: var(--text-muted);">None</span>
                    @else
                        {{ $user->services->pluck('name')->join(', ') }}
                    @endif
                </td>
                <td style="text-align: right;">
                    <button class="btn-action edit-btn editUserBtn"
                            data-id="{{ $user->id }}"
                            data-name="{{ $user->name }}"
                            data-email="{{ $user->email }}"
                            data-mobile="{{ $user->mobile }}"
                            data-device-limit="{{ $user->device_limit }}"
                            data-start-date="{{ $user->start_date?->format('Y-m-d') }}"
                            data-expire-date="{{ $user->expire_date?->format('Y-m-d') }}"
                            data-services="{{ $user->services->pluck('id')->join(',') }}">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline-block; vertical-align:middle;">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                    </button>
                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this user?');">
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
                <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2rem 0;">No users found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Add User Modal -->
<div class="modal-overlay" id="addUserModal">
    <div class="modal-card modal-card-wide">
        <div class="modal-header">
            <h3>Add User</h3>
            <button class="modal-close" id="closeAddUserModalBtn">&times;</button>
        </div>
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_user_name">Name</label>
                        <input type="text" id="add_user_name" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="add_user_email">Email</label>
                        <input type="email" id="add_user_email" name="email" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_user_mobile">Mobile</label>
                        <input type="text" id="add_user_mobile" name="mobile" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="add_user_device_limit">Device Limit</label>
                        <input type="number" id="add_user_device_limit" name="device_limit" class="form-control" min="1" value="10" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_user_start_date">Start Date</label>
                        <input type="date" id="add_user_start_date" name="start_date" class="form-control" value="{{ $today }}" required>
                    </div>
                    <div class="form-group">
                        <label for="add_user_expire_date">Expire Date</label>
                        <input type="date" id="add_user_expire_date" name="expire_date" class="form-control" value="{{ $oneYearLater }}" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_user_password">Password</label>
                        <input type="password" id="add_user_password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="add_user_password_confirmation">Confirm Password</label>
                        <input type="password" id="add_user_password_confirmation" name="password_confirmation" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Services</label>
                    <div class="checkbox-group">
                        @forelse($services as $service)
                            <label class="checkbox-label">
                                <input type="checkbox" name="services[]" value="{{ $service->id }}">
                                {{ $service->name }}
                            </label>
                        @empty
                            <p style="color: var(--text-muted); font-size: 0.85rem;">No services available. Create services first.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="cancelAddUserModalBtn">Cancel</button>
                <button type="submit" class="btn-primary" style="width:auto; padding: 0.5rem 1.5rem;">Create User</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal-overlay" id="editUserModal">
    <div class="modal-card modal-card-wide">
        <div class="modal-header">
            <h3>Edit User</h3>
            <button class="modal-close" id="closeEditUserModalBtn">&times;</button>
        </div>
        <form action="" method="POST" id="editUserForm">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_user_name">Name</label>
                        <input type="text" id="edit_user_name" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_user_email">Email</label>
                        <input type="email" id="edit_user_email" name="email" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_user_mobile">Mobile</label>
                        <input type="text" id="edit_user_mobile" name="mobile" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_user_device_limit">Device Limit</label>
                        <input type="number" id="edit_user_device_limit" name="device_limit" class="form-control" min="1" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_user_start_date">Start Date</label>
                        <input type="date" id="edit_user_start_date" name="start_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_user_expire_date">Expire Date</label>
                        <input type="date" id="edit_user_expire_date" name="expire_date" class="form-control" required>
                    </div>
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
                <div class="form-group">
                    <label>Services</label>
                    <div class="checkbox-group" id="editUserServices">
                        @foreach($services as $service)
                            <label class="checkbox-label">
                                <input type="checkbox" name="services[]" value="{{ $service->id }}" class="edit-service-checkbox">
                                {{ $service->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="cancelEditUserModalBtn">Cancel</button>
                <button type="submit" class="btn-primary" style="width:auto; padding: 0.5rem 1.5rem;">Update User</button>
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

    const addModal = document.getElementById('addUserModal');
    document.getElementById('openAddUserModalBtn').addEventListener('click', () => addModal.classList.add('open'));
    document.getElementById('closeAddUserModalBtn').addEventListener('click', () => addModal.classList.remove('open'));
    document.getElementById('cancelAddUserModalBtn').addEventListener('click', () => addModal.classList.remove('open'));

    const editModal = document.getElementById('editUserModal');
    const editForm = document.getElementById('editUserForm');

    document.querySelectorAll('.editUserBtn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('edit_user_name').value = this.getAttribute('data-name');
            document.getElementById('edit_user_email').value = this.getAttribute('data-email');
            document.getElementById('edit_user_mobile').value = this.getAttribute('data-mobile') || '';
            document.getElementById('edit_user_device_limit').value = this.getAttribute('data-device-limit');
            document.getElementById('edit_user_start_date').value = this.getAttribute('data-start-date');
            document.getElementById('edit_user_expire_date').value = this.getAttribute('data-expire-date');
            document.getElementById('edit_user_password').value = '';
            document.getElementById('edit_user_password_confirmation').value = '';

            const serviceIds = (this.getAttribute('data-services') || '').split(',').filter(Boolean);
            document.querySelectorAll('.edit-service-checkbox').forEach(cb => {
                cb.checked = serviceIds.includes(cb.value);
            });

            editForm.action = `/users/${this.getAttribute('data-id')}`;
            editModal.classList.add('open');
        });
    });

    document.getElementById('closeEditUserModalBtn').addEventListener('click', () => editModal.classList.remove('open'));
    document.getElementById('cancelEditUserModalBtn').addEventListener('click', () => editModal.classList.remove('open'));
});
</script>
@endsection
