document.addEventListener('DOMContentLoaded', function () {
    const U = window.NmsReportUtils;
    const config = window.NmsFaultReportConfig || {};
    if (!U || !config.dataUrl) return;

    const loadingEl = document.getElementById('nmsFaultLoading');
    const errorEl = document.getElementById('nmsFaultError');
    const contentIds = ['nmsKpiGrid', 'nmsFaultTables', 'nmsFaultCharts', 'nmsFeatureBanner'];

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

        U.renderTable(
            document.getElementById('activeAlarmsHead'),
            document.getElementById('activeAlarmsBody'),
            [
                { key: 'id', label: 'Alarm ID' },
                { key: 'device', label: 'Device' },
                { key: 'ip', label: 'IP Address' },
                { key: 'type', label: 'Alarm Type' },
                { key: 'severity', label: 'Severity', render: (v) => U.badge(v, v) },
                { key: 'start', label: 'Start Time' },
                { key: 'duration', label: 'Duration' },
                { key: 'status', label: 'Status', render: (v) => U.badge(v, v) },
            ],
            D.activeAlarms
        );

        U.renderTable(
            document.getElementById('downtimeHead'),
            document.getElementById('downtimeBody'),
            [
                { key: 'device', label: 'Device' },
                { key: 'ip', label: 'IP' },
                { key: 'down', label: 'Down Time' },
                { key: 'up', label: 'Up Time' },
                { key: 'duration', label: 'Duration' },
                { key: 'reason', label: 'Reason' },
            ],
            D.downtimeSummary
        );

        U.donutChart(
            document.getElementById('severityChart'),
            D.severitySummary.labels,
            D.severitySummary.values,
            D.severitySummary.colors
        );

        U.lineChart(document.getElementById('alarmsTrendChart'), D.alarmsOverTime.labels, D.alarmsOverTime.datasets);
        U.renderFeatureBanner(document.getElementById('nmsFeatureBanner'), D.featureBanner);
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
                errorEl.textContent = err.message || 'Unable to load fault management report.';
            }
        });
});
