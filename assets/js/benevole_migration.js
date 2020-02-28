const $ = require('jquery');

$(function () {
    const checkboxes = $('form input[type="checkbox"]');
    checkboxes.on('change', function () {
        const nbSelected = checkboxes.filter(':checked').length;
        const max = parseInt($('#modal-migration-content').data('benevoles-max'));
        if (nbSelected >= max) {
            checkboxes.filter(':not(:checked)').prop('disabled', true);
        } else {
            checkboxes.prop('disabled', false);
        }
    })
});
