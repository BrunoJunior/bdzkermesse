$(function() {
    const hauteurEnteteInit = $(".planning-entete").height();
    const fnDisplayCreneaux = function () {
        $('.activite-creneau.creneau-incomplet').toggle(!$("#afficherIncomplets").prop('checked'));
        $(".planning-entete").height((100 * $("#planning").height() / hauteurEnteteInit) + "%");
    };
    $("#afficherIncomplets").on('change', fnDisplayCreneaux);
    fnDisplayCreneaux();
});
