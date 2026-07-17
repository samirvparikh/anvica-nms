@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Cron Log</h1>
        <p>Track scheduled task runs to verify the cron is executing.</p>
    </div>
</div>

<div class="card-table-container">
    <div class="table-toolbar">
        <div class="table-search">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="cronLogSearchInput" placeholder="Search cron logs...">
        </div>
        <form method="GET" action="{{ route('cron-logs') }}" class="cron-log-date-filter" style="display: flex; align-items: center; gap: 0.5rem;">
            <label for="cronLogDate" style="color: var(--text-muted); font-size: 0.85rem;">Date</label>
            <input type="date" id="cronLogDate" name="date" value="{{ $date }}" max="{{ now()->toDateString() }}" onchange="this.form.submit()" class="form-control" style="width: auto;">
            @if($date !== now()->toDateString())
                <a href="{{ route('cron-logs') }}" class="btn-action" title="Reset to today" style="white-space: nowrap;">Today</a>
            @endif
        </form>
    </div>

    <div class="table-scroll">
    <table class="data-table" id="cronLogsTable">
        <thead>
            <tr>
                <th style="width: 60px;">Sr. No.</th>
                <th>Command</th>
                <th>Status</th>
                <th>Message</th>
                <th style="text-align: center;">Affected</th>
                <th style="text-align: center;">Exit Code</th>
                <th>Started At</th>
                <th>Finished At</th>
                <th style="text-align: right;">Duration</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr
                    class="cron-log-row"
                    data-command="{{ strtolower($log->command) }}"
                    data-status="{{ strtolower($log->status) }}"
                    data-message="{{ strtolower($log->message) }}"
                >
                    <td style="font-weight: 700; text-align: center;">{{ $log->id }}</td>
                    <td class="cell-mono">{{ $log->command }}</td>
                    <td>
                        @php
                            $statusClass = match($log->status) {
                                \App\Models\CronLog::STATUS_SUCCESS => 'active',
                                \App\Models\CronLog::STATUS_FAILED => 'down',
                                default => 'warning',
                            };
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ ucfirst($log->status) }}</span>
                    </td>
                    <td>
                        <div class="cell-truncate" title="{{ $log->message }}">
                            {{ Str::limit($log->message, 60) ?: '—' }}
                        </div>
                    </td>
                    <td style="text-align: center;">{{ $log->affected }}</td>
                    <td style="text-align: center;">{{ $log->exit_code ?? '—' }}</td>
                    <td style="color: var(--text-muted); white-space: nowrap;">
                        {{ $log->started_at?->format('M d, Y H:i:s') ?? '—' }}
                    </td>
                    <td style="color: var(--text-muted); white-space: nowrap;">
                        {{ $log->finished_at?->format('M d, Y H:i:s') ?? '—' }}
                    </td>
                    <td class="cell-mono" style="text-align: right; white-space: nowrap;">
                        {{ $log->duration_ms !== null ? number_format($log->duration_ms) . ' ms' : '—' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 2rem 0;">
                        No cron runs logged on {{ \Illuminate\Support\Carbon::parse($date)->format('M d, Y') }}.
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
        const searchInput = document.getElementById('cronLogSearchInput');
        const tableRows = document.querySelectorAll('.cron-log-row');

        if (!searchInput || !tableRows.length) {
            return;
        }

        searchInput.addEventListener('keyup', function (e) {
            const query = e.target.value.toLowerCase().trim();

            tableRows.forEach(function (row) {
                const command = row.getAttribute('data-command') || '';
                const status = row.getAttribute('data-status') || '';
                const message = row.getAttribute('data-message') || '';

                if (command.includes(query) || status.includes(query) || message.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
</script>
@endsection
