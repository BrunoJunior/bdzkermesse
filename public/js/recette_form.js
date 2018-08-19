$(function() {
    var affichageLibelle = function () {
        var libelleField = $('#recette_libelle');
        var isReportStock = $('#recette_report_stock').is(':checked');
        var initVal = libelleField.data('init-val');
        libelleField.closest('.form-group').toggle(!isReportStock);
        if (isReportStock) {
            libelleField.data('init-val', libelleField.val());
            libelleField.val('Stock N+1');
        } else if (typeof initVal === 'string') {
            libelleField.val(initVal);
        }
    };

    var affichageZonesLieesActivites = function () {
        $('form [data-activites_autorisees]').each(function () {
            var zone = $(this);
            var activitesAutorisees = zone.data('activites_autorisees');
            var activite = parseInt($('#recette_activite').val());
            zone.closest('.form-group').toggle(activitesAutorisees.indexOf(activite) > -1);
        });
    }

    affichageLibelle();
    affichageZonesLieesActivites();

    $('#recette_report_stock').on('change', affichageLibelle);
    $('#recette_activite').on('change', affichageZonesLieesActivites);
});