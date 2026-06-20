(function () {
    'use strict';

    function getCellText(row, columnIndex) {
        const cell = row.cells[columnIndex];
        if (!cell) {
            return '';
        }

        return cell.textContent.replace(/\s+/g, ' ').trim();
    }

    function isFilterableRow(row) {
        if (!row || row.cells.length === 0) {
            return false;
        }

        if (row.classList.contains('no-sort-row')) {
            return false;
        }

        const firstCell = row.cells[0];
        if (firstCell && firstCell.colSpan > 1) {
            return false;
        }

        return true;
    }

    function applyFilters(table) {
        const filterRow = table.tHead && table.tHead.rows[1];
        const tbody = table.tBodies[0];

        if (!filterRow || !tbody) {
            return;
        }

        const filters = Array.from(filterRow.cells).map(function (cell) {
            const input = cell.querySelector('input');
            return input ? input.value.toLowerCase().trim() : '';
        });

        Array.from(tbody.rows).forEach(function (row) {
            if (!isFilterableRow(row)) {
                return;
            }

            const visible = filters.every(function (filter, index) {
                if (!filter) {
                    return true;
                }

                return getCellText(row, index).toLowerCase().includes(filter);
            });

            row.style.display = visible ? '' : 'none';
        });
    }

    function clearFilterInputs(table) {
        if (!table.tHead || !table.tHead.rows[1]) {
            return;
        }

        table.tHead.rows[1].querySelectorAll('input.data-table-filter-input').forEach(function (input) {
            input.value = '';
        });
    }

    window.resetDataTableFilters = function (table) {
        if (!table) {
            return;
        }

        clearFilterInputs(table);
        applyFilters(table);
    };

    function initDataTableFilter(table) {
        if (!table || table.dataset.filterInitialized === 'true') {
            return;
        }

        const headerRow = table.tHead && table.tHead.rows[0];
        if (!headerRow) {
            return;
        }

        const filterRow = document.createElement('tr');
        filterRow.className = 'data-table-filter-row';

        Array.from(headerRow.cells).forEach(function (header) {
            const th = document.createElement('th');

            if (header.classList.contains('col-actions') || header.dataset.noFilter === 'true') {
                th.innerHTML = '&nbsp;';
            } else {
                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'data-table-filter-input';
                input.placeholder = 'Filter...';
                input.setAttribute('aria-label', 'Filter ' + (header.textContent || '').trim());
                input.addEventListener('input', function () {
                    applyFilters(table);
                });
                th.appendChild(input);
            }

            filterRow.appendChild(th);
        });

        table.tHead.appendChild(filterRow);
        table.dataset.filterInitialized = 'true';
    }

    function initAllDataTableFilters(root) {
        (root || document).querySelectorAll('table.data-table-filterable').forEach(initDataTableFilter);
    }

    window.initDataTableFilter = initDataTableFilter;
    window.initAllDataTableFilters = initAllDataTableFilters;

    document.addEventListener('DOMContentLoaded', function () {
        initAllDataTableFilters(document);
    });
})();
