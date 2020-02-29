import {getRandomColor} from "./service/random.color";

require('chart.js');
require('chart.js/dist/Chart.min.css');

const labels = $('#charts').data('labels');
const colors = labels.map(getRandomColor);

function newChart(idElement, title, donnees, full) {
    const element = document.getElementById(idElement);
    const finalData = donnees || {
        datasets: [{
            data: $(element).data('datasets-data'),
            backgroundColor: colors
        }],
        labels: labels
    };
    new Chart(element.getContext('2d'), {
        type: 'doughnut',
        data: finalData,
        options: {
            responsive: true,
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: title
            },
            animation: {
                animateScale: true,
                animateRotate: true
            },
            circumference: !full ? Math.PI : (2 * Math.PI),
            rotation: !full ? -Math.PI : (-0.5 * Math.PI)
        }
    });
}

newChart('depenses_chart', 'Dépenses');
newChart('recettes_chart', 'Recettes');
