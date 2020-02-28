import '../scss/activite_form.scss';
import {addRemoveLink} from "./service/collection.widget";
const $ = require('jquery');

const affichageAucunCreneau = function () {
    const nbFieldest = $('#activite_creneaux fieldset').length;
    const noCreneau = $('#no_creneau');
    if (nbFieldest === 0) {
        $('#activite_creneaux').append('<fieldset class="form-group" id="no_creneau">Aucun créneau horaire</fieldset>');
    } else if (noCreneau.length > 0) {
        noCreneau.remove();
    }
};

$(function() {
    $('#activite_creneaux fieldset').each((_, element) => addRemoveLink($(element)));
    affichageAucunCreneau();
    $('form').on('collection-widget-added, collection-widget-removed', affichageAucunCreneau);
});

