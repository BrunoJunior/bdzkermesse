import {addAlert} from "./alert";

const $ = require('jquery');

export function initModal(fnDone) {
    const body = $('body');

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
                    if (typeof fnDone === 'function') {
                        fnDone();
                    }
                }
            }
        });
    });
    body.on('click', '.modal .dismiss', function () {
        $(this).closest('.modal').modal('hide');
    });
    body.on('click', '[data-ajax]', function () {
        $('head .ajax-stylesheet').remove();
        const btn = $(this);
        const destination = $(btn.data('ajax-destination') || '#main-modal-form');
        const url = btn.data('ajax');
        let validation = btn.data('ajax-validation');
        if (typeof validation === 'boolean') {
            validation = 'Êtes-vous sûr ?';
        }
        if (url === undefined || destination.length === 0 || (!!validation && !confirm(validation))) {
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
                    if (destination.is('.modal')) {
                        destination.trigger('form-modal:valide', [btn]);
                        const hide = function () {destination.modal('hide');};
                        hide();
                        destination.on('shown.bs.modal', hide);
                        destination.on('hide.bs.modal', function () {
                            destination.off('shown.bs.modal', hide);
                        })
                    }
                    btn.trigger('ajax:success');
                    addAlert(data.message || "Traitement effectué avec succès");
                } else {
                    const content = $(data);
                    const associatedStylesheets = content.find('#modal-stylesheets').children();
                    associatedStylesheets.addClass('ajax-stylesheet');
                    $('head').append(associatedStylesheets);
                    destination.html(data);
                    if (typeof fnDone === 'function') {
                        fnDone();
                    }
                }
            }
        })
    });
}
