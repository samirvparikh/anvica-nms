@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Settings</h1>
        <p>Configure application mail and SMTP settings.</p>
    </div>
</div>

<div class="profile-card settings-card">
    <h3 class="settings-section-title">SMTP Mail Settings</h3>
    <p class="settings-section-desc">These settings are used when the application sends emails (alerts, notifications, etc.).</p>

    <form action="{{ route('settings.mail.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="settings-form-grid">
            <div class="form-group">
                <label for="host">SMTP Host</label>
                <input type="text" id="host" name="host" class="form-input" value="{{ old('host', $mailSettings->host) }}" placeholder="e.g. smtp.gmail.com" required>
            </div>

            <div class="form-group">
                <label for="port">SMTP Port</label>
                <input type="number" id="port" name="port" class="form-input" value="{{ old('port', $mailSettings->port ?? 587) }}" min="1" max="65535" required>
            </div>

            <div class="form-group">
                <label for="encryption">Encryption</label>
                <select id="encryption" name="encryption" class="form-input">
                    @php
                        $encryption = old('encryption', $mailSettings->encryption ?? 'tls');
                        $encryption = $encryption === null ? 'none' : $encryption;
                    @endphp
                    <option value="tls" {{ $encryption === 'tls' ? 'selected' : '' }}>TLS</option>
                    <option value="ssl" {{ $encryption === 'ssl' ? 'selected' : '' }}>SSL</option>
                    <option value="none" {{ $encryption === 'none' ? 'selected' : '' }}>None</option>
                </select>
            </div>

            <div class="form-group">
                <label for="username">SMTP Username</label>
                <input type="text" id="username" name="username" class="form-input" value="{{ old('username', $mailSettings->username) }}" placeholder="e.g. noreply@yourdomain.com">
            </div>

            <div class="form-group">
                <label for="password">SMTP Password</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="Leave blank to keep current password">
                <span class="form-hint">Leave blank to keep the existing password.</span>
            </div>

            <div class="form-group">
                <label for="from_address">From Email Address</label>
                <input type="email" id="from_address" name="from_address" class="form-input" value="{{ old('from_address', $mailSettings->from_address) }}" placeholder="e.g. nms@anvica.in" required>
            </div>

            <div class="form-group">
                <label for="from_name">From Name</label>
                <input type="text" id="from_name" name="from_name" class="form-input" value="{{ old('from_name', $mailSettings->from_name ?? config('app.name')) }}" placeholder="e.g. Anvica NMS" required>
            </div>
        </div>

        <div class="profile-form-actions">
            <button type="submit" class="btn-save">Save SMTP Settings</button>
        </div>
    </form>

    <div class="settings-test-section">
        <h4 class="settings-subtitle">Send Test Email</h4>
        <p class="settings-section-desc">Save settings first, then send a test email to verify the configuration.</p>

        <form action="{{ route('settings.mail.test') }}" method="POST" class="settings-test-form">
            @csrf
            <div class="form-group">
                <label for="test_email">Test Email Address</label>
                <input type="email" id="test_email" name="test_email" class="form-input" value="{{ old('test_email', auth()->user()->email) }}" placeholder="recipient@example.com" required>
            </div>
            <button type="submit" class="btn-secondary">Send Test Email</button>
        </form>
    </div>
</div>
@endsection
