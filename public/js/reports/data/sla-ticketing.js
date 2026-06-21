window.NmsReportData = window.NmsReportData || {};

window.NmsReportData.slaTicketing = {
    kpis: [
        { label: 'SLA Compliance', value: '99.42%', trend: '▲ 2.35% vs last 30 days', trendDir: 'up', icon: 'fa-solid fa-shield-check' },
        { label: 'SLA Breaches', value: '6', trend: '▼ 2 vs last month', trendDir: 'down', icon: 'fa-solid fa-triangle-exclamation' },
        { label: 'Total Tickets', value: '142', trend: '▲ 12% vs last 30 days', trendDir: 'up', icon: 'fa-solid fa-ticket' },
        { label: 'Open Tickets', value: '14', trend: '▼ 3 vs last week', trendDir: 'down', icon: 'fa-solid fa-folder-open' },
        { label: 'Resolved Tickets', value: '128', trend: '▲ 15% vs last 30 days', trendDir: 'up', icon: 'fa-solid fa-circle-check' },
        { label: 'Avg. Resolution Time', value: '2h 34m', trend: '▼ 18m vs last month', trendDir: 'down', icon: 'fa-solid fa-clock' },
        { label: 'User Satisfaction', value: '4.6 / 5', trend: '▲ 0.4 vs last month', trendDir: 'up', icon: 'fa-solid fa-thumbs-up' },
    ],
    slaComplianceTrend: {
        labels: ['May 22', 'May 27', 'Jun 01', 'Jun 06', 'Jun 11', 'Jun 16', 'Jun 21'],
        values: [98.5, 98.8, 99.0, 99.1, 99.2, 99.3, 99.42],
    },
    slaByCustomer: {
        labels: ['BSNL', 'PM-WANI', 'Corporate-A', 'Enterprise-B', 'Govt. Project'],
        values: [99.82, 99.45, 99.72, 98.65, 99.12],
        colors: ['#6BA539', '#22c55e', '#84cc16', '#f97316', '#3b82f6'],
    },
    breachesByPriority: {
        labels: ['Critical', 'Major', 'Minor', 'Warning'],
        values: [2, 2, 1, 1],
        colors: ['#ef4444', '#f97316', '#3b82f6', '#eab308'],
    },
    tickets: [
        { id: 'TKT-2026-0142', subject: 'Internet Down at Site A', customer: 'BSNL', priority: 'Critical', status: 'Open', assigned: 'Sanjay', created: 'Jun 21, 2026 10:15', due: 'Jun 21, 2026 12:15', resolution: '—' },
        { id: 'TKT-2026-0139', subject: 'High Latency on WAN Link', customer: 'Corporate-A', priority: 'Major', status: 'In Progress', assigned: 'Priya', created: 'Jun 21, 2026 09:30', due: 'Jun 21, 2026 14:30', resolution: '—' },
        { id: 'TKT-2026-0135', subject: 'Router CPU Above Threshold', customer: 'PM-WANI', priority: 'Major', status: 'Resolved', assigned: 'Amit', created: 'Jun 20, 2026 16:45', due: 'Jun 21, 2026 08:45', resolution: '2h 10m' },
        { id: 'TKT-2026-0130', subject: 'Firewall Rule Change Request', customer: 'Enterprise-B', priority: 'Minor', status: 'Resolved', assigned: 'Neha', created: 'Jun 20, 2026 11:00', due: 'Jun 22, 2026 11:00', resolution: '4h 25m' },
        { id: 'TKT-2026-0125', subject: 'SLA Breach - Site B Outage', customer: 'Govt. Project', priority: 'Critical', status: 'Resolved', assigned: 'Sanjay', created: 'Jun 19, 2026 08:00', due: 'Jun 19, 2026 10:00', resolution: '1h 55m' },
    ],
    userPerformance: [
        { user: 'Site-A / Delhi', location: 'Delhi', availability: '99.92%', latency: '12.4 ms', packetLoss: '0.12%', jitter: '1.8 ms', score: '4.8/5' },
        { user: 'Site-B / Mumbai', location: 'Mumbai', availability: '99.65%', latency: '18.2 ms', packetLoss: '0.28%', jitter: '2.4 ms', score: '4.5/5' },
        { user: 'Site-C / Bangalore', location: 'Bangalore', availability: '99.88%', latency: '14.6 ms', packetLoss: '0.15%', jitter: '2.1 ms', score: '4.7/5' },
        { user: 'Site-D / Chennai', location: 'Chennai', availability: '98.95%', latency: '24.8 ms', packetLoss: '0.52%', jitter: '3.6 ms', score: '4.1/5' },
    ],
    ticketTrend: {
        labels: ['May 22', 'May 27', 'Jun 01', 'Jun 06', 'Jun 11', 'Jun 16', 'Jun 21'],
        opened: [12, 15, 18, 14, 20, 16, 22],
        resolved: [10, 14, 16, 15, 18, 19, 20],
    },
    ticketStatus: {
        labels: ['Open', 'In Progress', 'Resolved'],
        values: [14, 28, 128],
        colors: ['#f97316', '#3b82f6', '#6BA539'],
    },
    topIssues: [
        { issue: 'Internet Down', tickets: 32, percent: 22.54 },
        { issue: 'High Latency', tickets: 24, percent: 16.90 },
        { issue: 'Device Unreachable', tickets: 18, percent: 12.68 },
        { issue: 'Interface Down', tickets: 15, percent: 10.56 },
        { issue: 'CPU/RAM High', tickets: 12, percent: 8.45 },
    ],
    summaryCards: [
        {
            title: 'SLA Summary',
            items: [
                { label: 'Total SLAs', value: '18' },
                { label: 'Active SLAs', value: '16' },
                { label: 'Breached SLAs', value: '3' },
                { label: 'At Risk SLAs', value: '2' },
                { label: 'Upcoming Review', value: '5' },
            ],
        },
        {
            title: 'SLA Breach Summary',
            items: [
                { label: 'Breaches (This Month)', value: '6' },
                { label: 'Breaches (Last Month)', value: '8' },
                { label: 'Improvement', value: '25% ▼' },
            ],
        },
        {
            title: 'Resolution Performance',
            items: [
                { label: 'First Response Time (Avg.)', value: '28m' },
                { label: 'Resolution Time (Avg.)', value: '2h 34m' },
                { label: 'SLA Met Rate', value: '99.42%' },
            ],
        },
    ],
};
