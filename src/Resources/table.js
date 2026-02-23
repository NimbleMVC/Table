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

                $table.on('dblclick', '.table-cell-editable', function (event) {
                    if ($(event.target).closest('.table-cell-editor').length) {
                        return;
                    }

                    methods._enableCellEdit($(this));
                });

                $table.on('click', '.table-cell-edit-trigger', function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    methods._enableCellEdit($(this).closest('.table-cell-editable'));
                });

                $table.on('keydown', '.table-cell-editor :input', function (event) {
                    if (event.key === 'Escape') {
                        methods._disableCellEdit($(this).closest('.table-cell-editable'));
                    }
                });

                $table.on('click', function (event) {
                    if ($(event.target).closest('.table-cell-editable').length === 0) {
                        methods._disableAllCellEdit($table);
                    }
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

                methods._restoreEditableState($table);
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
                            methods._restoreEditableState($(currentElement));
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
                tableId = $(input).closest('.table-module').attr('id'),
                $editableCell = $(input).closest('.table-cell-editable');

            let value;

            if ($(input).attr('type') === 'checkbox') {
                value = $(input).prop('checked') ? 1 : 0;
            } else {
                value = $(input).val();
            }

            formData.append($(input).attr('name'), value);

            if ($editableCell.length) {
                formData.append('table_edit_column', $editableCell.attr('data-edit-column') || '');
                formData.append('table_edit_id', $editableCell.attr('data-edit-id') || '');
                formData.append('table_edit_value', value);
            }

            formData.append('table_action_id', tableId);

            methods._fetchAndUpdate(tableId, formData);
        },
        _enableCellEdit: function ($editableCell) {
            if (!$editableCell || !$editableCell.length || $editableCell.hasClass('is-editing')) {
                return;
            }

            const $table = $editableCell.closest('.table-module');

            methods._disableAllCellEdit($table, $editableCell);

            $editableCell.addClass('is-editing');
            $editableCell.find('.table-cell-value').addClass('d-none');
            $editableCell.find('.table-cell-editor').removeClass('d-none');
            $editableCell.find('.table-cell-edit-trigger').addClass('d-none');

            const $input = $editableCell.find('.table-cell-editor :input:visible').first();

            if ($input.length) {
                $input.trigger('focus');
            }
        },
        _disableCellEdit: function ($editableCell) {
            if (!$editableCell || !$editableCell.length) {
                return;
            }

            $editableCell.removeClass('is-editing');
            $editableCell.find('.table-cell-value').removeClass('d-none');
            $editableCell.find('.table-cell-editor').addClass('d-none');
            $editableCell.find('.table-cell-edit-trigger').removeClass('d-none');
        },
        _disableAllCellEdit: function ($table, $except) {
            if (!$table || !$table.length) {
                return;
            }

            $table.find('.table-cell-editable.is-editing').each(function () {
                if ($except && $except.length && this === $except.get(0)) {
                    return;
                }

                methods._disableCellEdit($(this));
            });
        },
        _restoreEditableState: function ($table) {
            if (!$table || !$table.length) {
                return;
            }

            const $editableCell = $table.find('.table-cell-editable.table-cell-editable-open').first();

            if ($editableCell.length) {
                methods._enableCellEdit($editableCell);
            }
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
