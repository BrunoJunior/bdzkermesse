$( function() {
    const activitiesBloc = $("#activity-list")
    activitiesBloc.sortable({
        cursor: "grabbing",
        handle: ".activity-card-handle",
        tolerance: "pointer",
        stop: updatePosition
    })
    activitiesBloc.disableSelection()

    /**
     * Update the position of the moved activity
     * @param _
     * @param ui
     */
    function updatePosition(_,ui) {
        const activity = ui.item
        const moveUrl = activity.data('move-url').replace('__position__', getPositionInList(activity))
        $.ajax({url: moveUrl}).done(function(result) {
            console.log({moveUrl, result})
        })
    }

    /**
     * Getting the position of the activity (in the activities bloc)
     * @param activity
     * @returns {number}
     */
    function getPositionInList(activity) {
        return activitiesBloc.children().index(activity) + 1
    }
} )