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

    // Créneaux complets non droppable / non draggable
    $( ".droppable.creneau-complet" ).droppable( "option", "disabled", true );

    const droppables = $('.droppable');
    const draggables = $('.draggable');

    $('.drag-grip').on('click', function( evt ) {
        evt.preventDefault();
        evt.stopPropagation();
        return false;
    });

    // Sur toutes les zones droppables ont vient mettre/enlever une classe pour définir su on est en train de déplacer une zone draggable
    draggables.on( "dragstart", function() {droppables.addClass("dragging");});
    draggables.on( "dragstop", function() {droppables.removeClass("dragging");});

    // Quand on rentre dans une zone droppable, on filtre les bénévoles de l'origine déjà présent dans la destination
    droppables.on("dropover", function ( event, ui ) {
        const from = $(ui.draggable);
        const to = $(this);
        const alreadyInDest = to.find('.identite-benevole')
            .map(function (index, element) {return $(element).data('benevole-id');})
            .toArray();
        const transferableBenevoles = from.find('.identite-benevole').map(function ( index, element ) {
            const $elem = $(element);
            return {id: $elem.data('benevole-id'), nom: $elem.text()};
        }).toArray().filter(function (benevole) {return alreadyInDest.indexOf(benevole.id) === -1;});
        to.data('transferables', transferableBenevoles);
        if (transferableBenevoles.length === 0) {
            to.removeClass('ui-droppable-active');
        }
    });

    // On a dropé un élément valide (au moins 1 bénévole non déjà présent)
    droppables.on( "drop", function( event, ui ) {
        const to = $(this);
        const transferables = to.data('transferables') || [];
        if (transferables.length === 0) {
            return;
        }
        const from = $(ui.draggable);
        $.ajax({
            url: from.data('migration-url').replace('__to__', to.data('creneau')),
            context: this
        }).done(function(html) {
            const modale = $('#modal-migration');
            modale.html(html);
            modale.modal('show');
        });
    } );
});
