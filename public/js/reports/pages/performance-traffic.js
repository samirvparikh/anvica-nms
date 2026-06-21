document.addEventListener('DOMContentLoaded', function () {
    const U = window.NmsReportUtils;
    const D = window.NmsReportData.performanceTraffic;
    if (!U || !D) return;

    U.renderKpiCards(document.getElementById('nmsKpiGrid'), D.kpis);

    const labels = D.trendLabels;
    const accent = U.ACCENT;

    U.areaChart(document.getElementById('bandwidthChart'), labels, {
        label: 'Bandwidth %', data: D.bandwidthTrend, borderColor: accent, backgroundColor: 'rgba(107,165,57,0.15)',
    });
    U.areaChart(document.getElementById('latencyChart'), labels, {
        label: 'Latency ms', data: D.latencyTrend, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.12)',
    });
    U.areaChart(document.getElementById('packetLossChart'), labels, {
        label: 'Packet Loss %', data: D.packetLossTrend, borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.1)',
    });
    U.areaChart(document.getElementById('cpuChart'), labels, {
        label: 'CPU %', data: D.cpuTrend, borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.1)',
    });
    U.areaChart(document.getElementById('memoryChart'), labels, {
        label: 'Memory %', data: D.memoryTrend, borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.1)',
    });

    U.renderTable(
        document.getElementById('interfacesHead'),
        document.getElementById('interfacesBody'),
        [
            { key: 'interface', label: 'Interface' },
            { key: 'device', label: 'Device' },
            { key: 'inTraffic', label: 'In Traffic' },
            { key: 'outTraffic', label: 'Out Traffic' },
            { key: 'utilization', label: 'Utilization', render: (v) => U.progressBar(v, 100) },
        ],
        D.topInterfaces
    );
});
