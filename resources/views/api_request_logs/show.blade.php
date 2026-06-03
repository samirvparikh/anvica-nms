@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>API Request Log #{{ $log->id }}</h1>
        <p>Full details for this API request.</p>
    </div>
    <a href="{{ route('api-request-logs') }}" class="btn-secondary">Back to List</a>
</div>

<div class="card-table-container api-log-detail-card">
    <div class="api-log-detail-header">
        <span class="method-badge {{ strtolower($log->method) }}">{{ $log->method }}</span>
        <span class="api-log-detail-timestamp">{{ $log->created_at->format('M d, Y H:i:s') }}</span>
    </div>

    <dl class="api-log-detail-grid">
        <div class="api-log-detail-item">
            <dt>URL</dt>
            <dd class="cell-mono">{{ $log->url }}</dd>
        </div>
        <div class="api-log-detail-item">
            <dt>IP Address</dt>
            <dd class="cell-mono">{{ $log->ip_address }}</dd>
        </div>
        <div class="api-log-detail-item">
            <dt>Referer</dt>
            <dd>{{ $log->referer ?: '—' }}</dd>
        </div>
        <div class="api-log-detail-item api-log-detail-item--full">
            <dt>User Agent</dt>
            <dd class="cell-mono">{{ $log->user_agent ?: '—' }}</dd>
        </div>
    </dl>

    <div class="api-log-detail-block">
        <h3>Request Data</h3>
        <pre class="api-log-json">{{ $log->request_data ? json_encode($log->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '—' }}</pre>
    </div>

    <div class="api-log-detail-block">
        <h3>Headers</h3>
        <pre class="api-log-json">{{ $log->headers ? json_encode($log->headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '—' }}</pre>
    </div>
</div>
@endsection
