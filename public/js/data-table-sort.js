(function () {
    'use strict';

    function getCellText(row, columnIndex) {
        const cell = row.cells[columnIndex];
        if (!cell) {
            return '';
        }

        return cell.textContent.replace(/\s+/g, ' ').trim();
    }

    function parseSortNumber(value) {
        const cleaned = value.replace(/,/g, '').trim();

        if (cleaned === '' || !/^-?\d+(\.\d+)?$/.test(cleaned)) {
            return null;
        }

        return Number(cleaned);
    }

    function compareValues(a, b) {
        if (a === b) {
            return 0;
        }

        if (a === '' || a === '—') {
            return 1;
        }

        if (b === '' || b === '—') {
            return -1;
        }

        const numA = parseSortNumber(a);
        const numB = parseSortNumber(b);

        if (numA !== null && numB !== null) {
            return numA - numB;
        }

        return a.localeCompare(b, undefined, { numeric: true, sensitivity: 'base' });
    }

    function isSortableRow(row) {
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

    function clearSortState(table) {
        table.querySelectorAll('th.sortable').forEach(function (header) {
            header.dataset.sortDir = '';
            header.classList.remove('sorted-asc', 'sorted-desc');
            header.setAttribute('aria-sort', 'none');
        });
    }

    window.resetDataTableSort = clearSortState;

    function sortTable(table, columnIndex, direction) {
        const tbody = table.tBodies[0];
        if (!tbody) {
            return;
        }

        const rows = Array.from(tbody.rows);
        const dataRows = rows.filter(isSortableRow);
        const placeholderRows = rows.filter(function (row) {
            return !isSortableRow(row);
        });

        dataRows.sort(function (rowA, rowB) {
            const result = compareValues(
                getCellText(rowA, columnIndex).toLowerCase(),
                getCellText(rowB, columnIndex).toLowerCase()
            );

            return direction === 'asc' ? result : -result;
        });

        dataRows.forEach(function (row) {
            tbody.appendChild(row);
        });

        placeholderRows.forEach(function (row) {
            tbody.appendChild(row);
        });
    }

    function isSortableHeader(header) {
        return !header.classList.contains('col-actions') && header.dataset.noSort !== 'true';
    }

    function toggleSort(table, header, columnIndex) {
        const nextDirection = header.dataset.sortDir === 'asc' ? 'desc' : 'asc';

        clearSortState(table);
        header.dataset.sortDir = nextDirection;
        header.classList.add(nextDirection === 'asc' ? 'sorted-asc' : 'sorted-desc');
        header.setAttribute('aria-sort', nextDirection === 'asc' ? 'ascending' : 'descending');
        sortTable(table, columnIndex, nextDirection);
    }

    function initDataTableSort(table) {
        if (!table || table.dataset.sortInitialized === 'true') {
            return;
        }

        const headerRow = table.tHead && table.tHead.rows[0];
        if (!headerRow) {
            return;
        }

        Array.from(headerRow.cells).forEach(function (header, columnIndex) {
            if (!isSortableHeader(header)) {
                return;
            }

            header.classList.add('sortable');
            header.setAttribute('role', 'button');
            header.setAttribute('tabindex', '0');
            header.setAttribute('aria-sort', 'none');

            header.addEventListener('click', function () {
                toggleSort(table, header, columnIndex);
            });

            header.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    toggleSort(table, header, columnIndex);
                }
            });
        });

        table.dataset.sortInitialized = 'true';
    }

    function initAllDataTableSort(root) {
        (root || document).querySelectorAll('table.data-table').forEach(initDataTableSort);
    }

    window.initDataTableSort = initDataTableSort;
    window.initAllDataTableSort = initAllDataTableSort;

    document.addEventListener('DOMContentLoaded', function () {
        initAllDataTableSort(document);
    });
})();
