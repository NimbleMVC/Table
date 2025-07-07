(function ($) {
    const settings = {
        debug: false
    };

    const methods = {
        init: function () {
            return this.each(function () {
                const $table = $(this);

                if (!$table.hasClass('table-module')) {
                    $table.addClass('table-module');
                }

                $table.on('click', '.ajax-link', function (event) {
                    event.preventDefault();

                    const link = $(this),
                        urlParams = new URLSearchParams(link.attr('href')),
                        page = urlParams.get('page'),
                        tableId = link.closest('.table-module').attr('id'),
                        formData = new FormData();

                    formData.append('page', page);
                    formData.append('table_action_id', tableId);

                    methods._fetchAndUpdate(tableId, formData);
                });

                $table.on('keydown', '.ajax-form', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        methods._submitFormData(this);
                    }
                });

                $table.on('change', '.ajax-form', function (event) {
                    if (event.target.tagName === 'SELECT' || event.target.type === 'date') {
                        methods._submitFormData(this);
                    }
                });

                $table.on('change', '.ajax-checkbox', function (event) {
                    methods._submitFormData(this);
                });

                $table.on('click', '.ajax-action-button', function (event) {
                    event.preventDefault();

                    const selectedValues = $table.find('.ajax-action-checkbox:checked').map(function() {return $(this).val();}).get().join(','),
                        $button = $(this),
                        originalHref = $button.attr('href'),
                        simulatedHref = originalHref + '/' + selectedValues;

                    $button.attr('href', simulatedHref);

                    setTimeout(() => {
                        if (originalHref) {
                            $button.attr('href', originalHref);
                        }
                    }, 100);
                });

                $table.on('click', 'thead th[data-sortable="true"]', function (event) {
                    const $sortIcon = $(this).find('.sort-icon'),
                        actual = $sortIcon.attr('data-sort');

                    if (actual === 'none') {
                        $sortIcon.html('<i class="fas fa-sort-down mt-1" style="opacity: 0.4;"></i>');
                        $sortIcon.attr('data-sort', 'ASC');
                    } else if (actual === 'ASC') {
                        $sortIcon.html('<i class="fas fa-sort-up mt-1" style="opacity: 0.4;"></i>');
                        $sortIcon.attr('data-sort', 'DESC');
                    } else if (actual === 'DESC') {
                        $sortIcon.html('<i class="fas fa-sort mt-1" style="opacity: 0.4;"></i>');
                        $sortIcon.attr('data-sort', 'none');
                    }

                    console.log($sortIcon.attr('data-sort'));
                });
            });
        },
        reload: function (callback) {
            let $element = $(this);

            if ($element[0].nodeType === Node.DOCUMENT_NODE) {
                $element = $('.table-module');
            }

            if (settings.debug) {
                console.log('table reload', $element);
            }

            return $element.each(function () {
                const $table = $(this),
                    tableId = $table.attr('id'),
                    formData = new FormData();

                formData.append('table_action_id', tableId);

                methods._fetchAndUpdate(tableId, formData).then(() => {
                    if (typeof callback === 'function') {
                        callback.call($table);
                    }
                });
            });
        },
        _fetchAndUpdate: function (tableId, formData) {
            if (settings.debug) {
                console.log('table reload', tableId, formData);
            }

            let url = window.location.href,
                dataurl = $('#' + tableId).attr('data-url');

            if (dataurl) {
                url = dataurl;
            }

            return fetch(url, {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(html => {
                    if (settings.debug) {
                        console.log('table reload response', tableId, [html], url);
                    }

                    const parser = new DOMParser(),
                        doc = parser.parseFromString(html, 'text/html'),
                        newElement = doc.getElementById(tableId);

                    if (newElement) {
                        if (settings.debug) {
                            console.log('table reload update content', tableId, newElement);
                        }

                        const currentElement = document.getElementById(tableId);

                        $('#' + tableId).trigger('ajaxTable.reload', [currentElement]);

                        if (currentElement) {
                            currentElement.innerHTML = newElement.innerHTML;
                        }
                    } else {
                        if (settings.debug) {
                            console.log('table reload no new content', tableId);
                        }
                    }
                })
                .catch(error => {
                    console.error('Table error:', error);
                });
        },
        _submitFormData: function (input) {
            const formData = new FormData(),
                tableId = $(input).closest('.table-module').attr('id');

            let value;

            if ($(input).attr('type') === 'checkbox') {
                value = $(input).prop('checked') ? 1 : 0;
            } else {
                value = $(input).val();
            }

            formData.append($(input).attr('name'), value);
            formData.append('table_action_id', tableId);

            methods._fetchAndUpdate(tableId, formData);
        }
    };

    $.fn.ajaxTable = function (methodOrOptions) {
        if (methods[methodOrOptions]) {
            return methods[methodOrOptions].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof methodOrOptions === 'object' || !methodOrOptions) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + methodOrOptions + ' does not exist on jQuery.ajaxTable');
        }
    };
}(jQuery));
