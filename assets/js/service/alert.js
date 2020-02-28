const $ = require('jquery');

export function addAlert(message, type="success") {
    $('#alerts-container').append('<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert" style="z-index: 1000;">' +
        message + '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
}
