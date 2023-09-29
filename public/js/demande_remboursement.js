$(function() {
    // Par défaut, tous les tickets cochés
    $('#demande_remboursement_tickets input[type="checkbox"]').prop( "checked", true );

    var calculerMontantSel = function () {
        let montant = 0;
        $('#demande_remboursement_tickets input:checked').each(function () {
            montant += parseInt($(this).data("montant"));
        });
        // Bug affichage décimales
        let dec = montant%100;
        dec = dec < 10 ? '0' + dec : dec;
        $("#demande_remboursement_montant").val("" + Math.floor(montant/100) + "," + dec);
    };

    calculerMontantSel();
    $('#demande_remboursement_tickets input').on('change', calculerMontantSel);
});