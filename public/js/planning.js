$(function() {
    const hauteurEnteteInit = $(".planning-entete").height();
    const fnDisplayCreneaux = function () {
        $('.activite-creneau.creneau-incomplet').toggle(!$("#afficherIncomplets").prop('checked'));
        $(".planning-entete").height((100 * $("#planning").height() / hauteurEnteteInit) + "%");
    };
    $("#afficherIncomplets").on('change', fnDisplayCreneaux);
    fnDisplayCreneaux();

    $('#copy-link').on('click', function () {
        const toast = $("#toast");
        const tempInput = document.createElement("input");
        tempInput.style = "position: absolute; left: -1000px; top: -1000px";
        tempInput.value = $(this).data('link');
        document.body.appendChild(tempInput);
        tempInput.select();
        if (document.execCommand( 'copy' )) {
            toast.find('.toast-body').text("Lien copié dans le presse-papier");
        } else {
            toast.find('.toast-body').text($(this).data('lien'));
        }
        toast.toast('show');
        document.body.removeChild(tempInput);
    });
});
