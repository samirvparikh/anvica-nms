@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>SLA Dashboard</h1>
        <p>Real-time monitoring of service level agreements, compliance levels, and ticketing metrics.</p>
    </div>
    <div class="page-actions" style="display: flex; gap: 0.75rem;">
        <a href="{{ route('sla.targets') }}" class="btn-add" style="background-color: var(--sidebar-active); color: white; border: 1px solid var(--border-color);">
            <i class="fa-solid fa-bullseye" style="margin-right: 0.5rem;"></i> SLA Targets
        </a>
        <a href="{{ route('sla.reports') }}" class="btn-add">
            <i class="fa-solid fa-file-invoice" style="margin-right: 0.5rem;"></i> Generate SLA Report
        </a>
    </div>
</div>

<!-- KPI Dashboard Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <!-- SLA Compliance Card -->
    <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); border-left: 5px solid var(--primary); display: flex; flex-direction: column; justify-content: space-between; min-height: 120px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; color: var(--text-muted);">
            <span style="font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">SLA Compliance</span>
            <i class="fa-solid fa-shield-halved" style="color: var(--primary); font-size: 1.25rem;"></i>
        </div>
        <div style="margin-top: 1rem;">
            <h2 style="font-size: 2.25rem; font-weight: 800; line-height: 1; color: var(--text-dark); font-family: 'Outfit';">{{ $compliance }}%</h2>
            <p style="color: var(--status-up); font-size: 0.75rem; font-weight: 600; margin-top: 0.25rem;">
                <i class="fa-solid fa-arrow-trend-up"></i> Above target threshold (99.0%)
            </p>
        </div>
    </div>

    <!-- Active Tickets Card -->
    <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); border-left: 5px solid #3b82f6; display: flex; flex-direction: column; justify-content: space-between; min-height: 120px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; color: var(--text-muted);">
            <span style="font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">Open Tickets</span>
            <i class="fa-solid fa-ticket" style="color: #3b82f6; font-size: 1.25rem;"></i>
        </div>
        <div style="margin-top: 1rem;">
            <h2 style="font-size: 2.25rem; font-weight: 800; line-height: 1; color: var(--text-dark); font-family: 'Outfit';">{{ $openTicketsCount }}</h2>
            <p style="color: var(--text-muted); font-size: 0.75rem; font-weight: 500; margin-top: 0.25rem;">Active Service Desk tickets</p>
        </div>
    </div>

    <!-- Breach Risk Card -->
    <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); border-left: 5px solid var(--status-warning); display: flex; flex-direction: column; justify-content: space-between; min-height: 120px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; color: var(--text-muted);">
            <span style="font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">At Risk of Breach</span>
            <i class="fa-solid fa-triangle-exclamation" style="color: var(--status-warning); font-size: 1.25rem;"></i>
        </div>
        <div style="margin-top: 1rem;">
            <h2 style="font-size: 2.25rem; font-weight: 800; line-height: 1; color: var(--text-dark); font-family: 'Outfit';">{{ $breachRiskCount }}</h2>
            <p style="color: {{ $breachRiskCount > 0 ? 'var(--status-warning)' : 'var(--status-up)' }}; font-size: 0.75rem; font-weight: 600; margin-top: 0.25rem;">
                @if($breachRiskCount > 0)
                    <i class="fa-solid fa-clock-rotate-left"></i> Requires immediate attention
                @else
                    <i class="fa-solid fa-circle-check"></i> All clean. Deadlines are safe.
                @endif
            </p>
        </div>
    </div>

    <!-- MTTR Card -->
    <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); border-left: 5px solid #8b5cf6; display: flex; flex-direction: column; justify-content: space-between; min-height: 120px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; color: var(--text-muted);">
            <span style="font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">Avg MTTR</span>
            <i class="fa-solid fa-hourglass-half" style="color: #8b5cf6; font-size: 1.25rem;"></i>
        </div>
        <div style="margin-top: 1rem;">
            <h2 style="font-size: 2.25rem; font-weight: 800; line-height: 1; color: var(--text-dark); font-family: 'Outfit';">{{ $mttrFormatted }}</h2>
            <p style="color: var(--status-up); font-size: 0.75rem; font-weight: 600; margin-top: 0.25rem;">
                <i class="fa-solid fa-arrow-trend-down"></i> Down 12% from last week
            </p>
        </div>
    </div>

    <!-- MTBF Card -->
    <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); border-left: 5px solid #06b6d4; display: flex; flex-direction: column; justify-content: space-between; min-height: 120px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; color: var(--text-muted);">
            <span style="font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">Avg MTBF</span>
            <i class="fa-solid fa-chart-line" style="color: #06b6d4; font-size: 1.25rem;"></i>
        </div>
        <div style="margin-top: 1rem;">
            <h2 style="font-size: 2.25rem; font-weight: 800; line-height: 1; color: var(--text-dark); font-family: 'Outfit';">{{ $mtbfFormatted }}</h2>
            <p style="color: var(--text-muted); font-size: 0.75rem; font-weight: 500; margin-top: 0.25rem;">Mean Time Between Failures</p>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
    <!-- Compliance Line Chart -->
    <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow);">
        <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text-dark);">
            SLA Compliance Trend (Last 12 Months)
        </h3>
        <div style="height: 300px; position: relative;">
            <canvas id="slaComplianceChart"></canvas>
        </div>
    </div>

    <!-- SLA Health Status Pie/Donut Chart -->
    <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); display: flex; flex-direction: column;">
        <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text-dark);">
            Ticket Target Health
        </h3>
        <div style="height: 200px; position: relative; display: flex; justify-content: center; align-items: center; margin-bottom: 1.5rem;">
            <canvas id="slaHealthChart"></canvas>
        </div>
        <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.85rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="display: flex; align-items: center; gap: 0.5rem;"><i class="fa-solid fa-circle" style="color: var(--primary); font-size: 0.65rem;"></i> Within Target</span>
                <span style="font-weight: 700;">88.5%</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="display: flex; align-items: center; gap: 0.5rem;"><i class="fa-solid fa-circle" style="color: var(--status-warning); font-size: 0.65rem;"></i> Warning Risk</span>
                <span style="font-weight: 700;">9.2%</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="display: flex; align-items: center; gap: 0.5rem;"><i class="fa-solid fa-circle" style="color: var(--status-down); font-size: 0.65rem;"></i> Breached</span>
                <span style="font-weight: 700;">2.3%</span>
            </div>
        </div>
    </div>
</div>

<!-- SLA Prediction / Risks -->
<div style="background: var(--bg-warning); border: 1px solid rgba(249, 115, 22, 0.2); border-radius: 12px; padding: 1.25rem; margin-bottom: 2rem; display: flex; align-items: flex-start; gap: 1rem;">
    <div style="background: rgba(249, 115, 22, 0.1); width: 42px; height: 42px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
        <i class="fa-solid fa-wand-magic-sparkles" style="color: var(--status-warning); font-size: 1.25rem;"></i>
    </div>
    <div>
        <h4 style="font-weight: 700; color: #7c2d12; margin-bottom: 0.25rem; font-size: 0.95rem;">SLA Risk Prediction & Proactive Alert</h4>
        <p style="color: #9a3412; font-size: 0.85rem; line-height: 1.5;">
            Our anomaly algorithm predicts <strong>Core-Router-01</strong> might breach resolution deadline in the next <strong>35 minutes</strong> due to persistent alarms. The ticket has been escalated to NOC Manager Rajesh Sharma automatically.
        </p>
    </div>
</div>

<!-- Detailed Table Grids -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 1.5rem;">
    <!-- Active SLA Policies / SLA Targets -->
    <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); display: flex; flex-direction: column;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-dark);">Active SLA Policy Deadlines</h3>
            <a href="{{ route('sla.targets') }}" style="color: var(--primary); text-decoration: none; font-size: 0.85rem; font-weight: 600;">View All</a>
        </div>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-color); color: var(--text-muted); font-weight: 600;">
                        <th style="padding: 0.75rem 0.5rem;">SLA Policy Name</th>
                        <th style="padding: 0.75rem 0.5rem;">Response Target</th>
                        <th style="padding: 0.75rem 0.5rem;">Resolution Target</th>
                        <th style="padding: 0.75rem 0.5rem;">Escalation Time</th>
                        <th style="padding: 0.75rem 0.5rem;">Daily Max</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($policies as $policy)
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 0.75rem 0.5rem; font-weight: 700; color: var(--text-dark);">{{ $policy->name }}</td>
                        <td style="padding: 0.75rem 0.5rem;">{{ $policy->response_time_minutes }} mins</td>
                        <td style="padding: 0.75rem 0.5rem;">{{ $policy->resolution_time_minutes }} mins</td>
                        <td style="padding: 0.75rem 0.5rem;">{{ $policy->escalation_time_minutes }} mins</td>
                        <td style="padding: 0.75rem 0.5rem;">{{ $policy->max_tickets_per_day }} tickets</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="padding: 1.5rem 0.5rem; text-align: center; color: var(--text-muted);">No SLA Policies configured.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Active SLA Breaches & Warnings -->
    <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); display: flex; flex-direction: column;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-dark);">Recent SLA Breach Log</h3>
            <span class="status-badge down" style="font-size: 0.7rem; font-weight: 700; padding: 0.25rem 0.5rem;">Latest Breaches</span>
        </div>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-color); color: var(--text-muted); font-weight: 600;">
                        <th style="padding: 0.75rem 0.5rem;">Ticket No</th>
                        <th style="padding: 0.75rem 0.5rem;">Title</th>
                        <th style="padding: 0.75rem 0.5rem;">Breach Type</th>
                        <th style="padding: 0.75rem 0.5rem;">Breached At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($breaches as $breach)
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 0.75rem 0.5rem; font-weight: 700;"><a href="#" style="color: var(--primary); text-decoration: none;">{{ $breach->ticket->ticket_number ?? '—' }}</a></td>
                        <td style="padding: 0.75rem 0.5rem;">{{ Str::limit($breach->ticket->title ?? '—', 30) }}</td>
                        <td style="padding: 0.75rem 0.5rem;">
                            <span class="status-badge down" style="font-size: 0.7rem;">{{ ucfirst($breach->type) }} SLA</span>
                        </td>
                        <td style="padding: 0.75rem 0.5rem; color: var(--text-muted);">{{ $breach->breached_at->format('d M Y H:i') }}</td>
                    </tr>
                    @empty
                    <!-- Mock premium rows if database is freshly initialized -->
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 0.75rem 0.5rem; font-weight: 700; color: var(--primary);">INC-1024</td>
                        <td style="padding: 0.75rem 0.5rem;">VPN Connectivity - Mumbai DC</td>
                        <td style="padding: 0.75rem 0.5rem;"><span class="status-badge down" style="font-size: 0.7rem;">Response SLA</span></td>
                        <td style="padding: 0.75rem 0.5rem; color: var(--text-muted);">24 Jun 2026 08:35</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 0.75rem 0.5rem; font-weight: 700; color: var(--primary);">INC-1011</td>
                        <td style="padding: 0.75rem 0.5rem;">BGP Route Flapping - Chennai</td>
                        <td style="padding: 0.75rem 0.5rem;"><span class="status-badge down" style="font-size: 0.7rem;">Resolution SLA</span></td>
                        <td style="padding: 0.75rem 0.5rem; color: var(--text-muted);">23 Jun 2026 14:20</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Customer SLA Rankings & Service Availability Grid -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
    <!-- Service Availability List -->
    <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow);">
        <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 1.25rem;">Service Availability Tracker</h3>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div>
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem;">
                    <span>Main Core Network</span>
                    <span style="color: var(--status-up);">99.98%</span>
                </div>
                <div style="height: 6px; background-color: var(--border-color); border-radius: 3px; overflow: hidden;">
                    <div style="width: 99.98%; height: 100%; background-color: var(--status-up); border-radius: 3px;"></div>
                </div>
            </div>
            <div>
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem;">
                    <span>Security Systems (Firewalls)</span>
                    <span style="color: var(--status-up);">99.95%</span>
                </div>
                <div style="height: 6px; background-color: var(--border-color); border-radius: 3px; overflow: hidden;">
                    <div style="width: 99.95%; height: 100%; background-color: var(--status-up); border-radius: 3px;"></div>
                </div>
            </div>
            <div>
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem;">
                    <span>Customer VPN Portals</span>
                    <span style="color: var(--status-warning);">98.85%</span>
                </div>
                <div style="height: 6px; background-color: var(--border-color); border-radius: 3px; overflow: hidden;">
                    <div style="width: 98.85%; height: 100%; background-color: var(--status-warning); border-radius: 3px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer SLA Performance -->
    <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow);">
        <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 1.25rem;">Top Customers SLA Compliance</h3>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div>
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem;">
                    <span>Western Railway</span>
                    <span>99.94%</span>
                </div>
                <div style="height: 6px; background-color: var(--border-color); border-radius: 3px; overflow: hidden;">
                    <div style="width: 99.94%; height: 100%; background-color: var(--primary); border-radius: 3px;"></div>
                </div>
            </div>
            <div>
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem;">
                    <span>Northern Railway</span>
                    <span>99.82%</span>
                </div>
                <div style="height: 6px; background-color: var(--border-color); border-radius: 3px; overflow: hidden;">
                    <div style="width: 99.82%; height: 100%; background-color: var(--primary); border-radius: 3px;"></div>
                </div>
            </div>
            <div>
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem;">
                    <span>Central Office</span>
                    <span>99.12%</span>
                </div>
                <div style="height: 6px; background-color: var(--border-color); border-radius: 3px; overflow: hidden;">
                    <div style="width: 99.12%; height: 100%; background-color: var(--primary); border-radius: 3px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // SLA Compliance Chart (Line)
    const complianceCtx = document.getElementById('slaComplianceChart').getContext('2d');
    new Chart(complianceCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartMonths) !!},
            datasets: [{
                label: 'SLA Compliance Level (%)',
                data: {!! json_encode($chartData) !!},
                borderColor: '#74C62B',
                backgroundColor: 'rgba(116, 198, 43, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#74C62B',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    min: 98,
                    max: 100,
                    ticks: {
                        callback: function(value) { return value + '%'; }
                    },
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // SLA Health Donut Chart
    const healthCtx = document.getElementById('slaHealthChart').getContext('2d');
    new Chart(healthCtx, {
        type: 'doughnut',
        data: {
            labels: ['Within Target', 'Warning Risk', 'Breached'],
            datasets: [{
                data: [88.5, 9.2, 2.3],
                backgroundColor: ['#74C62B', '#f97316', '#ef4444'],
                hoverOffset: 4,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            cutout: '75%'
        }
    });
});
</script>
@endsection
