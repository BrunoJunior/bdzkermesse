function addRemoveLinkCollectionWidget(element) {
    var removeFormButton = $('<button type="button" class="btn btn-danger collection-widget-remove"><i class="fas fa-times"></i></button>');
    element.append(removeFormButton);
    removeFormButton.on('click', function(e) {
        var parent = element.parent();
        element.remove();
        parent.trigger('collection-widget-removed');
    });
};

$(function() {
    $("#menu").metisMenu();

    // add-collection-widget.js
    $('.add-another-collection-widget').click(function (e) {
        var list = $($(this).attr('data-list'));
        // Try to find the counter of the list
        var counter = list.data('widget-counter') | list.children().length;
        // If the counter does not exist, use the length of the list
        if (!counter) { counter = list.children().length; }

        // grab the prototype template
        var newWidget = list.attr('data-prototype');
        // replace the "__name__" used in the id and name of the prototype
        // with a number that's unique to your emails
        // end name attribute looks like name="contact[emails][2]"
        newWidget = newWidget.replace(/__name__/g, counter);
        // Increase the counter
        counter++;
        // And store it, the length cannot be used if deleting widgets is allowed
        list.data('widget-counter', counter);

        // create a new list element and add it to the list
        var element = $(newWidget);
        list.append(element);

        list.trigger('collection-widget-added', [element]);

        addRemoveLinkCollectionWidget(element);
    });

    // Date picker
    $('.js-datepicker').datepicker({
        language: 'fr',
        format: 'yyyy-mm-dd'
    });

    // Tooltip
    $('[data-toggle="tooltip"]').tooltip();

    // Ajax simple
    $('.ajax').each(function () {
        var element = $(this);
        var url = element.data('ajax-url');
        if (url === undefined) {
            return;
        }
        $.ajax({
            url: url,
            context: element[0]
        }).done(function(html) {
            $(this).html(html);
        })
    });

    // Afficher / Cacher le menu slim
    $('#show-menu-slim').on('click', function () {
        $('#menu-slim').toggleClass('d-none');
    });

});