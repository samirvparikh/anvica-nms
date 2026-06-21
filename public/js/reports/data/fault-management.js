window.NmsReportData = window.NmsReportData || {};

window.NmsReportData.faultManagement = {
    kpis: [
        { label: 'Total Alerts', value: '128', trend: '▲ 12% vs last 7 days', trendDir: 'up', icon: 'fa-solid fa-bell' },
        { label: 'Active Alarms', value: '8', subtitle: '4 Critical · 3 Major · 1 Minor', icon: 'fa-solid fa-triangle-exclamation' },
        { label: 'Downtime Events', value: '5', trend: '▲ 2 vs last 7 days', trendDir: 'up', icon: 'fa-solid fa-power-off' },
        { label: 'Total Downtime', value: '3h 24m', trend: '▼ 18% vs last 7 days', trendDir: 'down', icon: 'fa-solid fa-clock' },
    ],
    activeAlarms: [
        { id: 'ALM-2026-0142', device: 'Router-WAN-01', ip: '192.168.5.1', type: 'Device Down', severity: 'Critical', start: 'Jun 21, 2026 09:15', duration: '2h 35m', status: 'Active' },
        { id: 'ALM-2026-0138', device: 'Core-Switch-02', ip: '192.168.5.10', type: 'High CPU', severity: 'Major', start: 'Jun 21, 2026 10:42', duration: '1h 08m', status: 'Active' },
        { id: 'ALM-2026-0135', device: 'Firewall-Edge', ip: '192.168.5.5', type: 'Interface Down', severity: 'Major', start: 'Jun 21, 2026 08:30', duration: '3h 20m', status: 'Active' },
        { id: 'ALM-2026-0131', device: 'Router-Branch-A', ip: '10.10.20.1', type: 'Packet Loss', severity: 'Major', start: 'Jun 21, 2026 11:05', duration: '45m', status: 'Active' },
        { id: 'ALM-2026-0128', device: 'UPS-Server-Room', ip: '192.168.5.50', type: 'Battery Low', severity: 'Minor', start: 'Jun 21, 2026 07:00', duration: '4h 50m', status: 'Active' },
        { id: 'ALM-2026-0124', device: 'CCTV-NVR-01', ip: '192.168.5.80', type: 'Storage Warning', severity: 'Warning', start: 'Jun 20, 2026 22:15', duration: '13h 35m', status: 'Active' },
        { id: 'ALM-2026-0120', device: 'Router-WAN-02', ip: '192.168.5.2', type: 'Latency High', severity: 'Major', start: 'Jun 21, 2026 11:30', duration: '20m', status: 'Active' },
        { id: 'ALM-2026-0116', device: 'Switch-Floor-3', ip: '192.168.5.15', type: 'Port Flapping', severity: 'Critical', start: 'Jun 21, 2026 06:45', duration: '5h 05m', status: 'Active' },
    ],
    downtimeSummary: [
        { device: 'Router-WAN-01', ip: '192.168.5.1', down: 'Jun 21, 2026 09:15', up: '—', duration: '2h 35m', reason: 'Device Not Responding' },
        { device: 'Firewall-Edge', ip: '192.168.5.5', down: 'Jun 21, 2026 08:30', up: '—', duration: '3h 20m', reason: 'Interface Down' },
        { device: 'Core-Switch-01', ip: '192.168.5.10', down: 'Jun 20, 2026 14:22', up: 'Jun 20, 2026 15:10', duration: '48m', reason: 'Power Issue' },
        { device: 'Router-Branch-B', ip: '10.10.30.1', down: 'Jun 19, 2026 03:10', up: 'Jun 19, 2026 04:05', duration: '55m', reason: 'ISP Link Failure' },
        { device: 'Server-DB-01', ip: '192.168.5.100', down: 'Jun 18, 2026 23:40', up: 'Jun 19, 2026 00:06', duration: '26m', reason: 'Scheduled Maintenance Overrun' },
    ],
    severitySummary: {
        labels: ['Critical', 'Major', 'Minor', 'Warning'],
        values: [32, 28, 45, 23],
        colors: ['#ef4444', '#f97316', '#3b82f6', '#eab308'],
    },
    alarmsOverTime: {
        labels: ['Jun 15', 'Jun 16', 'Jun 17', 'Jun 18', 'Jun 19', 'Jun 20', 'Jun 21'],
        datasets: [
            { label: 'Critical', data: [3, 5, 4, 6, 5, 4, 8], borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.1)', tension: 0.35, fill: false },
            { label: 'Major', data: [8, 6, 9, 7, 10, 8, 12], borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.1)', tension: 0.35, fill: false },
            { label: 'Minor', data: [12, 10, 14, 11, 13, 15, 10], borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', tension: 0.35, fill: false },
            { label: 'Warning', data: [6, 8, 5, 7, 6, 9, 7], borderColor: '#eab308', backgroundColor: 'rgba(234,179,8,0.1)', tension: 0.35, fill: false },
        ],
    },
    featureBanner: [
        { icon: 'fa-solid fa-satellite-dish', title: 'Real-Time Monitoring', text: 'Monitor your network 24×7 with live device status and health metrics.' },
        { icon: 'fa-solid fa-bolt', title: 'Instant Alerts', text: 'Get notified before issues become major outages.' },
        { icon: 'fa-solid fa-chart-pie', title: 'Performance Insights', text: 'Analyze trends and optimize network performance.' },
        { icon: 'fa-solid fa-shield-halved', title: 'Reduce Downtime', text: 'Detect problems early and reduce downtime.' },
        { icon: 'fa-solid fa-clipboard-check', title: 'SLA Compliance', text: 'Stay compliant and deliver better service quality.' },
    ],
};
