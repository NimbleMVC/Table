window.addEventListener('DOMContentLoaded', function() {
    document.body.addEventListener('click', function(event) {
        const link = event.target.closest('.ajax-link');

        if (link) {
            if (!link.closest('.table-module')) {
                return;
            }

            event.preventDefault();

            const urlParams = new URLSearchParams(link.getAttribute('href')),
                page = urlParams.get('page'),
                table_id = link.closest('.table-module').getAttribute('id'),
                formData = new FormData();

            formData.append('page', page);
            formData.append('table_action_id', table_id);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser(),
                        doc = parser.parseFromString(html, 'text/html'),
                        newElement = doc.getElementById(table_id);

                    if (newElement) {
                        const currentElement = document.getElementById(table_id);

                        if (currentElement) {
                            currentElement.innerHTML = newElement.innerHTML;
                        }
                    }
                })
                .catch(error => {
                    console.error('Table error:', error);
                });
        }
    });

    document.body.addEventListener('keydown', function(event) {
        if (event.target.matches('.ajax-form') && event.key === 'Enter') {
            event.preventDefault();
            submitFormData(event.target);
        }
    });

    document.body.addEventListener('change', function(event) {
        if (event.target.matches('.ajax-form') && (event.target.tagName === 'SELECT' || event.target.type === 'date')) {
            submitFormData(event.target);
        }
    });

    function submitFormData(input) {
        const formData = new FormData(),
            table_id = input.closest('.table-module').getAttribute('id');

        formData.append(input.name, input.value);
        formData.append('table_action_id', table_id);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser(),
                    doc = parser.parseFromString(html, 'text/html'),
                    newElement = doc.getElementById(table_id);

                if (newElement) {
                    const currentElement = document.getElementById(table_id);

                    if (currentElement) {
                        currentElement.innerHTML = newElement.innerHTML;
                    }
                }
            })
            .catch(error => {
                console.error('Input error:', error);
            });
    }

});