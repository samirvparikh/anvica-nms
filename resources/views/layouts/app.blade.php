<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Anvica NMS - Network Monitoring System</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/data-table-sort.js') }}" defer></script>
    <script src="{{ asset('js/data-table-filter.js') }}" defer></script>
</head>
<body>
    @php
        $authUser = Auth::user();
        $userInitials = collect(explode(' ', $authUser->name ?? 'User'))
            ->filter()
            ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
            ->take(2)
            ->join('');
        $isProfileActive = request()->is('profile*');
        $isAdmin = $authUser->isAdmin();
        $navSectionServiceDesk = request()->is('tickets*') || request()->is('incidents*') || request()->is('problems*') || request()->is('changes*') || request()->is('knowledge-base*');
        $navSectionMaintenance = request()->is('maintenance/preventive*') || request()->is('maintenance/calendar*') || request()->is('maintenance/windows*');
        $navSectionInventory = request()->is('inventory*');
        $navSectionSla = request()->is('sla*');
        $navSectionAdmin = request()->is('alerts/manage') || request()->is('vendors*') || request()->is('services*') || request()->is('service-points*') || request()->is('users*') || request()->is('settings*') || request()->is('api-request-logs*');
    @endphp

    <div class="app-wrapper" id="appWrapper">
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar -->
        <aside class="app-sidebar" id="appSidebar">
            <div class="sidebar-header">
                <button type="button" class="sidebar-collapse-btn" id="sidebarCollapseBtn" aria-label="Collapse sidebar" title="Collapse menu">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
                <div class="sidebar-brand">
                    <div class="logo-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                        </svg>
                    </div>
                    <div class="auth-logo-text sidebar-brand-text">
                        <h2 style="color: white; font-size: 1.1rem; font-weight: 700;">Anvica NMS</h2>
                        <p style="color: #64748b; font-size: 0.65rem;">Network Monitoring System</p>
                    </div>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section-title">MONITORING</div>
                <ul class="nav-list">
                    <li>
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->is('/') || request()->is('dashboard') ? 'active' : '' }}" title="Dashboard">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="7" height="9" rx="1"/>
                                <rect x="14" y="3" width="7" height="5" rx="1"/>
                                <rect x="14" y="12" width="7" height="9" rx="1"/>
                                <rect x="3" y="16" width="7" height="5" rx="1"/>
                            </svg>
                            <span class="nav-link-text">Dashboard</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="{{ route('devices.index') }}" class="nav-link {{ request()->is('devices') ? 'active' : '' }}" title="Devices">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="2" y="2" width="20" height="8" rx="2" ry="2"/>
                                <rect x="2" y="14" width="20" height="8" rx="2" ry="2"/>
                                <line x1="6" y1="6" x2="6.01" y2="6"/>
                                <line x1="6" y1="18" x2="6.01" y2="18"/>
                            </svg>
                            <span class="nav-link-text">Devices</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('alarms.index') }}" class="nav-link {{ request()->is('alarms') ? 'active' : '' }}" title="Alarms">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                            <span class="nav-link-text">Alarms</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('maps.index') }}" class="nav-link {{ request()->is('maps') ? 'active' : '' }}" title="Maps">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/>
                                <line x1="9" y1="3" x2="9" y2="18"/>
                                <line x1="15" y1="6" x2="15" y2="21"/>
                            </svg>
                            <span class="nav-link-text">Maps</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('reports.performance-traffic') }}" class="nav-link {{ request()->is('reports/performance-traffic*') ? 'active' : '' }}" title="Performance">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                            </svg>
                            <span class="nav-link-text">Performance</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->is('reports*') ? 'active' : '' }}" title="Reports">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                                <polyline points="10 9 9 9 8 9"/>
                            </svg>
                            <span class="nav-link-text">Reports</span>
                        </a>
                    </li>
                </ul>

                <div class="nav-section {{ $navSectionServiceDesk ? 'is-expanded' : '' }}" data-section="service-desk">
                    <button type="button" class="nav-section-toggle" aria-expanded="{{ $navSectionServiceDesk ? 'true' : 'false' }}">
                        <span class="nav-section-title">SERVICE DESK</span>
                        <svg class="nav-section-chevron" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>
                    <div class="nav-section-body">
                <ul class="nav-list">
                    <li>
                        <a href="{{ route('tickets.index') }}" class="nav-link {{ request()->is('tickets*') ? 'active' : '' }}" title="Tickets">
                            <i class="fa-solid fa-ticket" style="margin-right: 0.5rem; font-size: 1.1rem; width: 1.25rem; text-align: center;"></i>
                            <span class="nav-link-text">Tickets</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('incidents.index') }}" class="nav-link {{ request()->is('incidents*') ? 'active' : '' }}" title="Incidents">
                            <i class="fa-solid fa-circle-exclamation" style="margin-right: 0.5rem; font-size: 1.1rem; width: 1.25rem; text-align: center;"></i>
                            <span class="nav-link-text">Incidents</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('problems.index') }}" class="nav-link {{ request()->is('problems*') ? 'active' : '' }}" title="Problems">
                            <i class="fa-solid fa-bug" style="margin-right: 0.5rem; font-size: 1.1rem; width: 1.25rem; text-align: center;"></i>
                            <span class="nav-link-text">Problems</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('changes.index') }}" class="nav-link {{ request()->is('changes*') ? 'active' : '' }}" title="Changes">
                            <i class="fa-solid fa-code-branch" style="margin-right: 0.5rem; font-size: 1.1rem; width: 1.25rem; text-align: center;"></i>
                            <span class="nav-link-text">Changes</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('knowledge-base.index') }}" class="nav-link {{ request()->is('knowledge-base*') ? 'active' : '' }}" title="Knowledge Base">
                            <i class="fa-solid fa-book" style="margin-right: 0.5rem; font-size: 1.1rem; width: 1.25rem; text-align: center;"></i>
                            <span class="nav-link-text">Knowledge Base</span>
                        </a>
                    </li>
                </ul>
                    </div>
                </div>

                <div class="nav-section {{ $navSectionMaintenance ? 'is-expanded' : '' }}" data-section="maintenance">
                    <button type="button" class="nav-section-toggle" aria-expanded="{{ $navSectionMaintenance ? 'true' : 'false' }}">
                        <span class="nav-section-title">MAINTENANCE</span>
                        <svg class="nav-section-chevron" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>
                    <div class="nav-section-body">
                <ul class="nav-list">
                    <li>
                        <a href="{{ route('maintenance.preventive.index') }}" class="nav-link {{ request()->is('maintenance/preventive*') ? 'active' : '' }}" title="Preventive Downtime">
                            <i class="fa-solid fa-screwdriver-wrench" style="margin-right: 0.5rem; font-size: 1.1rem; width: 1.25rem; text-align: center;"></i>
                            <span class="nav-link-text">Preventive Downtime</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('maintenance.calendar.index') }}" class="nav-link {{ request()->is('maintenance/calendar*') ? 'active' : '' }}" title="Maintenance Calendar">
                            <i class="fa-solid fa-calendar-days" style="margin-right: 0.5rem; font-size: 1.1rem; width: 1.25rem; text-align: center;"></i>
                            <span class="nav-link-text">Maintenance Calendar</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('maintenance.windows.index') }}" class="nav-link {{ request()->is('maintenance/windows*') ? 'active' : '' }}" title="Maintenance Windows">
                            <i class="fa-solid fa-window-restore" style="margin-right: 0.5rem; font-size: 1.1rem; width: 1.25rem; text-align: center;"></i>
                            <span class="nav-link-text">Maintenance Windows</span>
                        </a>
                    </li>
                </ul>
                    </div>
                </div>

                <div class="nav-section {{ $navSectionInventory ? 'is-expanded' : '' }}" data-section="inventory">
                    <button type="button" class="nav-section-toggle" aria-expanded="{{ $navSectionInventory ? 'true' : 'false' }}">
                        <span class="nav-section-title">INVENTORY</span>
                        <svg class="nav-section-chevron" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>
                    <div class="nav-section-body">
                <ul class="nav-list">
                    <li>
                        <a href="{{ route('inventory.assets.index') }}" class="nav-link {{ request()->is('inventory/assets*') ? 'active' : '' }}" title="Assets">
                            <i class="fa-solid fa-server" style="margin-right: 0.5rem; font-size: 1.1rem; width: 1.25rem; text-align: center;"></i>
                            <span class="nav-link-text">Assets</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('inventory.asset-groups.index') }}" class="nav-link {{ request()->is('inventory/asset-groups*') ? 'active' : '' }}" title="Asset Groups">
                            <i class="fa-solid fa-network-wired" style="margin-right: 0.5rem; font-size: 1.1rem; width: 1.25rem; text-align: center;"></i>
                            <span class="nav-link-text">Asset Groups</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('inventory.software.index') }}" class="nav-link {{ request()->is('inventory/software*') ? 'active' : '' }}" title="Software">
                            <i class="fa-solid fa-paste" style="margin-right: 0.5rem; font-size: 1.1rem; width: 1.25rem; text-align: center;"></i>
                            <span class="nav-link-text">Software</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('inventory.warranty.index') }}" class="nav-link {{ request()->is('inventory/warranty*') ? 'active' : '' }}" title="Warranty">
                            <i class="fa-solid fa-file-shield" style="margin-right: 0.5rem; font-size: 1.1rem; width: 1.25rem; text-align: center;"></i>
                            <span class="nav-link-text">Warranty</span>
                        </a>
                    </li>
                </ul>
                    </div>
                </div>

                <div class="nav-section {{ $navSectionSla ? 'is-expanded' : '' }}" data-section="sla-management">
                    <button type="button" class="nav-section-toggle" aria-expanded="{{ $navSectionSla ? 'true' : 'false' }}">
                        <span class="nav-section-title">SLA MANAGEMENT</span>
                        <svg class="nav-section-chevron" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>
                    <div class="nav-section-body">
                <ul class="nav-list">
                    <li>
                        <a href="{{ route('sla.dashboard') }}" class="nav-link {{ request()->is('sla/dashboard*') ? 'active' : '' }}" title="SLA Dashboard">
                            <i class="fa-solid fa-chart-line" style="margin-right: 0.5rem; font-size: 1.1rem; width: 1.25rem; text-align: center;"></i>
                            <span class="nav-link-text">SLA Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('sla.reports') }}" class="nav-link {{ request()->is('sla/reports*') ? 'active' : '' }}" title="SLA Reports">
                            <i class="fa-solid fa-file-invoice" style="margin-right: 0.5rem; font-size: 1.1rem; width: 1.25rem; text-align: center;"></i>
                            <span class="nav-link-text">SLA Reports</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('sla.targets') }}" class="nav-link {{ request()->is('sla/targets*') ? 'active' : '' }}" title="SLA Targets">
                            <i class="fa-solid fa-bullseye" style="margin-right: 0.5rem; font-size: 1.1rem; width: 1.25rem; text-align: center;"></i>
                            <span class="nav-link-text">SLA Targets</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('sla.maintenance') }}" class="nav-link {{ request()->is('sla/maintenance*') ? 'active' : '' }}" title="Maintenance">
                            <i class="fa-solid fa-business-time" style="margin-right: 0.5rem; font-size: 1.1rem; width: 1.25rem; text-align: center;"></i>
                            <span class="nav-link-text">Maintenance</span>
                        </a>
                    </li>
                </ul>
                    </div>
                </div>

                @if($isAdmin)
                <div class="nav-section {{ $navSectionAdmin ? 'is-expanded' : '' }}" data-section="administration">
                    <button type="button" class="nav-section-toggle" aria-expanded="{{ $navSectionAdmin ? 'true' : 'false' }}">
                        <span class="nav-section-title">Administration</span>
                        <svg class="nav-section-chevron" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>
                    <div class="nav-section-body">
                <ul class="nav-list">
                    <li>
                        <a href="{{ route('alerts.manage') }}" class="nav-link {{ request()->is('alerts/manage') ? 'active' : '' }}" title="Manage Alerts">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M12 20h9"/>
                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                            </svg>
                            <span class="nav-link-text">Manage Alerts</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="{{ route('vendors.index') }}" class="nav-link {{ request()->is('vendors*') ? 'active' : '' }}" title="Vendors">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                <polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                            <span class="nav-link-text">Vendors</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="{{ route('services.index') }}" class="nav-link {{ request()->is('services*') ? 'active' : '' }}" title="Services">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                            </svg>
                            <span class="nav-link-text">Services</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('service-points.index') }}" class="nav-link {{ request()->is('service-points*') ? 'active' : '' }}" title="Service Points">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="3"/>
                                <path d="M12 2v4M12 18v4M2 12h4M18 12h4"/>
                            </svg>
                            <span class="nav-link-text">Service Points</span>
                        </a>
                    </li>

                    

                    <li>
                        <a href="{{ route('users.index') }}" class="nav-link {{ request()->is('users*') ? 'active' : '' }}" title="Users">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                            <span class="nav-link-text">Users</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="{{ route('settings.edit') }}" class="nav-link {{ request()->is('settings*') ? 'active' : '' }}" title="Settings">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="3"/>
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                            </svg>
                            <span class="nav-link-text">Settings</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('api-request-logs') }}" class="nav-link {{ request()->is('api-request-logs*') ? 'active' : '' }}" title="API Data">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M9 12h6m-6 4h6M9 8h6M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="nav-link-text">API Data</span>
                        </a>
                    </li>
                </ul>
                    </div>
                </div>
                @endif

                <div class="nav-section-title">Account</div>
                <ul class="nav-list">
                    <li>
                        <form action="{{ route('logout') }}" method="POST" id="logout-form" style="display: none;">
                            @csrf
                        </form>
                        <a href="#" class="nav-link" title="Logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                            <span class="nav-link-text">Logout</span>
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
                    <button type="button" class="sidebar-toggle-btn" id="sidebarMobileBtn" aria-label="Open menu">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <line x1="3" y1="12" x2="21" y2="12"/>
                            <line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                    <div class="header-search">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input type="text" placeholder="Search devices, alerts...">
                    </div>
                    @if(Auth::user() && Auth::user()->isAdmin() && (request()->is('/') || request()->is('dashboard*')))
                        <div class="header-user-filter">
                            <i class="fa-solid fa-user-tag select-icon"></i>
                            <select id="headerUserSelect" class="form-control-select" onchange="filterDashboardByUser(this.value)">
                                <option value="">All Devices</option>
                                @foreach($dashboardUsers ?? [] as $dbUser)
                                    <option value="{{ $dbUser->id }}" {{ request()->query('user_id') == $dbUser->id ? 'selected' : '' }}>
                                        {{ $dbUser->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <script>
                            function filterDashboardByUser(userId) {
                                const url = new URL(window.location.href);
                                if (userId) {
                                    url.searchParams.set('user_id', userId);
                                } else {
                                    url.searchParams.delete('user_id');
                                }
                                window.location.href = url.pathname + url.search;
                            }
                        </script>
                    @endif
                </div>

                <div class="header-right">
                    <!-- Notifications -->
                    <div class="notification-widget" id="notificationWidget">
                        <button class="notification-btn" id="notificationTrigger" aria-expanded="false" aria-haspopup="true" aria-label="Toggle notifications">
                            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                            </svg>
                        </button>
                        @if(($activeAlertsCount ?? 0) > 0)
                            <span class="notification-badge">{{ $activeAlertsCount }}</span>
                        @endif

                        <!-- Notifications Dropdown -->
                        <div class="notification-dropdown" id="notificationDropdown">
                            <div class="dropdown-header">
                                <h3>Notifications</h3>
                                @if(($activeAlertsCount ?? 0) > 0)
                                    <span class="badge">{{ $activeAlertsCount }} Active</span>
                                @endif
                            </div>
                            <div class="dropdown-body">
                                @forelse($headerNotifications ?? [] as $notification)
                                    <a href="{{ $notification['url'] }}" class="notification-item {{ $notification['severity'] }}">
                                        <div class="notification-icon">
                                            @if($notification['severity'] === 'critical')
                                                <i class="fa-solid fa-circle-exclamation text-danger" style="color: var(--status-down);"></i>
                                            @elseif($notification['severity'] === 'warning')
                                                <i class="fa-solid fa-triangle-exclamation text-warning" style="color: var(--status-warning);"></i>
                                            @else
                                                <i class="fa-solid fa-circle-info text-info" style="color: #3b82f6;"></i>
                                            @endif
                                        </div>
                                        <div class="notification-content">
                                            <div class="notification-meta">
                                                <span class="device-name">{{ $notification['device_name'] }}</span>
                                                <span class="time">{{ $notification['created_at']->diffForHumans() }}</span>
                                            </div>
                                            <p class="notification-message">{{ $notification['message'] }}</p>
                                        </div>
                                    </a>
                                @empty
                                    <div class="empty-state">
                                        <i class="fa-regular fa-bell-slash" style="font-size: 1.5rem; margin-bottom: 0.5rem; display: block; color: var(--text-muted);"></i>
                                        <p>No active notifications</p>
                                    </div>
                                @endforelse
                            </div>
                            <div class="dropdown-footer">
                                <a href="{{ route('alerts.index') }}">View All Alerts</a>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile Menu -->
                    <div class="user-profile-menu {{ $isProfileActive ? 'active' : '' }}" id="userProfileMenu">
                        <button type="button" class="user-profile-trigger" id="userProfileTrigger" aria-expanded="false" aria-haspopup="true">
                            <div class="user-avatar">{{ $userInitials }}</div>
                            <div class="user-details">
                                <h4>{{ $authUser->name }}</h4>
                                <p>{{ $isAdmin ? 'Administrator' : 'User' }}</p>
                            </div>
                            <svg class="user-profile-chevron" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </button>
                        <div class="user-profile-dropdown" id="userProfileDropdown">
                            <a href="{{ route('profile.edit') }}" class="user-profile-dropdown-item {{ $isProfileActive ? 'active' : '' }}">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                Profile
                            </a>
                            <a href="#" class="user-profile-dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                    <polyline points="16 17 21 12 16 7"/>
                                    <line x1="21" y1="12" x2="9" y2="12"/>
                                </svg>
                                Logout
                            </a>
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

    <script>
        (function () {
            var wrapper = document.getElementById('appWrapper');
            var collapseBtn = document.getElementById('sidebarCollapseBtn');
            var mobileBtn = document.getElementById('sidebarMobileBtn');
            var overlay = document.getElementById('sidebarOverlay');
            var profileMenu = document.getElementById('userProfileMenu');
            var profileTrigger = document.getElementById('userProfileTrigger');
            var profileDropdown = document.getElementById('userProfileDropdown');

            function isMobile() {
                return window.matchMedia('(max-width: 991px)').matches;
            }

            function setCollapsed(collapsed) {
                wrapper.classList.toggle('sidebar-collapsed', collapsed);
                localStorage.setItem('sidebarCollapsed', collapsed ? '1' : '0');
            }

            function closeMobileSidebar() {
                wrapper.classList.remove('sidebar-open');
            }

            function openMobileSidebar() {
                wrapper.classList.add('sidebar-open');
            }

            if (localStorage.getItem('sidebarCollapsed') === '1' && !isMobile()) {
                wrapper.classList.add('sidebar-collapsed');
            }

            if (collapseBtn) {
                collapseBtn.addEventListener('click', function () {
                    if (isMobile()) {
                        closeMobileSidebar();
                        return;
                    }
                    setCollapsed(!wrapper.classList.contains('sidebar-collapsed'));
                });
            }

            if (mobileBtn) {
                mobileBtn.addEventListener('click', function () {
                    if (wrapper.classList.contains('sidebar-open')) {
                        closeMobileSidebar();
                    } else {
                        openMobileSidebar();
                    }
                });
            }

            if (overlay) {
                overlay.addEventListener('click', closeMobileSidebar);
            }

            window.addEventListener('resize', function () {
                if (!isMobile()) {
                    closeMobileSidebar();
                } else {
                    wrapper.classList.remove('sidebar-collapsed');
                }
            });

            var notificationWidget = document.getElementById('notificationWidget');
            var notificationTrigger = document.getElementById('notificationTrigger');
            var notificationDropdown = document.getElementById('notificationDropdown');

            if (profileTrigger && profileDropdown) {
                profileTrigger.addEventListener('click', function (e) {
                    e.stopPropagation();
                    var isOpen = profileMenu.classList.toggle('open');
                    profileTrigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

                    // Close notification dropdown if it is open
                    if (isOpen && notificationWidget) {
                        notificationWidget.classList.remove('open');
                        if (notificationTrigger) notificationTrigger.setAttribute('aria-expanded', 'false');
                    }
                });

                document.addEventListener('click', function (e) {
                    if (!profileMenu.contains(e.target)) {
                        profileMenu.classList.remove('open');
                        profileTrigger.setAttribute('aria-expanded', 'false');
                    }
                });
            }

            if (notificationTrigger && notificationDropdown) {
                notificationTrigger.addEventListener('click', function (e) {
                    e.stopPropagation();
                    var isOpen = notificationWidget.classList.toggle('open');
                    notificationTrigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

                    // Close profile menu if it is open
                    if (isOpen && profileMenu) {
                        profileMenu.classList.remove('open');
                        if (profileTrigger) profileTrigger.setAttribute('aria-expanded', 'false');
                    }
                });

                document.addEventListener('click', function (e) {
                    if (notificationWidget && !notificationWidget.contains(e.target)) {
                        notificationWidget.classList.remove('open');
                        notificationTrigger.setAttribute('aria-expanded', 'false');
                    }
                });
            }

            var navSections = document.querySelectorAll('.nav-section[data-section]');
            var navSectionsStorageKey = 'navSectionsState';

            function getNavSectionsState() {
                try {
                    return JSON.parse(localStorage.getItem(navSectionsStorageKey) || '{}');
                } catch (e) {
                    return {};
                }
            }

            function saveNavSectionState(sectionId, expanded) {
                var state = getNavSectionsState();
                state[sectionId] = expanded;
                localStorage.setItem(navSectionsStorageKey, JSON.stringify(state));
            }

            navSections.forEach(function (section) {
                var sectionId = section.getAttribute('data-section');
                var toggle = section.querySelector('.nav-section-toggle');
                var hasActive = section.querySelector('.nav-link.active');
                var stored = getNavSectionsState()[sectionId];
                var expanded = !!hasActive || stored === true;

                section.classList.toggle('is-expanded', expanded);
                if (toggle) {
                    toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                }

                if (toggle) {
                    toggle.addEventListener('click', function () {
                        var isExpanded = section.classList.toggle('is-expanded');
                        toggle.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
                        saveNavSectionState(sectionId, isExpanded);
                    });
                }
            });
        })();
    </script>
</body>
</html>
