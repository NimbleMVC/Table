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

            return fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(html => {
                    if (settings.debug) {
                        console.log('table reload response', tableId, [html], window.location.href);
                    }

                    const parser = new DOMParser(),
                        doc = parser.parseFromString(html, 'text/html'),
                        newElement = doc.getElementById(tableId);

                    if (newElement) {
                        if (settings.debug) {
                            console.log('table reload update content', tableId, newElement);
                        }

                        const currentElement = document.getElementById(tableId);

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

            formData.append($(input).attr('name'), $(input).val());
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
