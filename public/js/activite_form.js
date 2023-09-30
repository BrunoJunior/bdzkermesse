$(function() {
    const form = $('form')
    $('#activite_creneaux fieldset').each(function (){
        addRemoveLinkCollectionWidget($(this))
    })
    changeVisibilityBlocs()
    form.on('collection-widget-added', changeVisibilityBlocs)
    form.on('collection-widget-removed', affichageAucunCreneau)
    form.on('change', '#activite_onlyForPlanning', changeVisibilityNbBenevoles)
    form.on("change", "#activite_type", affichageNewType)
    form.on("change", "#activite_accepteTickets", changeVisibilityNbTickets)

    function changeVisibilityBlocs() {
        affichageAucunCreneau()
        changeVisibilityNbBenevoles()
        affichageNewType()
        changeVisibilityNbTickets()
    }

    function affichageNewType() {
        const selectedType = parseInt($("#activite_type").val())
        $("#activite_new_type_activite").closest(".form-group").toggle(selectedType === -1)
    }

    function affichageAucunCreneau() {
        const nbFieldest = $('#activite_creneaux fieldset').length
        const noCreneau = $('#no_creneau')
        if (nbFieldest === 0) {
            $('#activite_creneaux').append('<fieldset class="form-group" id="no_creneau">Aucun créneau horaire</fieldset>')
        } else if (noCreneau.length > 0) {
            noCreneau.remove()
        }
    }

    function changeVisibilityNbBenevoles() {
        const onlyForPlanning = $("#activite_onlyForPlanning").is(':checked')
        if (onlyForPlanning) {
            form.find('.creneau-nb-benevoles-requis input').val('0')
        }
        form.find('.creneau-nb-benevoles-requis').toggle(!onlyForPlanning)
        form.find('#activite_accepteTickets').closest('.form-group').toggle(!onlyForPlanning)
        changeVisibilityNbTickets()
    }

    function changeVisibilityNbTickets() {
        const acceptTickets = $("#activite_accepteTickets").is(':checked')
        const onlyForPlanning = $("#activite_onlyForPlanning").is(':checked')
        const toDisplay = acceptTickets && !onlyForPlanning
        const nbTicketsInput = form.find('#activite_nbTickets')
        if (!toDisplay) {
            nbTicketsInput.val('0')
        }
        nbTicketsInput.closest('.form-group').toggle(toDisplay);
    }
});
