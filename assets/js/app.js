import '../scss/app.scss';
import {initSearch} from "./service/search";
import {initializeMenu} from "./service/menu";
import {initAjax, initSimpleAjax} from "./service/ajax";
import {initCollectionWidget} from "./service/collection.widget";
import {initModal} from "./service/modal";

// Global jquery
const $ = require('jquery');
global.$ = global.jQuery = $;

require('webpack-jquery-ui');
require('@fortawesome/fontawesome-free/css/all.min.css');
require('popper.js');
require('bootstrap');
require('bootstrap-datepicker');
require('bootstrap-datepicker/dist/css/bootstrap-datepicker3.standalone.min.css');
require('bootstrap-datepicker/js/locales/bootstrap-datepicker.fr');

const mainInit = function () {
    // Date picker
    $('.js-datepicker').datepicker({
        language: 'fr',
        format: 'dd/mm/yyyy'
    });
    $('.draggable').draggable({
        revert: true,
        handle: '.drag-grip',
        helper: 'clone'
    });
    $('.droppable').droppable({
        accept : '.draggable',
        tolerance: "pointer"
    });
    // Tooltip
    $('[data-toggle="tooltip"]').tooltip();
    initAjax(mainInit);
};

$(function() {
    initializeMenu();
    initSearch();
    mainInit();
    initCollectionWidget();
    initModal(mainInit);
    initSimpleAjax(mainInit);
});
