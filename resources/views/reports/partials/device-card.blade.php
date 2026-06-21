<article class="report-device-card report-device-card--{{ $healthClass }}">
    <div class="report-card-accent"></div>

    <div class="report-card-top">
        <div class="report-card-identity">
            <div class="report-card-icon">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="2" y="2" width="20" height="8" rx="2" ry="2"/>
                    <rect x="2" y="14" width="20" height="8" rx="2" ry="2"/>
                    <line x1="6" y1="6" x2="6.01" y2="6"/>
                    <line x1="6" y1="18" x2="6.01" y2="18"/>
                </svg>
            </div>
            <div class="report-card-meta">
                <div class="report-card-name-row">
                    <h3>{{ $device->name }}</h3>
                    <span class="status-badge {{ $healthClass }}">{{ $health }}</span>
                </div>
                <p class="report-card-subtitle">
                    {{ $device->service?->name ?? $device->type ?? 'Unknown service' }}
                    @if($device->vendor?->name)
                        <span class="report-card-dot">•</span> {{ $device->vendor->name }}
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div class="report-metrics-row">
        <div class="report-metric-tile report-metric-tile--{{ $metricLevel($cpuPct) }}">
            <span class="report-metric-label">CPU</span>
            <span class="report-metric-value">{{ $cpuPct !== null ? number_format($cpuPct, 1) . '%' : '—' }}</span>
            <span class="report-metric-bar"><span style="width:{{ min(100, $cpuPct ?? 0) }}%"></span></span>
        </div>
        <div class="report-metric-tile report-metric-tile--{{ $metricLevel($ramPct) }}">
            <span class="report-metric-label">RAM</span>
            <span class="report-metric-value">{{ $ramPct !== null ? number_format($ramPct, 1) . '%' : '—' }}</span>
            <span class="report-metric-bar"><span style="width:{{ min(100, $ramPct ?? 0) }}%"></span></span>
        </div>
        <div class="report-metric-tile report-metric-tile--{{ $metricLevel($diskPct) }}">
            <span class="report-metric-label">Disk</span>
            <span class="report-metric-value">{{ $diskPct !== null ? number_format($diskPct, 1) . '%' : '—' }}</span>
            <span class="report-metric-bar"><span style="width:{{ min(100, $diskPct ?? 0) }}%"></span></span>
        </div>
        <div class="report-metric-tile report-metric-tile--{{ $metricLevel($tempVal, 70, 85) }}">
            <span class="report-metric-label">Temp</span>
            <span class="report-metric-value">{{ $tempVal !== null ? number_format($tempVal, 1) . '°C' : '—' }}</span>
            <span class="report-metric-bar"><span style="width:{{ min(100, $tempVal ?? 0) }}%"></span></span>
        </div>
    </div>

    <div class="report-info-panel">
        <div class="report-detail-item">
            <span class="report-detail-label">IP Address</span>
            <span class="report-detail-value cell-mono">{{ $device->ip_address ?? '—' }}</span>
        </div>
        <div class="report-detail-item">
            <span class="report-detail-label">Hostname</span>
            <span class="report-detail-value">{{ $device->hostname ?? '—' }}</span>
        </div>
        @if($isAdmin)
        <div class="report-detail-item">
            <span class="report-detail-label">Customer</span>
            <span class="report-detail-value">{{ $device->user?->name ?? 'Admin / Unassigned' }}</span>
        </div>
        @endif
        <div class="report-detail-item">
            <span class="report-detail-label">Location</span>
            <span class="report-detail-value">{{ $device->location ?? '—' }}</span>
        </div>
    </div>

    <div class="report-interfaces-section">
        <div class="report-section-head">
            <h4>Interfaces</h4>
            <span class="report-count-badge">{{ $deviceInterfaces->count() }}</span>
        </div>

        @if($deviceInterfaces->isEmpty())
            <div class="report-empty-state">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M4 7h16M4 12h16M4 17h10"/>
                </svg>
                <p>No interface data recorded yet.</p>
            </div>
        @else
        <div class="report-iface-list">
            @foreach($deviceInterfaces as $iface)
            @php
                $ifaceLogQuery = '?interface_name=' . urlencode($iface->interface_name);
                if (! empty($customerId)) {
                    $ifaceLogQuery .= '&user_id=' . $customerId;
                }
                $ifaceLogUrl = route('reports.device.interface.log', $device) . $ifaceLogQuery;
            @endphp
            <div class="report-iface-item">
                <div class="report-iface-main">
                    <span class="report-iface-name">{{ $iface->interface_name }}</span>
                    <div class="report-iface-status-actions">
                        <span class="status-badge {{ strtolower($iface->status) }}">{{ ucfirst($iface->status) }}</span>
                        <a href="{{ $ifaceLogUrl }}"
                           class="report-iface-log-btn"
                           target="_blank"
                           rel="noopener noreferrer"
                           title="View interface log in new tab">View Log</a>
                    </div>
                </div>
                <div class="report-iface-stats">
                    <span><strong>RX</strong> {{ \App\Support\ByteFormatter::formatBytes($iface->rx) }}</span>
                    <span><strong>TX</strong> {{ \App\Support\ByteFormatter::formatBytes($iface->tx) }}</span>
                    <span title="{{ number_format($iface->rx_packets) }} packets"><strong>RX Pkts</strong> {{ \App\Support\ByteFormatter::formatPackets($iface->rx_packets) }}</span>
                    <span title="{{ number_format($iface->tx_packets) }} packets"><strong>TX Pkts</strong> {{ \App\Support\ByteFormatter::formatPackets($iface->tx_packets) }}</span>
                    <span class="report-iface-time">{{ $iface->updated_at?->format('M d, H:i') ?? '—' }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="report-card-footer report-card-footer--preview">
        <div class="report-last-seen report-last-seen--{{ $lastSeenClass }}" title="{{ $lastSeenFull }}">
            <div class="report-last-seen-icon" aria-hidden="true">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="report-last-seen-content">
                <span class="report-last-seen-label">Last Seen</span>
                <span class="report-last-seen-value">{{ $lastSeenRelative }}</span>
                @if($lastSeen)
                <span class="report-last-seen-date">{{ $lastSeenFull }}</span>
                @endif
            </div>
            @if($lastSeenClass === 'fresh')
            <span class="report-last-seen-live" aria-label="Online">Live</span>
            @endif
        </div>
    </div>
</article>
