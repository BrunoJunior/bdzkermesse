$(function() {
    var affichageLibelle = function () {
        debugger;
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
    affichageLibelle();
    $('#recette_report_stock').on('change', affichageLibelle);
});