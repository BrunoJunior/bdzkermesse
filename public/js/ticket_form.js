$(function() {

    $('#ticket_depenses fieldset').each(function (){
        addRemoveLinkCollectionWidget($(this));
    });

    // Calcul du montant restant à allouer pour faciliter la saisie
    $('form').on('collection-widget-added', function (event) {
        var left = parseFloat($('#ticket_montant').val());
        var montants = $(event.target).find('input[name $= "[montant]"]');
        montants.each(function () {
            var montant = parseFloat($(this).val());
            if (montant) {
                left = left - montant;
            }
        });
        if (left > 0.0) {
            montants.last().val(left);
        }
    });
});