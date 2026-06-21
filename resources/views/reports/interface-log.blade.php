@extends('layouts.app')

@section('content')
@php
    $backUrl = route('reports.device-management') . ($customerId ? '?user_id=' . $customerId : '');
@endphp

<div class="page-header">
    <div class="page-title">
        <a href="{{ $backUrl }}" class="report-back-link">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Back to Device Management
        </a>
        <h1>{{ $device->name }} — {{ $interfaceName }} Logs</h1>
        <p>
            Interface log history for {{ $device->ip_address ?? 'this device' }}
            @if($device->service?->name)
                · {{ $device->service->name }}
            @endif
            @if($isAdmin && $device->user)
                · Customer: {{ $device->user->name }}
            @endif
        </p>
    </div>
</div>

<div class="card-table-container">
    <div class="table-toolbar">
        <div>
            <h3 style="font-size:1rem;font-weight:700;margin:0 0 0.25rem;">Interface Log (device_interface_log)</h3>
            <p style="margin:0;font-size:0.82rem;color:var(--text-muted);">
                {{ number_format($logs->count()) }} records · Click column headers to sort · Use filters below each column
            </p>
        </div>
    </div>

    <div class="table-scroll">
        <table class="data-table data-table-filterable" id="interfaceReportLogsTable">
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
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td style="font-weight:700;">{{ $log->id }}</td>
                    <td style="white-space:nowrap;color:var(--text-muted);">{{ $log->recorded_at->format('M d, Y H:i:s') }}</td>
                    <td><span class="status-badge {{ strtolower($log->status) }}">{{ ucfirst($log->status) }}</span></td>
                    <td class="cell-mono" title="{{ number_format($log->rx) }} B">{{ \App\Support\ByteFormatter::formatBytes($log->rx) }}</td>
                    <td class="cell-mono" title="{{ number_format($log->tx) }} B">{{ \App\Support\ByteFormatter::formatBytes($log->tx) }}</td>
                    <td class="cell-mono" title="{{ number_format($log->rx_packets) }} packets">{{ \App\Support\ByteFormatter::formatPackets($log->rx_packets) }}</td>
                    <td class="cell-mono" title="{{ number_format($log->tx_packets) }} packets">{{ \App\Support\ByteFormatter::formatPackets($log->tx_packets) }}</td>
                    <td class="cell-mono">{{ $log->if_index ?? '—' }}</td>
                </tr>
                @empty
                <tr class="no-sort-row">
                    <td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted);">
                        No log records yet for this interface.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
