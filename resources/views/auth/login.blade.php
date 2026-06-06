<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in - Anvica NMS</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="auth-container">
        <!-- Left Side: Brand Section -->
        <div class="auth-sidebar">
            <div class="auth-logo">
                <div class="logo-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                    </svg>
                </div>
                <div class="auth-logo-text">
                    <h2 style="color: white; font-weight: 700; font-size: 1.15rem; margin: 0;">Anvica NMS</h2>
                    <p style="color: #64748b; font-size: 0.65rem; margin: 0;">Network Monitoring System</p>
                </div>
            </div>

            <div class="auth-content-middle">
                <h1 class="auth-title-large">
                    Stay Ahead. Stay Connected.<br>
                    <span>We Monitor, You Grow.</span>
                </h1>
                <p class="auth-desc-large">
                    A robust, scalable and customized NMS solution to monitor your entire IT/network infrastructure from one centralized dashboard.
                </p>
            </div>

            <div class="auth-footer">
                &copy; 2026 Anvica Infosys Pvt. Ltd.
            </div>
        </div>

        <!-- Right Side: Form Section -->
        <div class="auth-form-section">
            <div class="auth-form-wrapper">
                <div class="auth-form-header">
                    <h2>Sign in</h2>
                    <p>Enter your credentials to access the dashboard.</p>
                </div>

                @if($errors->any())
                    <div style="background-color: var(--bg-down); border: 1px solid rgba(239, 68, 68, 0.2); color: var(--status-down); padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.8rem; font-weight: 600;">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ url('/login') }}" method="POST">
                    @csrf
                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-with-icon">
                            <span class="input-icon">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                    <polyline points="22,6 12,13 2,6"/>
                                </svg>
                            </span>
                            <input type="email" id="email" name="email" class="form-control" placeholder="Enter Email" value="admin@anvica.in" required>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-with-icon">
                            <span class="input-icon">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </span>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Enter Password" value="" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">Sign in</button>

                    <p class="demo-text">Demo: any email & password works.</p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
