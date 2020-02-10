$(function() {

    $("#inscription_email").on('change', function () {
        const champ = $(this);
        const nom = $("#inscription_nom");
        const portable = $("#inscription_portable");
        $.ajax({
            url: champ.data('onchange') + '?email=' + encodeURIComponent(champ.val()),
            context: champ
        }).done(function(retour) {
            nom.val(retour.identite);
            nom.prop('disabled', true);
            portable.val(retour.portable);
        }).fail(function () {
            nom.prop('disabled', false);
        });
    });


});
