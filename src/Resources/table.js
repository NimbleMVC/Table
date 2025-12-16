(function ($) {
    const settings = {
        debug: true
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

                $table.on('click', 'thead th[data-sortable="true"]', function () {
                    const $sortIcon = $(this).find('.sort-icon'),
                        actual = $sortIcon.attr('data-sort');

                    methods._fetchAndUpdate($table.attr('id'), {'sort_column_key': $(this).attr('data-key'), 'sort_column_direction': actual})
                });

                $table.on('click', '.action-checkbox-select-all', function () {
                    $(this).closest('table').find('.ajax-action-checkbox').prop('checked', $(this).prop('checked'));
                });

                $table.on('click, change', '.ajax-action-checkbox', function () {
                    const $selectAll = $(this).closest('table').find('.action-checkbox-select-all'),
                        $boxes = $(this).closest('table').find('.ajax-action-checkbox');

                    if ($boxes.filter(':checked').length === 0) {
                        $selectAll.prop("checked", false).prop("indeterminate", false);
                        $selectAll.prop("checked", false).prop("checked", false);
                    } else if ($boxes.filter(':checked').length === $boxes.length) {
                        $selectAll.prop("checked", false).prop("indeterminate", false);
                        $selectAll.prop("checked", false).prop("checked", true);
                    } else {
                        $selectAll.prop("checked", false).prop("indeterminate", true);
                    }
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
            if (methods.__isObject(formData)) {
                formData = methods.__objectToFormData(formData);
                formData.append('table_action_id', tableId);
            }

            if (settings.debug) {
                console.log('table reload', tableId, formData);

                for (const [key, value] of formData.entries()) {
                    console.log('Data:', key, value);
                }
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
        },
        __isObject: function(value) {
            return value && typeof value === 'object' && !(value instanceof FormData) && !(value instanceof File);
        },
        __objectToFormData: function(obj, form, namespace) {
            const formData = form || new FormData();

            for (let property in obj) {
                if (!obj.hasOwnProperty(property) || obj[property] === undefined || obj[property] === null) continue;

                const formKey = namespace ? `${namespace}[${property}]` : property;

                if (obj[property] instanceof Date) {
                    formData.append(formKey, obj[property].toISOString());
                } else if (typeof obj[property] === 'object' && !(obj[property] instanceof File)) {
                    objectToFormData(obj[property], formData, formKey);
                } else {
                    formData.append(formKey, obj[property]);
                }
            }

            return formData;
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
