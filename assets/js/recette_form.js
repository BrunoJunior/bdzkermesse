const $ = require('jquery');

$(function() {
    const affichageLibelle = function () {
        const libelleField = $('#recette_libelle');
        const isReportStock = $('#recette_report_stock').is(':checked');
        const initVal = libelleField.data('init-val');
        libelleField.closest('.form-group').toggle(!isReportStock);
        if (isReportStock) {
            libelleField.data('init-val', libelleField.val());
            libelleField.val('Stock N+1');
        } else if (typeof initVal === 'string') {
            libelleField.val(initVal);
        }
    };

    const affichageZonesLieesActivites = function () {
        $('form [data-activites_autorisees]').each(function () {
            const zone = $(this);
            const activitesAutorisees = zone.data('activites_autorisees');
            const activite = parseInt($('#recette_activite').val());
            zone.closest('.form-group').toggle(activitesAutorisees.indexOf(activite) > -1);
        });
    };

    affichageLibelle();
    affichageZonesLieesActivites();

    $('#recette_report_stock').on('change', affichageLibelle);
    $('#recette_activite').on('change', affichageZonesLieesActivites);
});
