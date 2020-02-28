import '../scss/ticket_form.scss';
import {addRemoveLink} from "./service/collection.widget";

const $ = require('jquery');

const affichageAucuneActivite = function () {
    const nbFieldest = $('#ticket_depenses fieldset').length;
    const noActivity = $('#no_activity');
    if (nbFieldest === 0) {
        $('#ticket_depenses').append('<fieldset class="form-group" id="no_activity">Aucune activité associée</fieldset>');
    } else if (noActivity.length > 0) {
        noActivity.remove();
    }
};

$(function() {
    const form = $('form');
    $('#ticket_depenses fieldset').each((_, element) => addRemoveLink($(element)));
    affichageAucuneActivite();
    form.on('collection-widget-removed', affichageAucuneActivite);

    // Calcul du montant restant à allouer pour faciliter la saisie
    form.on('collection-widget-added', function (event) {
        let left = parseFloat($('#ticket_montant').val().replace(/,/g, '.'));
        const montants = $(event.target).find('input[name $= "[montant]"]');
        montants.each(function () {
            const montant = parseFloat($(this).val().replace(/,/g, '.'));
            if (montant) {
                left = left - montant;
            }
        });
        if (left > 0.0) {
            montants.last().val(left.toString(10).replace(/[.]/g, ','));
        }
        affichageAucuneActivite();
    });

    $('form .custom-file label').remove();
    $('form .custom-file input').removeClass('custom-file-input');
    $('form .custom-file').removeClass('file');
});
