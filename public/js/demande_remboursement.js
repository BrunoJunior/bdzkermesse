$(function() {
    // Par défaut, tous les tickets cochés
    $('#demande_remboursement_tickets input[type="checkbox"]').prop( "checked", true );

    var calculerMontantSel = function () {
        var montant = 0;
        $('#demande_remboursement_tickets input:checked').each(function () {
            montant += parseInt($(this).data("montant"));
        });
        $("#demande_remboursement_montant").val("" + Math.floor(montant/100) + "," + (montant%100));
    };

    calculerMontantSel();
    $('#demande_remboursement_tickets input').on('change', calculerMontantSel);
});