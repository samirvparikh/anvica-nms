@php
    $metricLevel = $metricLevel ?? function (?float $value, float $warn = 70, float $crit = 90): string {
        if ($value === null) {
            return 'neutral';
        }
        if ($value >= $crit) {
            return 'critical';
        }
        if ($value >= $warn) {
            return 'warning';
        }

        return 'healthy';
    };
    $showInterfaceList = $showInterfaceList ?? false;
    $deviceColspan = $isAdmin ? 11 : 10;
@endphp

<div class="card-table-container" style="margin-bottom:1.5rem;">
    <div class="table-toolbar">
        <h3 style="font-size:1rem;font-weight:700;margin:0;">Devices</h3>
    </div>

    <div class="table-scroll">
        <table class="data-table" id="reportsDevicesTable">
            <thead>
                <tr>
                    <th>Device</th>
                    <th>IP Address</th>
                    @if($isAdmin)<th>Customer</th>@endif
                    <th>Service</th>
                    <th>Health</th>
                    <th>Last Seen</th>
                    <th>CPU %</th>
                    <th>RAM %</th>
                    <th>Latency (ms)</th>
                    <th>Interfaces</th>
                    <th class="col-actions" data-no-sort="true" style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($devices as $device)
                @php
                    $m = $latestMetrics[$device->id] ?? [];
                    $health = $deviceHealth[$device->id] ?? 'Down';
                    $healthClass = strtolower($health);
                    $cpuMetric = $m['CPU'] ?? $m['cpu'] ?? null;
                    $cpuPct = $cpuMetric ? (float) $cpuMetric->metric_value : null;
                    $ramUsed = (float) (($m['Ram_Used'] ?? $m['ram_uses'] ?? null)?->metric_value ?? 0);
                    $ramTotal = (float) (($m['Total_Ram'] ?? $m['total_ram'] ?? null)?->metric_value ?? 0);
                    $ramPct = isset($m['ram']) ? (float) $m['ram']->metric_value : ($ramTotal > 0 ? round(($ramUsed / $ramTotal) * 100, 1) : null);
                    $latencyMetric = null;
                    $latencySlug = null;
                    foreach (['Latency', 'latency', 'Ping_Latency', 'ping_latency', 'Ping_Time', 'ping_time'] as $latencyKey) {
                        if (isset($m[$latencyKey])) {
                            $latencyMetric = $m[$latencyKey];
                            $latencySlug = $latencyKey;
                            break;
                        }
                    }
                    $latencyDisplay = $latencyMetric
                        ? \App\Support\LatencyFormatter::formatMilliseconds($latencyMetric->metric_value, $latencyMetric->metric_text, $latencySlug)
                        : '—';
                    $deviceInterfaces = $scopedInterfaces->get($device->id, collect());
                    $reportUrl = route('reports.device.show', $device) . ($customerId ? '?user_id=' . $customerId : '');
                @endphp
                <tr class="device-data-row">
                    <td style="font-weight:700;">{{ $device->name }}</td>
                    <td class="cell-mono">{{ $device->ip_address ?? '—' }}</td>
                    @if($isAdmin)
                    <td>{{ $device->user?->name ?? 'Admin / Unassigned' }}</td>
                    @endif
                    <td>{{ $device->service?->name ?? $device->type ?? '—' }}</td>
                    <td><span class="status-badge {{ $healthClass }}">{{ $health }}</span></td>
                    <td style="color:var(--text-muted);white-space:nowrap;">{{ $device->last_seen?->format('M d, Y H:i') ?? '—' }}</td>
                    <td>{{ $cpuPct !== null ? number_format($cpuPct, 1) : '—' }}</td>
                    <td>{{ $ramPct !== null ? number_format($ramPct, 1) : '—' }}</td>
                    <td class="cell-mono">{{ $latencyDisplay }}</td>
                    <td>
                        @if($deviceInterfaces->count() > 0)
                        <button type="button"
                                class="device-interfaces-toggle"
                                data-target="device-ifaces-{{ $device->id }}"
                                aria-expanded="false"
                                title="Show interface list">
                            <span>{{ $deviceInterfaces->count() }}</span>
                            <svg class="device-interfaces-toggle__icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </button>
                        @else
                        0
                        @endif
                    </td>
                    <td class="col-actions">
                        <div class="table-actions">
                            <button type="button"
                                    class="btn-action view-btn preview-report-btn"
                                    title="Preview device report"
                                    data-device-name="{{ $device->name }}"
                                    data-card-target="report-card-{{ $device->id }}">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                            <a href="{{ $reportUrl }}"
                               class="btn-action edit-btn report-open-btn"
                               title="Open full report">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <line x1="16" y1="13" x2="8" y2="13"/>
                                    <line x1="16" y1="17" x2="8" y2="17"/>
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @if($showInterfaceList)
                <tr class="device-interfaces-row no-sort-row" id="device-ifaces-{{ $device->id }}" hidden>
                    <td colspan="{{ $deviceColspan }}">
                        <div class="device-interfaces-panel">
                            <div class="device-interfaces-panel__title">Interfaces — click a value for snapshot · eye icon for log history</div>
                            @if($deviceInterfaces->isNotEmpty())
                            <div class="table-scroll device-interfaces-scroll">
                                <table class="data-table data-table--nested">
                                    <thead>
                                        <tr>
                                            <th>Interface</th>
                                            <th>Status</th>
                                            <th>RX</th>
                                            <th>TX</th>
                                            <th>RX Packets</th>
                                            <th>TX Packets</th>
                                            <th>Updated</th>
                                            <th class="col-actions" data-no-sort="true" data-no-filter="true" style="text-align:right;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($deviceInterfaces as $iface)
                                        @php
                                            $ifacePayload = [
                                                'device' => $device->name,
                                                'device_ip' => $device->ip_address ?? '—',
                                                'interface' => $iface->interface_name,
                                                'status' => ucfirst($iface->status),
                                                'status_class' => strtolower($iface->status),
                                                'rx' => (int) $iface->rx,
                                                'rx_fmt' => \App\Support\ByteFormatter::formatBytes($iface->rx),
                                                'tx' => (int) $iface->tx,
                                                'tx_fmt' => \App\Support\ByteFormatter::formatBytes($iface->tx),
                                                'rx_packets' => (int) $iface->rx_packets,
                                                'rx_packets_fmt' => \App\Support\ByteFormatter::formatPackets($iface->rx_packets),
                                                'tx_packets' => (int) $iface->tx_packets,
                                                'tx_packets_fmt' => \App\Support\ByteFormatter::formatPackets($iface->tx_packets),
                                                'updated' => $iface->updated_at?->format('M d, Y H:i:s') ?? '—',
                                                'created' => $iface->created_at?->format('M d, Y H:i:s') ?? '—',
                                            ];
                                            $ifaceLogsUrl = route('reports.device.interface.logs', $device)
                                                . '?interface_name=' . urlencode($iface->interface_name)
                                                . ($customerId ? '&user_id=' . $customerId : '');
                                        @endphp
                                        <tr>
                                            <td>
                                                <button type="button"
                                                        class="interface-data-btn interface-data-btn--name"
                                                        data-interface='@json($ifacePayload)'
                                                        data-field="all">
                                                    {{ $iface->interface_name }}
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button"
                                                        class="interface-data-btn"
                                                        data-interface='@json($ifacePayload)'
                                                        data-field="status">
                                                    <span class="status-badge {{ strtolower($iface->status) }}">{{ ucfirst($iface->status) }}</span>
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button"
                                                        class="interface-data-btn"
                                                        data-interface='@json($ifacePayload)'
                                                        data-field="rx">
                                                    {{ $ifacePayload['rx_fmt'] }}
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button"
                                                        class="interface-data-btn"
                                                        data-interface='@json($ifacePayload)'
                                                        data-field="tx">
                                                    {{ $ifacePayload['tx_fmt'] }}
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button"
                                                        class="interface-data-btn"
                                                        data-interface='@json($ifacePayload)'
                                                        data-field="rx_packets">
                                                    {{ $ifacePayload['rx_packets_fmt'] }}
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button"
                                                        class="interface-data-btn"
                                                        data-interface='@json($ifacePayload)'
                                                        data-field="tx_packets">
                                                    {{ $ifacePayload['tx_packets_fmt'] }}
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button"
                                                        class="interface-data-btn interface-data-btn--muted"
                                                        data-interface='@json($ifacePayload)'
                                                        data-field="updated">
                                                    {{ $iface->updated_at?->format('M d, Y H:i') ?? '—' }}
                                                </button>
                                            </td>
                                            <td class="col-actions" style="text-align:right;">
                                                <button type="button"
                                                        class="btn-action view-btn interface-logs-btn"
                                                        title="View interface log history"
                                                        data-url="{{ $ifaceLogsUrl }}"
                                                        data-device-name="{{ $device->name }}"
                                                        data-interface-name="{{ $iface->interface_name }}">
                                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                        <circle cx="12" cy="12" r="3"/>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <p class="device-interfaces-empty">No interface data for this device yet.</p>
                            @endif
                        </div>
                    </td>
                </tr>
                @endif
                @empty
                <tr class="no-sort-row">
                    <td colspan="{{ $deviceColspan }}" style="text-align:center;padding:2rem;color:var(--text-muted);">
                        @if($isAdmin)
                            @if($selectedCustomer ?? null)
                                No devices found for {{ $selectedCustomer->name }}.
                            @else
                                No devices found in the system.
                            @endif
                        @else
                            No devices are assigned to your account yet.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@foreach($devices as $device)
@php
    $m = $latestMetrics[$device->id] ?? [];
    $health = $deviceHealth[$device->id] ?? 'Down';
    $healthClass = strtolower($health);
    $cpuMetric = $m['CPU'] ?? $m['cpu'] ?? null;
    $cpuPct = $cpuMetric ? (float) $cpuMetric->metric_value : null;
    $ramUsed = (float) (($m['Ram_Used'] ?? $m['ram_uses'] ?? null)?->metric_value ?? 0);
    $ramTotal = (float) (($m['Total_Ram'] ?? $m['total_ram'] ?? null)?->metric_value ?? 0);
    $ramPct = isset($m['ram']) ? (float) $m['ram']->metric_value : ($ramTotal > 0 ? round(($ramUsed / $ramTotal) * 100, 1) : null);
    $diskMetric = $m['disk'] ?? null;
    $diskPct = $diskMetric ? (float) $diskMetric->metric_value : null;
    $tempMetric = $m['CPU_Temp'] ?? $m['MB_Temp'] ?? $m['Board_Temp'] ?? $m['temperature'] ?? null;
    $tempVal = $tempMetric ? (float) $tempMetric->metric_value : null;
    $deviceInterfaces = $scopedInterfaces->get($device->id, collect());

    $lastSeen = $device->last_seen;
    $lastSeenClass = 'never';
    $lastSeenRelative = 'No activity yet';
    $lastSeenFull = 'Device has not reported in';

    if ($lastSeen) {
        $lastSeenFull = $lastSeen->format('M d, Y') . ' at ' . $lastSeen->format('h:i A');
        $lastSeenRelative = $lastSeen->diffForHumans(null, true) . ' ago';
        $minutesAgo = $lastSeen->diffInMinutes(now());

        if ($minutesAgo <= 5) {
            $lastSeenClass = 'fresh';
            $lastSeenRelative = $minutesAgo < 1 ? 'Just now' : $lastSeenRelative;
        } elseif ($minutesAgo <= 60) {
            $lastSeenClass = 'recent';
        } else {
            $lastSeenClass = 'stale';
        }
    }
@endphp
<div id="report-card-{{ $device->id }}" class="report-card-modal-source" hidden>
    @include('reports.partials.device-card')
</div>
@endforeach

@if($showInterfaceList)
<div class="modal-overlay" id="interfaceLogsModal">
    <div class="modal-card modal-card-wide interface-logs-modal">
        <div class="modal-header">
            <h3 id="interfaceLogsTitle">Interface Logs</h3>
            <button type="button" class="modal-close" id="closeInterfaceLogsModal">&times;</button>
        </div>
        <div class="modal-body">
            <p id="interfaceLogsMeta" class="interface-logs-meta">Loading interface log history…</p>
            <div class="table-scroll">
                <table class="data-table data-table-filterable" id="interfaceLogsTable">
                    <thead>
                        <tr>
                            <th style="width:70px;">ID</th>
                            <th>Recorded At</th>
                            <th>Status</th>
                            <th>RX</th>
                            <th>TX</th>
                            <th>RX Packets</th>
                            <th>TX Packets</th>
                            <th>If Index</th>
                        </tr>
                    </thead>
                    <tbody id="interfaceLogsBody">
                        <tr class="no-sort-row">
                            <td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted);">Loading…</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" id="cancelInterfaceLogsModal">Close</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="interfaceDetailModal">
    <div class="modal-card modal-card-wide interface-detail-modal">
        <div class="modal-header">
            <h3 id="interfaceDetailTitle">Interface Data</h3>
            <button type="button" class="modal-close" id="closeInterfaceDetailModal">&times;</button>
        </div>
        <div class="modal-body" id="interfaceDetailBody"></div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" id="cancelInterfaceDetailModal">Close</button>
        </div>
    </div>
</div>
@endif

<div class="modal-overlay" id="reportPreviewModal">
    <div class="modal-card modal-card-wide report-preview-modal">
        <div class="modal-header">
            <h3 id="reportPreviewTitle">Device Report Preview</h3>
            <button type="button" class="modal-close" id="closeReportPreviewModal">&times;</button>
        </div>
        <div class="modal-body" id="reportPreviewBody"></div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" id="cancelReportPreviewModal">Close</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    @if($showInterfaceList)
    const ifaceModal = document.getElementById('interfaceDetailModal');
    const ifaceTitle = document.getElementById('interfaceDetailTitle');
    const ifaceBody = document.getElementById('interfaceDetailBody');
    const ifaceCloseBtn = document.getElementById('closeInterfaceDetailModal');
    const ifaceCancelBtn = document.getElementById('cancelInterfaceDetailModal');

    function openIfaceModal() {
        if (ifaceModal) ifaceModal.classList.add('open');
    }

    function closeIfaceModal() {
        if (!ifaceModal) return;
        ifaceModal.classList.remove('open');
        if (ifaceBody) ifaceBody.innerHTML = '';
    }

    function formatNumber(value) {
        return Number(value).toLocaleString();
    }

    function renderDetailRow(label, value, mono) {
        return '<div class="interface-detail-item">'
            + '<span class="interface-detail-label">' + label + '</span>'
            + '<span class="interface-detail-value' + (mono ? ' cell-mono' : '') + '">' + value + '</span>'
            + '</div>';
    }

    function renderInterfaceDetail(data, field) {
        const fieldMap = {
            rx: {
                title: 'RX Bytes',
                rows: [
                    ['Display', data.rx_fmt, false],
                    ['Raw bytes', formatNumber(data.rx) + ' B', true],
                ],
            },
            tx: {
                title: 'TX Bytes',
                rows: [
                    ['Display', data.tx_fmt, false],
                    ['Raw bytes', formatNumber(data.tx) + ' B', true],
                ],
            },
            rx_packets: {
                title: 'RX Packets',
                rows: [
                    ['Display', data.rx_packets_fmt, false],
                    ['Raw count', formatNumber(data.rx_packets), true],
                ],
            },
            tx_packets: {
                title: 'TX Packets',
                rows: [
                    ['Display', data.tx_packets_fmt, false],
                    ['Raw count', formatNumber(data.tx_packets), true],
                ],
            },
            status: {
                title: 'Status',
                rows: [
                    ['Status', '<span class="status-badge ' + data.status_class + '">' + data.status + '</span>', false],
                ],
            },
            updated: {
                title: 'Last Updated',
                rows: [
                    ['Updated at', data.updated, false],
                    ['First recorded', data.created, false],
                ],
            },
        };

        if (field === 'all' || !fieldMap[field]) {
            ifaceTitle.textContent = data.device + ' — ' + data.interface;
            ifaceBody.innerHTML = '<div class="interface-detail-grid">'
                + renderDetailRow('Device', data.device, false)
                + renderDetailRow('IP Address', data.device_ip, true)
                + renderDetailRow('Interface', data.interface, false)
                + renderDetailRow('Status', '<span class="status-badge ' + data.status_class + '">' + data.status + '</span>', false)
                + renderDetailRow('RX', data.rx_fmt + ' (' + formatNumber(data.rx) + ' B)', true)
                + renderDetailRow('TX', data.tx_fmt + ' (' + formatNumber(data.tx) + ' B)', true)
                + renderDetailRow('RX Packets', data.rx_packets_fmt + ' (' + formatNumber(data.rx_packets) + ')', true)
                + renderDetailRow('TX Packets', data.tx_packets_fmt + ' (' + formatNumber(data.tx_packets) + ')', true)
                + renderDetailRow('Updated', data.updated, false)
                + renderDetailRow('Created', data.created, false)
                + '</div>';
            return;
        }

        const config = fieldMap[field];
        ifaceTitle.textContent = data.device + ' — ' + data.interface + ' (' + config.title + ')';
        ifaceBody.innerHTML = '<div class="interface-detail-grid">'
            + renderDetailRow('Device', data.device, false)
            + renderDetailRow('Interface', data.interface, false)
            + config.rows.map(function (row) { return renderDetailRow(row[0], row[1], row[2]); }).join('')
            + '</div>';
    }

    document.querySelectorAll('.device-interfaces-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const target = document.getElementById(this.getAttribute('data-target'));
            if (!target) return;

            const isOpen = !target.hidden;
            target.hidden = isOpen;
            this.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            this.classList.toggle('is-open', !isOpen);
        });
    });

    document.querySelectorAll('.interface-data-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            let data;
            try {
                data = JSON.parse(this.getAttribute('data-interface'));
            } catch (e) {
                return;
            }

            renderInterfaceDetail(data, this.getAttribute('data-field') || 'all');
            openIfaceModal();
        });
    });

    if (ifaceCloseBtn) ifaceCloseBtn.addEventListener('click', closeIfaceModal);
    if (ifaceCancelBtn) ifaceCancelBtn.addEventListener('click', closeIfaceModal);
    if (ifaceModal) {
        ifaceModal.addEventListener('click', function (e) {
            if (e.target === ifaceModal) closeIfaceModal();
        });
    }

    const ifaceLogsModal = document.getElementById('interfaceLogsModal');
    const ifaceLogsTitle = document.getElementById('interfaceLogsTitle');
    const ifaceLogsMeta = document.getElementById('interfaceLogsMeta');
    const ifaceLogsBody = document.getElementById('interfaceLogsBody');
    const ifaceLogsCloseBtn = document.getElementById('closeInterfaceLogsModal');
    const ifaceLogsCancelBtn = document.getElementById('cancelInterfaceLogsModal');
    let ifaceLogsTableReady = false;

    function openIfaceLogsModal() {
        if (ifaceLogsModal) ifaceLogsModal.classList.add('open');
    }

    function closeIfaceLogsModal() {
        if (!ifaceLogsModal) return;
        ifaceLogsModal.classList.remove('open');
    }

    function renderInterfaceLogsRows(logs) {
        if (!logs.length) {
            return '<tr class="no-sort-row"><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted);">No log records yet for this interface.</td></tr>';
        }

        return logs.map(function (log) {
            return '<tr>'
                + '<td style="font-weight:700;">' + log.id + '</td>'
                + '<td style="white-space:nowrap;color:var(--text-muted);">' + log.recorded_at + '</td>'
                + '<td><span class="status-badge ' + log.status_class + '">' + log.status + '</span></td>'
                + '<td class="cell-mono" title="' + Number(log.rx).toLocaleString() + ' B">' + log.rx_fmt + '</td>'
                + '<td class="cell-mono" title="' + Number(log.tx).toLocaleString() + ' B">' + log.tx_fmt + '</td>'
                + '<td class="cell-mono" title="' + Number(log.rx_packets).toLocaleString() + ' packets">' + log.rx_packets_fmt + '</td>'
                + '<td class="cell-mono" title="' + Number(log.tx_packets).toLocaleString() + ' packets">' + log.tx_packets_fmt + '</td>'
                + '<td class="cell-mono">' + (log.if_index || '—') + '</td>'
                + '</tr>';
        }).join('');
    }

    function setupInterfaceLogsTable() {
        const table = document.getElementById('interfaceLogsTable');
        if (!table) return;

        if (!ifaceLogsTableReady) {
            if (window.initDataTableSort) window.initDataTableSort(table);
            if (window.initDataTableFilter) window.initDataTableFilter(table);
            ifaceLogsTableReady = true;
            return;
        }

        if (window.resetDataTableFilters) window.resetDataTableFilters(table);
        if (window.resetDataTableSort) window.resetDataTableSort(table);
    }

    document.querySelectorAll('.interface-logs-btn').forEach(function (btn) {
        btn.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            const url = this.getAttribute('data-url');
            const deviceName = this.getAttribute('data-device-name');
            const interfaceName = this.getAttribute('data-interface-name');

            if (!url || !ifaceLogsBody) return;

            ifaceLogsTitle.textContent = deviceName + ' — ' + interfaceName + ' Logs';
            ifaceLogsMeta.textContent = 'Loading interface log history…';
            ifaceLogsBody.innerHTML = '<tr class="no-sort-row"><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted);">Loading…</td></tr>';
            openIfaceLogsModal();

            fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Failed to load interface logs');
                    }
                    return response.json();
                })
                .then(function (data) {
                    const logs = data.logs || [];
                    ifaceLogsMeta.textContent = logs.length.toLocaleString() + ' records · Click column headers to sort · Use filters below each column';
                    ifaceLogsBody.innerHTML = renderInterfaceLogsRows(logs);
                    setupInterfaceLogsTable();
                })
                .catch(function () {
                    ifaceLogsMeta.textContent = 'Unable to load interface logs.';
                    ifaceLogsBody.innerHTML = '<tr class="no-sort-row"><td colspan="8" style="text-align:center;padding:2rem;color:var(--status-down);">Unable to load interface log history.</td></tr>';
                });
        });
    });

    if (ifaceLogsCloseBtn) ifaceLogsCloseBtn.addEventListener('click', closeIfaceLogsModal);
    if (ifaceLogsCancelBtn) ifaceLogsCancelBtn.addEventListener('click', closeIfaceLogsModal);
    if (ifaceLogsModal) {
        ifaceLogsModal.addEventListener('click', function (e) {
            if (e.target === ifaceLogsModal) closeIfaceLogsModal();
        });
    }
    @endif

    const modal = document.getElementById('reportPreviewModal');
    if (!modal) return;

    const title = document.getElementById('reportPreviewTitle');
    const body = document.getElementById('reportPreviewBody');
    const closeBtn = document.getElementById('closeReportPreviewModal');
    const cancelBtn = document.getElementById('cancelReportPreviewModal');

    function openModal() {
        modal.classList.add('open');
    }

    function closeModal() {
        modal.classList.remove('open');
        body.innerHTML = '';
    }

    document.querySelectorAll('.preview-report-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-card-target');
            const deviceName = this.getAttribute('data-device-name');
            const source = document.getElementById(targetId);

            if (!source) {
                return;
            }

            title.textContent = deviceName + ' — Device Preview';
            body.innerHTML = source.innerHTML;
            openModal();
        });
    });

    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            closeModal();
        }
    });
});
</script>
