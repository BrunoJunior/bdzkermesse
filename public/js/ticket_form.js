$(function() {

    $('#ticket_depenses fieldset').each(function (){
        addRemoveLinkCollectionWidget($(this));
    });

    var affichageAucuneActivite = function () {
        var nbFieldest = $('#ticket_depenses fieldset').length;
        var noActivity = $('#no_activity');
        if (nbFieldest === 0) {
            $('#ticket_depenses').append('<fieldset class="form-group" id="no_activity">Aucune activité associée</fieldset>');
        } else if (noActivity.length > 0) {
            noActivity.remove();
        }
    };

    affichageAucuneActivite();

    // Calcul du montant restant à allouer pour faciliter la saisie
    $('form').on('collection-widget-added', function (event) {
        var left = parseFloat($('#ticket_montant').val().replace(/,/g, '.'));
        var montants = $(event.target).find('input[name $= "[montant]"]');
        montants.each(function () {
            var montant = parseFloat($(this).val().replace(/,/g, '.'));
            if (montant) {
                left = left - montant;
            }
        });
        if (left > 0.0) {
            montants.last().val(left.toString(10).replace(/[.]/g, ','));
        }
        affichageAucuneActivite();
    });

    $('form').on('collection-widget-removed', function () {
        affichageAucuneActivite();
    });

    $('form .custom-file label').remove();
    $('form .custom-file input').removeClass('custom-file-input');
    $('form .custom-file').removeClass('file');
});
