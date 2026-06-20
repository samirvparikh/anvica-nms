@extends('layouts.app')

@section('content')
@php
    $backUrl = route('reports.index') . ($customerId ? '?user_id=' . $customerId : '');
    $exportQuery = $customerId ? '?user_id=' . $customerId : '';
@endphp

<div class="page-header">
    <div class="page-title">
        <a href="{{ $backUrl }}" class="report-back-link">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Back to Reports
        </a>
        <h1>{{ $device->name }} — Report Logs</h1>
        <p>
            Metric history for {{ $device->ip_address ?? 'this device' }}
            @if($device->service?->name)
                · {{ $device->service->name }}
            @endif
            @if($isAdmin && $device->user)
                · Customer: {{ $device->user->name }}
            @endif
        </p>
    </div>
    <div class="report-export-actions">
        <a href="{{ route('reports.device.export.excel', $device) . $exportQuery }}" class="btn-secondary report-export-btn">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="8" y1="13" x2="16" y2="13"/>
                <line x1="8" y1="17" x2="16" y2="17"/>
            </svg>
            Export to Excel
        </a>
        <a href="{{ route('reports.device.export.pdf', $device) . $exportQuery }}" class="btn-secondary report-export-btn">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <path d="M9 13h6M9 17h4"/>
            </svg>
            Export to PDF
        </a>
    </div>
</div>

<div class="card-table-container">
    <div class="table-toolbar">
        <div>
            <h3 style="font-size:1rem;font-weight:700;margin:0 0 0.25rem;">Device Metrics Log</h3>
            <p style="margin:0;font-size:0.82rem;color:var(--text-muted);">
                {{ number_format($logs->count()) }} records · Click column headers to sort · Use filters below each column
            </p>
        </div>
    </div>

    <div class="table-scroll">
        <table class="data-table data-table-filterable" id="deviceReportLogsTable">
            <thead>
                <tr>
                    <th style="width:70px;">ID</th>
                    <th>Recorded At</th>
                    <th>Metric</th>
                    <th>Value</th>
                    <th>Text</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td style="font-weight:700;">{{ $log->id }}</td>
                    <td style="white-space:nowrap;color:var(--text-muted);">{{ $log->recorded_at->format('M d, Y H:i:s') }}</td>
                    <td style="font-weight:600;">{{ $log->metric_slug }}</td>
                    <td class="cell-mono">{{ $log->metric_value }}</td>
                    <td class="cell-mono">{{ $log->metric_text ?: '—' }}</td>
                </tr>
                @empty
                <tr class="no-sort-row">
                    <td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted);">
                        No report logs recorded yet for this device.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
