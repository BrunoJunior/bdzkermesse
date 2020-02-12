$(function() {
    const fnDisplayCreneaux = function () {
        $('.activite-creneau.creneau-incomplet').toggle($("#afficherIncomplets").prop('checked'));
    };
    $("#afficherIncomplets").on('change', fnDisplayCreneaux);
    fnDisplayCreneaux();
});
