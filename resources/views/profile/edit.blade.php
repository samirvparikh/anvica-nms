@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Profile</h1>
        <p>Update your account information.</p>
    </div>
</div>

<div class="profile-card">
    <form action="{{ route('profile.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="profile-form-grid">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-input" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" class="form-input is-readonly" value="{{ $user->email }}" readonly disabled>
                <span class="form-hint">Email cannot be changed.</span>
            </div>

            <div class="form-group">
                <label for="mobile">Mobile</label>
                <input type="text" id="mobile" class="form-input is-readonly" value="{{ $user->mobile ?? '—' }}" readonly disabled>
                <span class="form-hint">Mobile number cannot be changed.</span>
            </div>

            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="Leave blank to keep current password">
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm New Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" placeholder="Confirm new password">
            </div>
        </div>

        <div class="profile-form-actions">
            <button type="submit" class="btn-save">Save Changes</button>
        </div>
    </form>
</div>
@endsection
