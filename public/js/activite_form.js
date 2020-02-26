$(function() {
    $('#activite_creneaux fieldset').each(function (){
        addRemoveLinkCollectionWidget($(this));
    });
    var affichageAucunCreneau = function () {
        var nbFieldest = $('#activite_creneaux fieldset').length;
        var noCreneau = $('#no_creneau');
        if (nbFieldest === 0) {
            $('#activite_creneaux').append('<fieldset class="form-group" id="no_creneau">Aucun créneau horaire</fieldset>');
        } else if (noCreneau.length > 0) {
            noCreneau.remove();
        }
    };
    affichageAucunCreneau();
    $('form').on('collection-widget-added', function (event) {
        affichageAucunCreneau();
    });
    $('form').on('collection-widget-removed', function () {
        affichageAucunCreneau();
    });
});
