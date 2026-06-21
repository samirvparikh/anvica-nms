document.addEventListener('DOMContentLoaded', function () {
    const U = window.NmsReportUtils;
    const D = window.NmsReportData.faultManagement;
    if (!U || !D) return;

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
});
