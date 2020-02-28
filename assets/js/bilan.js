import {getRandomColor} from "./service/random.color";

require('chart.js');
require('chart.js/dist/Chart.min.css');

const labels = $('#charts').data('labels');
const depenses = document.getElementById('depenses_chart');
const recettes = document.getElementById('recettes_chart');
console.log(labels);
const colors = labels.map(getRandomColor);
const dataDepenses = {
    datasets: [{
        data: $(depenses).data('datasets-data'),
        backgroundColor: colors
    }],
    labels: labels
};
const dataRecettes = {
    datasets: [{
        data: $(recettes).data('datasets-data'),
        backgroundColor: colors
    }],
    labels: labels
};
new Chart(depenses.getContext('2d'), {
    type: 'doughnut',
    data: dataDepenses,
    options: {
        responsive: true,
        legend: {
            position: 'top',
        },
        title: {
            display: true,
            text: 'Dépenses'
        },
        animation: {
            animateScale: true,
            animateRotate: true
        },
        circumference: Math.PI,
        rotation: -Math.PI
    }
});
new Chart(recettes.getContext('2d'), {
    type: 'doughnut',
    data: dataRecettes,
    options: {
        responsive: true,
        legend: {
            position: 'top',
        },
        title: {
            display: true,
            text: 'Recettes'
        },
        animation: {
            animateScale: true,
            animateRotate: true
        },
        circumference: Math.PI,
        rotation: -Math.PI
    }
});
