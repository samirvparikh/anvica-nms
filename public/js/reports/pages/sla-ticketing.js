document.addEventListener('DOMContentLoaded', function () {
    const U = window.NmsReportUtils;
    const D = window.NmsReportData.slaTicketing;
    if (!U || !D) return;

    U.renderKpiCards(document.getElementById('nmsKpiGrid'), D.kpis);

    U.areaChart(document.getElementById('slaComplianceTrendChart'), D.slaComplianceTrend.labels, {
        label: 'SLA Compliance %', data: D.slaComplianceTrend.values, borderColor: U.ACCENT, backgroundColor: 'rgba(107,165,57,0.15)',
    });

    U.donutChart(document.getElementById('slaCustomerChart'), D.slaByCustomer.labels, D.slaByCustomer.values, D.slaByCustomer.colors);
    U.donutChart(document.getElementById('breachPriorityChart'), D.breachesByPriority.labels, D.breachesByPriority.values, D.breachesByPriority.colors);
    U.donutChart(document.getElementById('ticketStatusChart'), D.ticketStatus.labels, D.ticketStatus.values, D.ticketStatus.colors);

    U.lineChart(document.getElementById('ticketTrendChart'), D.ticketTrend.labels, [
        { label: 'Opened', data: D.ticketTrend.opened, borderColor: '#3b82f6', tension: 0.35, fill: false },
        { label: 'Resolved', data: D.ticketTrend.resolved, borderColor: U.ACCENT, tension: 0.35, fill: false },
    ]);

    U.renderTable(
        document.getElementById('ticketsHead'),
        document.getElementById('ticketsBody'),
        [
            { key: 'id', label: 'Ticket ID' },
            { key: 'subject', label: 'Subject' },
            { key: 'customer', label: 'Customer' },
            { key: 'priority', label: 'Priority', render: (v) => U.badge(v, v) },
            { key: 'status', label: 'Status', render: (v) => U.badge(v, v) },
            { key: 'assigned', label: 'Assigned To' },
            { key: 'created', label: 'Created On' },
            { key: 'due', label: 'Due Time' },
            { key: 'resolution', label: 'Resolution Time' },
        ],
        D.tickets
    );

    U.renderTable(
        document.getElementById('userPerfHead'),
        document.getElementById('userPerfBody'),
        [
            { key: 'user', label: 'User / Site' },
            { key: 'location', label: 'Location' },
            { key: 'availability', label: 'Availability' },
            { key: 'latency', label: 'Latency' },
            { key: 'packetLoss', label: 'Packet Loss' },
            { key: 'jitter', label: 'Jitter' },
            { key: 'score', label: 'Score' },
        ],
        D.userPerformance
    );

    U.renderTable(
        document.getElementById('topIssuesHead'),
        document.getElementById('topIssuesBody'),
        [
            { key: 'issue', label: 'Issue' },
            { key: 'tickets', label: 'Tickets' },
            { key: 'percent', label: '% of Total', render: (v) => v + '%' },
        ],
        D.topIssues
    );

    U.renderSummaryCards(document.getElementById('nmsSummaryCards'), D.summaryCards);
});
