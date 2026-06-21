(function (window) {
    'use strict';

    const ACCENT = '#6BA539';

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function severityClass(severity) {
        const map = {
            critical: 'down',
            major: 'warning',
            minor: 'warning',
            warning: 'warning',
            high: 'down',
            medium: 'warning',
            low: 'up',
            open: 'warning',
            'in progress': 'warning',
            resolved: 'up',
            closed: 'up',
            active: 'down',
            compliant: 'up',
            'at risk': 'warning',
            'non-compliant': 'down',
            breach: 'down',
        };

        return map[String(severity).toLowerCase()] || 'inactive';
    }

    function badge(text, type) {
        return '<span class="status-badge ' + severityClass(type || text) + '">' + escapeHtml(text) + '</span>';
    }

    function renderKpiCards(container, kpis) {
        if (!container) {
            return;
        }

        container.innerHTML = kpis.map(function (kpi) {
            const trendClass = kpi.trendDir === 'down' ? 'down' : 'up';
            const trendHtml = kpi.trend
                ? '<span class="nms-kpi-trend ' + trendClass + '">' + escapeHtml(kpi.trend) + '</span>'
                : (kpi.subtitle ? '<span class="nms-kpi-sub">' + escapeHtml(kpi.subtitle) + '</span>' : '');

            return ''
                + '<div class="nms-kpi-card">'
                + '<div class="nms-kpi-icon"><i class="' + escapeHtml(kpi.icon || 'fa-solid fa-chart-simple') + '"></i></div>'
                + '<div class="nms-kpi-body">'
                + '<span class="nms-kpi-label">' + escapeHtml(kpi.label) + '</span>'
                + '<span class="nms-kpi-value">' + escapeHtml(kpi.value) + '</span>'
                + trendHtml
                + '</div></div>';
        }).join('');
    }

    function renderTable(headEl, bodyEl, columns, rows) {
        if (!headEl || !bodyEl) {
            return;
        }

        headEl.innerHTML = '<tr>' + columns.map(function (col) {
            return '<th>' + escapeHtml(col.label) + '</th>';
        }).join('') + '</tr>';

        if (!rows.length) {
            bodyEl.innerHTML = '<tr class="no-sort-row"><td colspan="' + columns.length + '" style="text-align:center;padding:2rem;color:var(--text-muted);">No records found.</td></tr>';
            return;
        }

        bodyEl.innerHTML = rows.map(function (row) {
            return '<tr>' + columns.map(function (col) {
                const raw = row[col.key];
                const cell = col.render ? col.render(raw, row) : escapeHtml(raw ?? '—');
                return '<td>' + cell + '</td>';
            }).join('') + '</tr>';
        }).join('');

        if (window.initDataTableSort && bodyEl.closest('table')) {
            window.initDataTableSort(bodyEl.closest('table'));
        }
    }

    function donutChart(canvas, labels, values, colors, centerText) {
        if (!canvas || !window.Chart) {
            return null;
        }

        return new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: { enabled: true },
                },
            },
            plugins: centerText ? [{
                id: 'centerText',
                beforeDraw: function (chart) {
                    const { ctx, chartArea: { width, height, left, top } } = chart;
                    ctx.save();
                    ctx.font = 'bold 14px Inter, sans-serif';
                    ctx.fillStyle = '#0f172a';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(centerText, left + width / 2, top + height / 2);
                    ctx.restore();
                },
            }] : [],
        });
    }

    function lineChart(canvas, labels, datasets) {
        if (!canvas || !window.Chart) {
            return null;
        }

        return new Chart(canvas, {
            type: 'line',
            data: { labels: labels, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'top', labels: { boxWidth: 12 } } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                },
            },
        });
    }

    function areaChart(canvas, labels, dataset) {
        if (!canvas || !window.Chart) {
            return null;
        }

        return new Chart(canvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    ...dataset,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: false, grid: { color: '#f1f5f9' } },
                },
            },
        });
    }

    function renderFeatureBanner(container, items) {
        if (!container) {
            return;
        }

        container.innerHTML = items.map(function (item) {
            return ''
                + '<div class="nms-feature-item">'
                + '<div class="nms-feature-icon"><i class="' + escapeHtml(item.icon) + '"></i></div>'
                + '<strong>' + escapeHtml(item.title) + '</strong>'
                + '<p>' + escapeHtml(item.text) + '</p>'
                + '</div>';
        }).join('');
    }

    function renderSummaryCards(container, cards) {
        if (!container) {
            return;
        }

        container.innerHTML = cards.map(function (card) {
            const rows = card.items.map(function (item) {
                return '<div class="nms-summary-row"><span>' + escapeHtml(item.label) + '</span><strong>' + escapeHtml(item.value) + '</strong></div>';
            }).join('');

            return ''
                + '<div class="nms-summary-card">'
                + '<h4>' + escapeHtml(card.title) + '</h4>'
                + rows
                + '</div>';
        }).join('');
    }

    function progressBar(value, max) {
        const pct = Math.min(100, Math.round((value / max) * 100));
        return '<div class="nms-util-bar"><span style="width:' + pct + '%"></span></div> <span class="nms-util-text">' + pct + '%</span>';
    }

    window.NmsReportUtils = {
        ACCENT,
        escapeHtml,
        badge,
        renderKpiCards,
        renderTable,
        donutChart,
        lineChart,
        areaChart,
        renderFeatureBanner,
        renderSummaryCards,
        progressBar,
        severityClass,
    };
})(window);
