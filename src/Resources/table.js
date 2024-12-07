(function ($) {
    const methods = {
        init: function () {
            return this.each(function () {
                const $table = $(this);

                if (!$table.hasClass('table-module')) {
                    $table.addClass('table-module');
                }

                // Obsługa kliknięcia w link
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

                // Obsługa Enter w formularzu
                $table.on('keydown', '.ajax-form', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        methods._submitFormData(this);
                    }
                });

                // Obsługa zmiany w formularzu
                $table.on('change', '.ajax-form', function (event) {
                    if (event.target.tagName === 'SELECT' || event.target.type === 'date') {
                        methods._submitFormData(this);
                    }
                });
            });
        },
        reload: function () {
            return this.each(function () {
                const $table = $(this),
                    tableId = $table.attr('id'),
                    formData = new FormData();

                formData.append('table_action_id', tableId);

                methods._fetchAndUpdate(tableId, formData);
            });
        },
        _fetchAndUpdate: function (tableId, formData) {
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser(),
                        doc = parser.parseFromString(html, 'text/html'),
                        newElement = doc.getElementById(tableId);

                    if (newElement) {
                        const currentElement = document.getElementById(tableId);

                        if (currentElement) {
                            currentElement.innerHTML = newElement.innerHTML;
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
