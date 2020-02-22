function addRemoveLinkCollectionWidget(element) {
    const removeFormButton = $('<button type="button" class="btn btn-danger collection-widget-remove"><i class="fas fa-times"></i></button>');
    element.append(removeFormButton);
    removeFormButton.on('click', function() {
        const parent = element.parent();
        element.remove();
        parent.trigger('collection-widget-removed');
    });
}

function getSearchValue(table, index) {
    let value = '';
    const th = table.find('thead tr th').get(index);
    const input = $(th).find('input');
    if (input.length === 1) {
        value = input.val().toLowerCase();
    }
    return value;
}

function searchInTable(table) {
    const lignes = table.find('tbody tr');
    lignes.each(function () {
        const ligne = $(this);
        const colonnes = ligne.find('td');
        let display = true;
        colonnes.each(function () {
            const colonne = $(this);
            const index = colonnes.index(colonne);
            const search = getSearchValue(table, index + 1);
            const valeur = colonne.text().toLowerCase();
            if (search !== '' && valeur.indexOf(search) === -1) {
                display = false;
            }
        });
        ligne.toggle(display);
    });
}

function addAlert(message, type="success") {
    $('#alerts-container').append('<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert" style="z-index: 1000;">' +
        message + '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
}

function reloadAjaxElement(element) {
    const url = element.data('ajax-url');
    if (!element.is('.ajax') || url === undefined) {
        return;
    }
    element.html('<div class="d-flex justify-content-center align-items-center loader"><div class="spinner-grow" role="status" style="width: 4rem; height: 4rem;"><span class="sr-only">Chargement en cours</span></div>');
    $.ajax({
        url: url,
        context: element[0]
    }).done(function(html) {
        element.html(html);
    })
}

$(function() {
    $("#menu").metisMenu();

    // add-collection-widget.js
    $('.add-another-collection-widget').click(function () {
        const list = $($(this).attr('data-list'));
        // Try to find the counter of the list
        let counter = list.data('widget-counter') | list.children().length;
        // If the counter does not exist, use the length of the list
        if (!counter) { counter = list.children().length; }

        // grab the prototype template
        let newWidget = list.attr('data-prototype');
        // replace the "__name__" used in the id and name of the prototype
        // with a number that's unique to your emails
        // end name attribute looks like name="contact[emails][2]"
        newWidget = newWidget.replace(/__name__/g, counter);
        // Increase the counter
        counter++;
        // And store it, the length cannot be used if deleting widgets is allowed
        list.data('widget-counter', counter);

        // create a new list element and add it to the list
        const element = $(newWidget);
        list.append(element);

        list.trigger('collection-widget-added', [element]);

        addRemoveLinkCollectionWidget(element);
    });

    // Date picker
    $('.js-datepicker').datepicker({
        language: 'fr',
        format: 'dd/mm/yyyy'
    });

    $('.draggable').draggable({
        revert: true,
        handle: '.drag-grip',
        helper: 'clone'
    });

    $('.droppable').droppable({
        accept : '.draggable',
        tolerance: "pointer"
    });

    // Tooltip
    $('[data-toggle="tooltip"]').tooltip();

    // Ajax simple
    $('.ajax').each(function () {reloadAjaxElement($(this));});

    $('.ajax-reload').on('ajax:success', function () {document.location.reload();});

    // ==================== MODAL =========================
    const modaleForm = $('#main-modal-form');
    // La modale de formulaire est cachée
    modaleForm.on('hidden.bs.modal', function () {$('head .ajax-stylesheet').remove();});
    modaleForm.on('submit', 'form', function (e) {
        const form = $(this);
        e.preventDefault(); // avoid to execute the actual submit of the form.
        const formData = new FormData(form.get(0));
        $.ajax({
            type: form.attr('method'),
            url: form.attr('action'),
            data: formData,
            processData: false,
            contentType: false,
            error: function(jqXHR, textStatus, errorMessage) {
                addAlert(errorMessage, "danger");
            },
            success: function(data) {
                if (typeof data === 'object' && data.action === 'close') {
                    const origin = modaleForm.data('origin');
                    modaleForm.trigger('form-modal:valide', [origin]);
                    if (origin) {
                        $(origin).trigger('ajax:success');
                    }
                    modaleForm.modal('hide');
                    addAlert(data.message || "Enregistrement effectué avec succès");
                } else {
                    modaleForm.html(data);
                }
            }
        });
    });
    const body = $('body');
    body.on('click', '.modal .dismiss', function () {
        $(this).closest('.modal').modal('hide');
    });
    body.on('click', '[data-ajax]', function () {
        $('head .ajax-stylesheet').remove();
        const btn = $(this);
        const destination = $(btn.data('ajax-destination') || '#main-modal-form');
        const url = btn.data('ajax');
        if (url === undefined || destination.length === 0) {
            return;
        }
        destination.data('origin', btn);
        if (destination.is('.modal')) {
            destination.html('<div class="modal-dialog" role="document"><div class="modal-content">' +
                '<div class="modal-body d-flex flex-column justify-content-center"><div class="text-center">Chargement en cours</div>' +
                '<div class="text-center"><div class="spinner-grow" role="status" style="width: 3rem; height: 3rem;"></div></div></div>' +
                '</div></div>');
            destination.modal('show');
        }
        $.ajax({
            url: url,
            context: btn[0],
            error: function(jqXHR, textStatus, errorMessage) {
                addAlert(errorMessage, "danger");
            },
            success: function(data) {
                if (typeof data === 'object' && data.action === 'close') {
                    const origin = modaleForm.data('origin');
                    modaleForm.trigger('form-modal:valide', [origin]);
                    if (origin) {
                        $(origin).trigger('ajax:success');
                    }
                    modaleForm.modal('hide');
                    addAlert(data.message || "Traitement effectué avec succès");
                } else {
                    const content = $(data);
                    const associatedStylesheets = content.find('#modal-stylesheets').children();
                    associatedStylesheets.addClass('ajax-stylesheet');
                    $('head').append(associatedStylesheets);
                    destination.html(data);
                }
            }
        })
    });
    // ==================== /MODAL =========================

    // Afficher / Cacher le menu slim
    $('#show-menu-slim').on('click', function () {
        $('#menu-slim').toggleClass('d-none');
    });

    // Recherche dans tableau
    $('table thead .btn-filter').on('click', function () {
        searchInTable($(this).closest('table'));
    });

    $('table thead input').on('keyup', function (e) {
        const code = e.which;
        if(parseInt(code) === 13 || parseInt(code) === 9) {
            searchInTable($(this).closest('table'));
        }
    });

});

function getRandomColor() {
    const letters = '0123456789ABCDEF';
    let color = '#';
    for (let i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}
