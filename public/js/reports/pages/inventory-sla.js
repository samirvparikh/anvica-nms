document.addEventListener('DOMContentLoaded', function () {
    const U = window.NmsReportUtils;
    const D = window.NmsReportData.inventorySla;
    if (!U || !D) return;

    U.renderKpiCards(document.getElementById('nmsKpiGrid'), D.kpis);

    U.renderTable(
        document.getElementById('inventoryHead'),
        document.getElementById('inventoryBody'),
        [
            { key: 'name', label: 'Device Name' },
            { key: 'ip', label: 'IP' },
            { key: 'type', label: 'Device Type' },
            { key: 'vendor', label: 'Vendor' },
            { key: 'model', label: 'Model' },
            { key: 'serial', label: 'Serial' },
            { key: 'firmware', label: 'Firmware' },
            { key: 'warranty', label: 'Warranty Expiry' },
            { key: 'status', label: 'Status', render: (v) => U.badge(v, v) },
        ],
        D.deviceInventory
    );

    U.renderTable(
        document.getElementById('firmwareHead'),
        document.getElementById('firmwareBody'),
        [
            { key: 'vendor', label: 'Vendor' },
            { key: 'installed', label: 'Installed Version' },
            { key: 'latest', label: 'Latest Version' },
            { key: 'status', label: 'Status', render: (v) => U.badge(v, v === 'Compliant' ? 'Compliant' : 'At Risk') },
            { key: 'affected', label: 'Affected Devices' },
        ],
        D.firmwareCompliance
    );

    U.renderTable(
        document.getElementById('slaCustomerHead'),
        document.getElementById('slaCustomerBody'),
        [
            { key: 'customer', label: 'Customer' },
            { key: 'target', label: 'SLA Target' },
            { key: 'uptime', label: 'Uptime Achieved' },
            { key: 'compliance', label: 'Compliance (%)' },
            { key: 'status', label: 'Status', render: (v) => U.badge(v, v) },
        ],
        D.slaByCustomer
    );

    U.donutChart(document.getElementById('warrantyChart'), D.warrantyStatus.labels, D.warrantyStatus.values, D.warrantyStatus.colors);
    U.donutChart(document.getElementById('slaOverviewChart'), D.slaOverview.labels, D.slaOverview.values, D.slaOverview.colors);
    U.areaChart(document.getElementById('slaTrendChart'), D.slaTrend.labels, {
        label: 'SLA %', data: D.slaTrend.values, borderColor: U.ACCENT, backgroundColor: 'rgba(107,165,57,0.15)',
    });
    U.renderFeatureBanner(document.getElementById('nmsFeatureBanner'), D.featureBanner);
});
