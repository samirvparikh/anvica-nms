<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $device->name }} — Device Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 24px;
        }

        h1 {
            font-size: 18px;
            margin: 0 0 4px;
        }

        .meta {
            color: #64748b;
            margin-bottom: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background: #f8fafc;
            font-size: 10px;
            text-transform: uppercase;
        }

        tr:nth-child(even) td {
            background: #fbfcfe;
        }

        .footer {
            margin-top: 14px;
            color: #64748b;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <h1>{{ $device->name }} — Device Report</h1>
    <div class="meta">
        IP: {{ $device->ip_address ?? '—' }}
        · Service: {{ $device->service?->name ?? $device->type ?? '—' }}
        · Customer: {{ $device->user?->name ?? 'Unassigned' }}
        · Generated: {{ $generatedAt->format('M d, Y H:i:s') }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:40px;">ID</th>
                <th>Recorded At</th>
                <th>Metric</th>
                <th>Value</th>
                <th>Text</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td>{{ $log->id }}</td>
                <td>{{ $log->recorded_at->format('Y-m-d H:i:s') }}</td>
                <td>{{ $log->metric_slug }}</td>
                <td>{{ $log->metric_value }}</td>
                <td>{{ $log->metric_text ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center;">No report logs recorded.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Total records: {{ number_format($logs->count()) }} · Anvica NMS
    </div>
</body>
</html>
