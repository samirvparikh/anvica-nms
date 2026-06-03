<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Anvica NMS - Network Monitoring System</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="app-wrapper">
        <!-- Sidebar -->
        <aside class="app-sidebar">
            <div class="sidebar-header">
                <div class="logo-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                    </svg>
                </div>
                <div class="auth-logo-text">
                    <h2 style="color: white; font-size: 1.1rem; font-weight: 700;">Anvica NMS</h2>
                    <p style="color: #64748b; font-size: 0.65rem;">Network Monitoring System</p>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section-title">Main</div>
                <ul class="nav-list">
                    <li>
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->is('/') || request()->is('dashboard') ? 'active' : '' }}">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="7" height="9" rx="1"/>
                                <rect x="14" y="3" width="7" height="5" rx="1"/>
                                <rect x="14" y="12" width="7" height="9" rx="1"/>
                                <rect x="3" y="16" width="7" height="5" rx="1"/>
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('devices.index') }}" class="nav-link {{ request()->is('devices') ? 'active' : '' }}">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="2" y="2" width="20" height="8" rx="2" ry="2"/>
                                <rect x="2" y="14" width="20" height="8" rx="2" ry="2"/>
                                <line x1="6" y1="6" x2="6.01" y2="6"/>
                                <line x1="6" y1="18" x2="6.01" y2="18"/>
                            </svg>
                            Devices
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('alarms.index') }}" class="nav-link {{ request()->is('alarms') ? 'active' : '' }}">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                            Alarms
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('maps.index') }}" class="nav-link {{ request()->is('maps') ? 'active' : '' }}">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/>
                                <line x1="9" y1="3" x2="9" y2="18"/>
                                <line x1="15" y1="6" x2="15" y2="21"/>
                            </svg>
                            Maps
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('api-request-logs') }}" class="nav-link {{ request()->is('api-request-logs') ? 'active' : '' }}">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M9 12h6m-6 4h6M9 8h6M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            API Data
                        </a>
                    </li>
                    
                    <li>
                        <a href="#" class="nav-link">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                                <polyline points="10 9 9 9 8 9"/>
                            </svg>
                            Reports
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M20 7h-9L9 5H4a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                            </svg>
                            Inventory
                        </a>
                    </li>
                </ul>
                
                <div class="nav-section-title">Settings</div>
                <ul class="nav-list">
                    <li>
                        <a href="#" class="nav-link">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="3"/>
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                            </svg>
                            Settings
                        </a>
                    </li>
                    
                    <li>
                        <form action="{{ route('logout') }}" method="POST" id="logout-form" style="display: none;">
                            @csrf
                        </form>
                        <a href="#" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                            Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="app-header">
                <div class="header-left">
                    <div class="header-search">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input type="text" placeholder="Search devices, alarms...">
                    </div>
                </div>

                <div class="header-right">
                    <!-- Notifications -->
                    <div class="notification-widget" onclick="window.location.href='{{ route('alarms.index') }}'">
                        <button class="notification-btn">
                            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                            </svg>
                        </button>
                        @php
                            $activeAlarmsCount = \App\Models\Alarm::where('status', 'Open')->count();
                        @endphp
                        @if($activeAlarmsCount > 0)
                            <span class="notification-badge">{{ $activeAlarmsCount }}</span>
                        @endif
                    </div>

                    <!-- User Info -->
                    <div class="user-profile-menu">
                        <div class="user-avatar">
                            AD
                        </div>
                        <div class="user-details">
                            <h4>admin</h4>
                            <p>Administrator</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Body -->
            <div class="page-body">
                @if(session('success'))
                    <div style="background-color: var(--bg-up); border: 1px solid rgba(34, 197, 94, 0.2); color: var(--status-up); padding: 0.75rem 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.85rem; font-weight: 600;">
                        {{ session('success') }}
                    </div>
                @endif
                @if($errors->any())
                    <div style="background-color: var(--bg-down); border: 1px solid rgba(239, 68, 68, 0.2); color: var(--status-down); padding: 0.75rem 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.85rem; font-weight: 600;">
                        <ul style="list-style: none;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
