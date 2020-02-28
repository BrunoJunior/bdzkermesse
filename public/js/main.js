function addRemoveLinkCollectionWidget(element) {
    const removeFormButton = $('<button type="button" class="btn btn-danger collection-widget-remove"><i class="fas fa-times"></i></button>');
    element.append(removeFormButton);
    removeFormButton.on('click', function() {
        const parent = element.parent();
        element.remove();
        parent.trigger('collection-widget-removed');
    });
}
