window.NmsReportData = window.NmsReportData || {};

window.NmsReportData.performanceTraffic = {
    kpis: [
        { label: 'Bandwidth Utilization', value: '45.6%', trend: '▲ 8% vs last 7 days', trendDir: 'up', icon: 'fa-solid fa-gauge-high' },
        { label: 'Average Latency', value: '18.6 ms', trend: '▼ 12% vs last 7 days', trendDir: 'down', icon: 'fa-solid fa-stopwatch' },
        { label: 'Packet Loss', value: '0.35%', trend: '▼ 5% vs last 7 days', trendDir: 'down', icon: 'fa-solid fa-wave-square' },
        { label: 'CPU Utilization', value: '42.7%', trend: '▲ 3% vs last 7 days', trendDir: 'up', icon: 'fa-solid fa-microchip' },
        { label: 'Memory Utilization', value: '58.3%', trend: '▲ 6% vs last 7 days', trendDir: 'up', icon: 'fa-solid fa-memory' },
    ],
    trendLabels: ['Jun 15', 'Jun 16', 'Jun 17', 'Jun 18', 'Jun 19', 'Jun 20', 'Jun 21'],
    bandwidthTrend: [38, 42, 45, 41, 48, 44, 46],
    latencyTrend: [22, 20, 19, 21, 18, 17, 19],
    packetLossTrend: [0.52, 0.48, 0.41, 0.38, 0.36, 0.34, 0.35],
    cpuTrend: [38, 40, 41, 39, 43, 44, 43],
    memoryTrend: [52, 54, 55, 56, 57, 58, 58],
    topInterfaces: [
        { interface: 'ether1-WAN', device: 'Router-WAN-01', inTraffic: '152.6 Mbps', outTraffic: '98.4 Mbps', utilization: 76 },
        { interface: 'sfp-sfpplus1', device: 'Core-Switch-02', inTraffic: '890.2 Mbps', outTraffic: '845.1 Mbps', utilization: 89 },
        { interface: 'pppoe-out1', device: 'Anvica_Demo', inTraffic: '45.8 Mbps', outTraffic: '12.3 Mbps', utilization: 46 },
        { interface: 'ge-0/0/1', device: 'Firewall-Edge', inTraffic: '234.5 Mbps', outTraffic: '198.7 Mbps', utilization: 62 },
        { interface: 'eth0', device: 'Server-DB-01', inTraffic: '78.3 Mbps', outTraffic: '65.2 Mbps', utilization: 39 },
        { interface: 'wlan1', device: 'AP-Floor-2', inTraffic: '32.1 Mbps', outTraffic: '28.6 Mbps', utilization: 28 },
    ],
};
