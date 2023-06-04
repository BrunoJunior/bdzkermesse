$(function() {
    const form = $('form')
    $('#activite_creneaux fieldset').each(function (){
        addRemoveLinkCollectionWidget($(this))
    })
    changeVisibilityBlocs()
    form.on('collection-widget-added', changeVisibilityBlocs)
    form.on('collection-widget-removed', affichageAucunCreneau)
    form.on('change', '#activite_onlyForPlanning', changeVisibilityNbBenevoles)

    function changeVisibilityBlocs() {
        affichageAucunCreneau()
        changeVisibilityNbBenevoles()
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
    }
});
