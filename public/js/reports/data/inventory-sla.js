window.NmsReportData = window.NmsReportData || {};

window.NmsReportData.inventorySla = {
    kpis: [
        { label: 'Total Devices', value: '128', subtitle: 'All Registered Devices', icon: 'fa-solid fa-server' },
        { label: 'Active Devices', value: '120', subtitle: '93.75% of total', icon: 'fa-solid fa-circle-check' },
        { label: 'Devices Under Warranty', value: '98', subtitle: '76.56% of total', icon: 'fa-solid fa-shield-check' },
        { label: 'Expired Warranty', value: '30', subtitle: '23.44% of total', icon: 'fa-solid fa-calendar-xmark' },
        { label: 'SLA Compliance', value: '99.42%', subtitle: 'This Month', icon: 'fa-solid fa-chart-line' },
        { label: 'Average Uptime', value: '99.71%', subtitle: 'This Month', icon: 'fa-solid fa-arrow-trend-up' },
    ],
    deviceInventory: [
        { name: 'Router-WAN-01', ip: '192.168.5.1', type: 'Router', vendor: 'MikroTik', model: 'CCR2004', serial: 'MT123456', firmware: '7.18.2', warranty: '15-Dec-2027', status: 'Active' },
        { name: 'Core-Switch-02', ip: '192.168.5.10', type: 'Switch', vendor: 'Cisco', model: 'C9300-48P', serial: 'FCW2345', firmware: '17.9.4', warranty: '22-Mar-2028', status: 'Active' },
        { name: 'Firewall-Edge', ip: '192.168.5.5', type: 'Firewall', vendor: 'Fortigate', model: 'FG-100F', serial: 'FG678901', firmware: '7.4.3', warranty: '10-Jan-2027', status: 'Active' },
        { name: 'Anvica_Demo', ip: '192.168.88.1', type: 'Router', vendor: 'MikroTik', model: 'hEX S', serial: 'MT789012', firmware: '7.17.1', warranty: '05-Aug-2026', status: 'Active' },
        { name: 'Server-DB-01', ip: '192.168.5.100', type: 'Server', vendor: 'Dell', model: 'R740', serial: 'DL345678', firmware: '2.18.0', warranty: 'Expired', status: 'Active' },
        { name: 'UPS-Server-Room', ip: '192.168.5.50', type: 'UPS', vendor: 'APC', model: 'SRT5K', serial: 'AP901234', firmware: 'v1.4', warranty: 'Expired', status: 'Warning' },
    ],
    firmwareCompliance: [
        { vendor: 'MikroTik', installed: '7.18.2', latest: '7.19', status: 'Upgrade Available', affected: 4 },
        { vendor: 'Cisco', installed: '17.9.4', latest: '17.9.4', status: 'Compliant', affected: 0 },
        { vendor: 'Fortigate', installed: '7.4.3', latest: '7.4.5', status: 'Upgrade Available', affected: 2 },
        { vendor: 'Dell', installed: '2.18.0', latest: '2.20.1', status: 'Upgrade Available', affected: 3 },
    ],
    warrantyStatus: {
        labels: ['Under Warranty', 'Expiring < 30 Days', 'Expired'],
        values: [98, 12, 30],
        colors: ['#6BA539', '#f97316', '#ef4444'],
    },
    slaOverview: {
        labels: ['Compliant', 'At Risk', 'Non-Compliant'],
        values: [85, 28, 15],
        colors: ['#6BA539', '#f97316', '#ef4444'],
    },
    slaTrend: {
        labels: ['May 22', 'May 27', 'Jun 01', 'Jun 06', 'Jun 11', 'Jun 16', 'Jun 21'],
        values: [98.8, 99.1, 99.0, 99.3, 99.2, 99.4, 99.42],
    },
    slaByCustomer: [
        { customer: 'BSNL', target: '99.50%', uptime: '99.82%', compliance: '99.82%', status: 'Compliant' },
        { customer: 'PM-WANI', target: '99.00%', uptime: '99.45%', compliance: '99.45%', status: 'Compliant' },
        { customer: 'Corporate-A', target: '99.90%', uptime: '99.72%', compliance: '99.72%', status: 'At Risk' },
        { customer: 'Enterprise-B', target: '99.50%', uptime: '98.65%', compliance: '98.65%', status: 'Non-Compliant' },
        { customer: 'Govt. Project', target: '99.00%', uptime: '99.12%', compliance: '99.12%', status: 'Compliant' },
    ],
    featureBanner: [
        { icon: 'fa-solid fa-boxes-stacked', title: 'Complete Inventory', text: 'Track all network assets in one place.' },
        { icon: 'fa-solid fa-code-branch', title: 'Software Tracking', text: 'Monitor firmware versions and stay up to date.' },
        { icon: 'fa-solid fa-file-contract', title: 'Warranty Management', text: 'Track warranty status and get expiry alerts.' },
        { icon: 'fa-solid fa-shield-check', title: 'SLA Compliance', text: 'Measure performance and meet SLA commitments.' },
        { icon: 'fa-solid fa-lightbulb', title: 'Actionable Insights', text: 'Make informed decisions with intelligent reports.' },
    ],
};
