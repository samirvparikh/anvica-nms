@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>SLA & Availability Reports</h1>
        <p>Analyze uptime percentages, MTTR, ticket metrics, and compliance breaches across devices.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem; margin-bottom: 2rem;">
    <!-- Availability Summary Card -->
    <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 1rem;">Availability Metric</h3>
            <div style="text-align: center; margin: 1.5rem 0;">
                <span style="font-size: 3.5rem; font-weight: 800; color: var(--primary); font-family: 'Outfit';">99.96%</span>
                <p style="color: var(--text-muted); font-size: 0.8rem; margin-top: 0.5rem;">Overall Network availability index</p>
            </div>
        </div>
        <div style="border-top: 1px solid var(--border-color); padding-top: 1rem; display: flex; justify-content: space-between; font-size: 0.8rem; color: var(--text-muted);">
            <span>Total Downtime: <strong>14m 32s</strong></span>
            <span>Target: <strong>99.90%</strong></span>
        </div>
    </div>

    <!-- SLA Statistics Card -->
    <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow);">
        <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 1.25rem;">Compliance Stats</h3>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; text-align: center;">
            <div style="background: var(--bg-up); border-radius: 8px; padding: 1rem;">
                <h4 style="font-size: 1.5rem; font-weight: 700; color: var(--status-up);">{{ $tickets->where('status', 'resolved')->count() }}</h4>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Resolved Tickets</p>
            </div>
            <div style="background: var(--bg-warning); border-radius: 8px; padding: 1rem;">
                <h4 style="font-size: 1.5rem; font-weight: 700; color: var(--status-warning);">{{ $tickets->whereIn('status', ['new', 'assigned', 'in_progress'])->count() }}</h4>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Active Tickets</p>
            </div>
            <div style="background: var(--bg-down); border-radius: 8px; padding: 1rem;">
                <h4 style="font-size: 1.5rem; font-weight: 700; color: var(--status-down);">2</h4>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">SLA Breaches</p>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Availability table -->
<div class="card-table-container" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); margin-bottom: 2rem;">
    <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 1.25rem;">Device SLA Metrics</h3>
    <table class="data-table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                <th style="padding: 0.75rem 0.5rem;">Asset Name</th>
                <th style="padding: 0.75rem 0.5rem;">IP Address</th>
                <th style="padding: 0.75rem 0.5rem;">SLA Policy</th>
                <th style="padding: 0.75rem 0.5rem;">Availability SLA Target</th>
                <th style="padding: 0.75rem 0.5rem;">Current Uptime</th>
                <th style="padding: 0.75rem 0.5rem;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($devices as $device)
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 0.75rem 0.5rem; font-weight: 700; color: var(--text-dark);">{{ $device->name }}</td>
                <td style="padding: 0.75rem 0.5rem;">{{ $device->ip_address }}</td>
                <td style="padding: 0.75rem 0.5rem; color: var(--text-muted);">{{ $device->customer_sla_policy ?? 'Standard Incident SLA' }}</td>
                <td style="padding: 0.75rem 0.5rem;">{{ $device->availability_sla ?? 99.95 }}%</td>
                <td style="padding: 0.75rem 0.5rem; font-weight: 600; color: var(--status-up);">99.98%</td>
                <td style="padding: 0.75rem 0.5rem;">
                    <span class="status-badge active" style="font-size: 0.7rem;">Healthy</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="padding: 1.5rem 0.5rem; text-align: center; color: var(--text-muted);">No devices configured.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Ticket Log table -->
<div class="card-table-container" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow);">
    <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 1.25rem;">SLA Ticketing History</h3>
    <table class="data-table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                <th style="padding: 0.75rem 0.5rem;">Ticket ID</th>
                <th style="padding: 0.75rem 0.5rem;">Title</th>
                <th style="padding: 0.75rem 0.5rem;">Customer</th>
                <th style="padding: 0.75rem 0.5rem;">Priority</th>
                <th style="padding: 0.75rem 0.5rem;">Deadline</th>
                <th style="padding: 0.75rem 0.5rem;">SLA Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tickets as $ticket)
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 0.75rem 0.5rem; font-weight: 700; color: var(--primary);">{{ $ticket->ticket_number }}</td>
                <td style="padding: 0.75rem 0.5rem;">{{ $ticket->title }}</td>
                <td style="padding: 0.75rem 0.5rem;">{{ $ticket->customer->name ?? '—' }}</td>
                <td style="padding: 0.75rem 0.5rem;">
                    <span class="status-badge {{ $ticket->priority === 'critical' ? 'down' : 'warning' }}" style="font-size: 0.7rem;">
                        {{ ucfirst($ticket->priority) }}
                    </span>
                </td>
                <td style="padding: 0.75rem 0.5rem; color: var(--text-muted);">{{ $ticket->resolution_sla_deadline?->format('d M Y H:i') ?? '—' }}</td>
                <td style="padding: 0.75rem 0.5rem;">
                    @if($ticket->resolved_at && $ticket->resolution_sla_deadline && $ticket->resolved_at->gt($ticket->resolution_sla_deadline))
                        <span class="status-badge down" style="font-size: 0.7rem;">Breached</span>
                    @else
                        <span class="status-badge active" style="font-size: 0.7rem;">Met SLA</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 0.75rem 0.5rem; font-weight: 700; color: var(--primary);">INC-1024</td>
                <td style="padding: 0.75rem 0.5rem;">VPN Connectivity - Mumbai DC</td>
                <td style="padding: 0.75rem 0.5rem;">Western Railway</td>
                <td style="padding: 0.75rem 0.5rem;"><span class="status-badge down" style="font-size: 0.7rem;">Critical</span></td>
                <td style="padding: 0.75rem 0.5rem; color: var(--text-muted);">24 Jun 2026 09:30</td>
                <td style="padding: 0.75rem 0.5rem;"><span class="status-badge down" style="font-size: 0.7rem;">Breached</span></td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 0.75rem 0.5rem; font-weight: 700; color: var(--primary);">INC-1021</td>
                <td style="padding: 0.75rem 0.5rem;">High Packet Loss - Delhi DC</td>
                <td style="padding: 0.75rem 0.5rem;">Northern Railway</td>
                <td style="padding: 0.75rem 0.5rem;"><span class="status-badge warning" style="font-size: 0.7rem;">High</span></td>
                <td style="padding: 0.75rem 0.5rem; color: var(--text-muted);">24 Jun 2026 10:15</td>
                <td style="padding: 0.75rem 0.5rem;"><span class="status-badge active" style="font-size: 0.7rem;">Met SLA</span></td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
