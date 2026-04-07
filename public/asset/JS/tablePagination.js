(function () {
    function getRows(tbody) {
        return Array.from(tbody.querySelectorAll('tr'));
    }

    function getListItems(container, selector) {
        return Array.from(container.querySelectorAll(selector));
    }

    function setRowVisible(item, visible) {
        item.classList.toggle('table-pagination-hidden', !visible);
        if (visible) {
            item.style.removeProperty('display');
        } else {
            item.style.setProperty('display', 'none', 'important');
        }
    }

    function hasEmptyMessage(items) {
        return items.some(function (item) {
            return item.classList.contains('empty-message') || item.classList.contains('no-results') || item.querySelector('.empty-message, .no-results');
        });
    }

    function createButton(label, disabled, className, onClick) {
        var button = document.createElement('button');
        button.type = 'button';
        button.className = className;
        button.textContent = label;
        button.disabled = !!disabled;
        button.addEventListener('click', onClick);
        return button;
    }

    function scrollToTop(target) {
        if (!target || typeof target.scrollIntoView !== 'function') {
            return;
        }

        try {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (error) {
            target.scrollIntoView(true);
        }
    }

    function initPagination(target, options) {
        if (!target || target.dataset.paginationInit === 'true') {
            return;
        }

        var itemsSource = options && options.itemsSource ? options.itemsSource : target;
        var getItems = options && options.getItems ? options.getItems : function () { return []; };
        var insertAfter = options && options.insertAfter ? options.insertAfter : target;

        if (!itemsSource) {
            return;
        }

        target.dataset.paginationInit = 'true';

        var pageSize = parseInt(target.dataset.pageSize || '8', 10);
        if (!isFinite(pageSize) || pageSize < 1) {
            pageSize = 8;
        }

        var state = {
            page: 1,
            totalPages: 1,
            pageSize: pageSize
        };

        var controls = document.createElement('div');
        controls.className = 'table-pagination-controls';
        insertAfter.insertAdjacentElement('afterend', controls);

        function render() {
            var items = getItems();
            var empty = hasEmptyMessage(items);
            var totalItems = empty ? 0 : items.length;

            state.totalPages = Math.max(1, Math.ceil(totalItems / state.pageSize));
            if (state.page > state.totalPages) {
                state.page = state.totalPages;
            }

            if (empty || totalItems <= state.pageSize) {
                items.forEach(function (item) {
                    setRowVisible(item, true);
                });
                controls.innerHTML = '';
                controls.hidden = true;
                return;
            }

            var start = (state.page - 1) * state.pageSize;
            var end = start + state.pageSize;

            items.forEach(function (item, index) {
                setRowVisible(item, index >= start && index < end);
            });

            controls.hidden = false;
            controls.innerHTML = '';

            var prev = createButton('Precedent', state.page === 1, 'pagination-btn pagination-nav', function () {
                if (state.page > 1) {
                    state.page -= 1;
                    render();
                    scrollToTop(insertAfter);
                }
            });
            controls.appendChild(prev);

            for (var i = 1; i <= state.totalPages; i += 1) {
                (function (pageNumber) {
                    var btn = createButton(String(pageNumber), false, 'pagination-btn pagination-page', function () {
                        state.page = pageNumber;
                        render();
                        scrollToTop(insertAfter);
                    });
                    if (pageNumber === state.page) {
                        btn.classList.add('active');
                        btn.setAttribute('aria-current', 'page');
                    }
                    controls.appendChild(btn);
                }(i));
            }

            var next = createButton('Suivant', state.page === state.totalPages, 'pagination-btn pagination-nav', function () {
                if (state.page < state.totalPages) {
                    state.page += 1;
                    render();
                    scrollToTop(insertAfter);
                }
            });
            controls.appendChild(next);
        }

        var rafId = null;
        var observer = new MutationObserver(function () {
            if (rafId !== null) {
                cancelAnimationFrame(rafId);
            }
            rafId = requestAnimationFrame(function () {
                rafId = null;
                render();
            });
        });

        observer.observe(itemsSource, { childList: true, subtree: true });
        render();
    }

    function initTablePagination(table) {
        var tbody = table ? table.querySelector('tbody') : null;
        if (!tbody) {
            return;
        }

        initPagination(table, {
            itemsSource: tbody,
            getItems: function () {
                return getRows(tbody);
            },
            insertAfter: table
        });
    }

    function initListPagination(container) {
        if (!container) {
            return;
        }

        var selector = container.dataset.pageItem || '.absence-card';
        initPagination(container, {
            itemsSource: container,
            getItems: function () {
                return getListItems(container, selector);
            },
            insertAfter: container
        });
    }

    function bootstrap() {
        var tables = Array.from(document.querySelectorAll('table[data-pagination="true"]'));
        tables.forEach(initTablePagination);

        var lists = Array.from(document.querySelectorAll('[data-pagination-list="true"]'));
        lists.forEach(initListPagination);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootstrap);
    } else {
        bootstrap();
    }
}());
