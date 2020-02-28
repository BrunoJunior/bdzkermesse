const $ = require('jquery');
require('metismenu');

/**
 * Initialisation
 */
export function initializeMenu() {
    $("#menu").metisMenu();
    // Afficher / Cacher le menu slim
    $('#show-menu-slim').on('click', function () {
        $('#menu-slim').toggleClass('d-none');
    });
}

