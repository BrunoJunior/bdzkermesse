const $ = require('jquery');

function reloadAjaxElement(element, fnDone) {
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
        if (typeof fnDone === 'function') {
            fnDone();
        }
    })
}

export function initAjax(fnDone) {
    $('.ajax-reload').on('ajax:success', function () {document.location.reload();});
    $('.ajax-reload-element').on('ajax:success', function () {reloadAjaxElement($(this).closest('.ajax'), fnDone);});
    $('.ajax-remove').on('ajax:success', function () {$(this).closest('.ajax').remove();});
}

export function initSimpleAjax(fnDone) {
    $('.ajax').each(function () {reloadAjaxElement($(this), fnDone);});
}
