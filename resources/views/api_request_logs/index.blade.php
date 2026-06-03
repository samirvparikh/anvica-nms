@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-header-content">
        <h1>API Request Logs</h1>
        <p style="color: #64748b; margin-top: 0.5rem;">Monitor and track all API requests</p>
    </div>
</div>

<style>
    .table-container {
        background-color: var(--bg-content);
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .api-logs-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
    }

    .api-logs-table thead {
        background-color: var(--bg-header);
        border-bottom: 1px solid var(--border-color);
    }

    .api-logs-table th {
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .api-logs-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--border-color);
    }

    .api-logs-table tbody tr:hover {
        background-color: rgba(148, 163, 184, 0.05);
    }

    .method-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    .method-get {
        background-color: rgba(59, 130, 246, 0.1);
        color: #2563eb;
    }

    .method-post {
        background-color: rgba(34, 197, 94, 0.1);
        color: #16a34a;
    }

    .method-put {
        background-color: rgba(168, 85, 247, 0.1);
        color: #a855f7;
    }

    .method-delete {
        background-color: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    .method-patch {
        background-color: rgba(249, 115, 22, 0.1);
        color: #f97316;
    }

    .url-cell {
        font-family: 'Courier New', monospace;
        font-size: 0.8rem;
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ip-cell {
        font-family: 'Courier New', monospace;
        color: #64748b;
    }

    .timestamp {
        color: #64748b;
        white-space: nowrap;
    }

    .no-data {
        text-align: center;
        padding: 3rem 1rem;
        color: #64748b;
    }

    .pagination-container {
        padding: 1.5rem;
        border-top: 1px solid var(--border-color);
    }

    .pagination {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .pagination a,
    .pagination span {
        padding: 0.5rem 0.75rem;
        border-radius: 4px;
        border: 1px solid var(--border-color);
        text-decoration: none;
        color: var(--text-primary);
        font-size: 0.85rem;
    }

    .pagination a:hover {
        background-color: var(--bg-header);
    }

    .pagination .active span {
        background-color: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }

    .pagination .disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .data-display {
        background-color: #f9fafb;
        padding: 0.75rem;
        border-radius: 4px;
        font-family: 'Courier New', monospace;
        font-size: 0.8rem;
        white-space: pre-wrap;
        word-wrap: break-word;
        word-break: break-word;
        line-height: 1.4;
    }
</style>

<div class="table-container">
    @if($logs->count() > 0)
        <table class="api-logs-table">
            <thead>
                <tr>
                    <th>Method</th>
                    <!-- <th>URL</th> -->
                    <th>IP Address</th>
                    <th>User Agent</th>
                    <th>Request Data</th>
                    <th>Headers</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td>
                            <span class="method-badge method-{{ strtolower($log->method) }}">
                                {{ $log->method }}
                            </span>
                        </td>
                        <!-- <td>
                            <div class="url-cell" title="{{ $log->url }}">
                                {{ $log->url }}
                            </div>
                        </td> -->
                        <td>
                            <span class="ip-cell">{{ $log->ip_address }}</span>
                        </td>
                        <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $log->user_agent }}">
                            {{ $log->user_agent }}
                        </td>
                        <td>
                            <div class="data-display">{{ json_encode($log->request_data, JSON_PRETTY_PRINT) }}</div>
                        </td>
                        <td>
                            <div class="data-display">{{ json_encode($log->headers, JSON_PRETTY_PRINT) }}</div>
                        </td>
                        <td>
                            <span class="timestamp">{{ $log->created_at->format('M d, Y H:i:s') }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($logs->hasPages())
            <div class="pagination-container">
                {{ $logs->links() }}
            </div>
        @endif
    @else
        <div class="no-data">
            <p>No API requests logged yet.</p>
        </div>
    @endif
</div>
@endsection
