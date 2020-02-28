const $ = require('jquery');

function getSearchValue(table, index) {
    let value = '';
    const th = table.find('thead tr th').get(index);
    const input = $(th).find('input');
    if (input.length === 1) {
        value = input.val().toLowerCase();
    }
    return value;
}

function searchInTable(table) {
    const lignes = table.find('tbody tr');
    lignes.each(function () {
        const ligne = $(this);
        const colonnes = ligne.find('td');
        let display = true;
        colonnes.each(function () {
            const colonne = $(this);
            const index = colonnes.index(colonne);
            const search = getSearchValue(table, index + 1);
            const valeur = colonne.text().toLowerCase();
            if (search !== '' && valeur.indexOf(search) === -1) {
                display = false;
            }
        });
        ligne.toggle(display);
    });
}

export function initSearch() {
    // Recherche dans tableau
    $('table thead .btn-filter').on('click', function () {
        searchInTable($(this).closest('table'));
    });

    $('table thead input').on('keyup', function (e) {
        const code = e.which;
        if(parseInt(code) === 13 || parseInt(code) === 9) {
            searchInTable($(this).closest('table'));
        }
    });
}
