document.addEventListener('DOMContentLoaded', function () {
    const U = window.NmsReportUtils;
    const config = window.NmsPerfReportConfig || {};
    if (!U || !config.dataUrl) return;

    const loadingEl = document.getElementById('nmsPerfLoading');
    const errorEl = document.getElementById('nmsPerfError');
    const contentIds = ['nmsKpiGrid', 'nmsPerfCharts', 'nmsPerfMemoryChart', 'nmsPerfTable'];

    function showContent(show) {
        contentIds.forEach(function (id) {
            const el = document.getElementById(id);
            if (el) {
                el.hidden = !show;
            }
        });
        if (loadingEl) {
            loadingEl.hidden = show;
        }
    }

    function buildDataUrl() {
        const params = new URLSearchParams();
        if (config.from) params.set('from', config.from);
        if (config.to) params.set('to', config.to);
        if (config.userId) params.set('user_id', config.userId);
        const qs = params.toString();
        return qs ? config.dataUrl + '?' + qs : config.dataUrl;
    }

    function renderReport(D) {
        U.renderKpiCards(document.getElementById('nmsKpiGrid'), D.kpis);

        const labels = D.trendLabels || [];
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
            D.topInterfaces || []
        );
    }

    fetch(buildDataUrl(), {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
    })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Failed to load report data (' + response.status + ')');
            }
            return response.json();
        })
        .then(function (data) {
            showContent(true);
            renderReport(data);
        })
        .catch(function (err) {
            if (loadingEl) loadingEl.hidden = true;
            if (errorEl) {
                errorEl.hidden = false;
                errorEl.textContent = err.message || 'Unable to load performance report.';
            }
        });
});
