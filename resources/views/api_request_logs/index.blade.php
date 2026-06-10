@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>API Request Logs</h1>
        <p>Monitor and track all API requests.</p>
    </div>
</div>

<div class="card-table-container">
    <div class="table-toolbar">
        <div class="table-search">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="apiLogSearchInput" placeholder="Search API logs...">
        </div>
    </div>

    <div class="table-scroll">
    <table class="data-table" id="apiLogsTable">
        <thead>
            <tr>
                <th class="col-actions" style="text-align: center; width: 80px;">Actions</th>
                <th style="width: 60px;">Sr. No.</th>
                <th>Method</th>
                <th>URL</th>
                <th>Route</th>
                <th>Status</th>
                <th>IP Address</th>
                <th>User Agent</th>
                <th>Request Data</th>
                <th>Headers</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $index => $log)
                @php
                    $requestDataText = json_encode($log->request_data);
                    $headersText = json_encode($log->headers);
                @endphp
                <tr
                    class="api-log-row"
                    data-method="{{ strtolower($log->method) }}"
                    data-url="{{ strtolower($log->url) }}"
                    data-ip="{{ strtolower($log->ip_address) }}"
                    data-agent="{{ strtolower($log->user_agent) }}"
                    data-request="{{ strtolower($requestDataText) }}"
                    data-headers="{{ strtolower($headersText) }}"
                >
                    <td style="text-align: center;">
                        <a href="{{ route('api-request-logs.show', $log) }}" class="btn-action view-btn" title="View log details">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline-block; vertical-align:middle;">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </a>
                    </td>
                    <td style="font-weight: 700; text-align: center;">
                        {{ $log->id }}
                    </td>
                    <td>
                        <span class="method-badge {{ strtolower($log->method) }}">
                            {{ $log->method }}
                        </span>
                    </td>
                    <td>
                        <div class="cell-truncate cell-mono" title="{{ $log->url }}">
                            {{ Str::limit($log->url, 50) }}
                        </div>
                    </td>
                    <td>
                        <span class="status-badge {{ $log->route_exists ? 'active' : 'down' }}">
                            {{ $log->route_exists ? 'Exists' : 'Not Found' }}
                        </span>
                    </td>
                    <td>
                        @if($log->response_status)
                            <span class="status-badge {{ $log->response_status < 400 ? 'active' : 'down' }}">
                                {{ $log->response_status }}
                            </span>
                        @else
                            —
                        @endif
                    </td>
                    <td class="cell-mono">{{ $log->ip_address }}</td>
                    <td>
                        <div class="cell-truncate" title="{{ $log->user_agent }}">
                            {{ $log->user_agent }}
                        </div>
                    </td>
                    <td>
                        <div class="cell-truncate cell-mono" title="{{ $requestDataText }}">
                            {{ Str::limit($requestDataText, 80) }}
                        </div>
                    </td>
                    <td>
                        <div class="cell-truncate cell-mono" title="{{ $headersText }}">
                            {{ Str::limit($headersText, 80) }}
                        </div>
                    </td>
                    <td style="color: var(--text-muted); white-space: nowrap;">
                        {{ $log->created_at->format('M d, Y H:i:s') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align: center; color: var(--text-muted); padding: 2rem 0;">
                        No API requests logged yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>

    {{ $logs->links('pagination.api-logs') }}
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('apiLogSearchInput');
        const tableRows = document.querySelectorAll('.api-log-row');

        if (!searchInput || !tableRows.length) {
            return;
        }

        searchInput.addEventListener('keyup', function (e) {
            const query = e.target.value.toLowerCase().trim();

            tableRows.forEach(function (row) {
                const method = row.getAttribute('data-method') || '';
                const url = row.getAttribute('data-url') || '';
                const ip = row.getAttribute('data-ip') || '';
                const agent = row.getAttribute('data-agent') || '';
                const request = row.getAttribute('data-request') || '';
                const headers = row.getAttribute('data-headers') || '';

                if (
                    method.includes(query) ||
                    url.includes(query) ||
                    ip.includes(query) ||
                    agent.includes(query) ||
                    request.includes(query) ||
                    headers.includes(query)
                ) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
</script>
@endsection
