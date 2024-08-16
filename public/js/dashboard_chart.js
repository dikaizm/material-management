let chart;

document.addEventListener('DOMContentLoaded', function () {
    const chartData = window.chartData;
    // console.log(chartData);
    if (!window.chartData) {
        console.error('chartData is not defined');
        return;
    }

    // Ensure selected year and month element exists
    const chartYearElement = document.getElementById('chart_year');
    const chartMonthElement = document.getElementById('chart_month');
    if (!chartYearElement || !chartMonthElement) {
        console.error('Element with id "chart_year" or "chart_month" not found');
        return;
    }

    // Add event listener to year and month element
    chartYearElement.addEventListener('change', async function () {
        const year = this.value;
        const month = chartMonthElement.value;
        await updateChartData(year, month);
    });

    chartMonthElement.addEventListener('change', async function () {
        const year = chartYearElement.value;
        const month = this.value;
        await updateChartData(year, month);
    })

    chart = createChart(chartData);
});


function getDatesInMonth(year, month) {
    const dateNumbers = [];
    const date = new Date(year, month, 1); // Start from the first day of the month

    while (date.getMonth() === month) {
        dateNumbers.push(date.getDate()); // Push the current date number to the array
        date.setDate(date.getDate() + 1); // Move to the next day
    }

    return dateNumbers;
}

function getValuesToArray(labels, obj, key) {
    return labels.map(date => {
        if (date < 10) {
            date = `0${date}`;
        }
        if (chartData.month.length < 2) {
            chartData.month = `0${chartData.month}`;
        }

        const formattedDate = `${chartData.year}-${chartData.month}-${date}`;
        // console.log(formattedDate);

        const value = obj[formattedDate];
        if (value && value[key]) {
            return value[key];
        }
        return 0;
    });
}


function updateChart(chartData) {
    const labels = getDatesInMonth(parseInt(chartData.year), parseInt(chartData.month));

    chart.data.labels = labels;

    chart.data.datasets.forEach((dataset) => {
        if (dataset.label.includes('Stok')) {
            const material = dataset.label.split(' ')[0];
            dataset.data = getValuesToArray(labels, chartData.material_stock, material);
        } else if (dataset.label.includes('Masuk')) {
            const material = dataset.label.split(' ')[0];
            dataset.data = getValuesToArray(labels, chartData.material_ins, material);
        } else if (dataset.label.includes('Keluar')) {
            const material = dataset.label.split(' ')[0];
            dataset.data = getValuesToArray(labels, chartData.material_outs, material);
        }
    });

    chart.update();
}


async function updateChartData(year, month) {
    const response = await fetch(`/api/chart-data?year=${year}&month=${month}`, {
        method: 'GET',
    });

    if (!response.ok) {
        console.error('Failed to fetch data');
        return;
    }

    const data = await response.json();

    chartData.year = year;
    chartData.month = month;
    chartData.material_ins = data.material_ins;
    chartData.material_outs = data.material_outs;
    chartData.material_stock = data.material_stock;

    updateChart(chartData);
}


function createChart(chartData) {
    // Ensure that the element with id 'chart_material' exists
    const chartElement = document.getElementById('chart_material');
    if (!chartElement) {
        console.error('Element with id "chart_material" not found');
        return;
    }

    const getOrCreateLegendList = (chart, id) => {
        const legendContainer = document.getElementById(id);
        let listContainer = legendContainer.querySelector('ul');

        if (!listContainer) {
            listContainer = document.createElement('ul');
            listContainer.style.display = 'flex';
            listContainer.style.flexWrap = 'wrap';
            listContainer.style.justifyContent = 'center';
            listContainer.style.columnGap = '0.8rem';
            listContainer.style.rowGap = '0.5rem';
            listContainer.style.margin = 0;
            listContainer.style.padding = 0;
            legendContainer.appendChild(listContainer);
        }

        return listContainer;
    };

    const htmlLegendPlugin = {
        id: 'htmlLegend',
        afterUpdate(chart, args, options) {
            const ul = getOrCreateLegendList(chart, options.containerID);

            // Remove old legend items
            while (ul.firstChild) {
                ul.firstChild.remove();
            }

            const items = chart.options.plugins.legend.labels.generateLabels(chart);

            // Collect all items containing 'Stok'
            const stokItems = items.filter(item => item.text.includes('Stok'));

            // Remove 'Stok' items from the original array
            stokItems.forEach(stokItem => {
                const index = items.indexOf(stokItem);
                if (index > -1) {
                    items.splice(index, 1);
                }
            });

            // Add all 'Stok' items to the beginning of the array
            items.unshift(...stokItems);

            items.forEach(item => {
                const li = document.createElement('li');
                li.style.alignItems = 'center';
                li.style.cursor = 'pointer';
                li.style.display = 'flex';
                li.style.flexDirection = 'row';
                li.style.marginLeft = '10px';

                const { type } = chart.config;
                li.onclick = () => {
                    if (type === 'pie' || type === 'doughnut') {
                        // Pie and doughnut charts only have a single dataset and visibility is per item
                        chart.toggleDataVisibility(item.index);
                    } else {
                        chart.setDatasetVisibility(item.datasetIndex, !chart.isDatasetVisible(item.datasetIndex));
                    }
                    chart.update();
                };

                // Color box
                const boxDiv = document.createElement('div');

                boxDiv.style.background = item.fillStyle;
                boxDiv.style.borderColor = item.strokeStyle;
                boxDiv.style.borderWidth = item.lineWidth + 'px';
                boxDiv.style.display = 'inline-block';
                boxDiv.style.flexShrink = 0;

                const isKeluar = item.text.includes('Keluar');
                const isMasuk = item.text.includes('Masuk');

                if (isKeluar || isMasuk) {
                    if (isKeluar) {
                        boxDiv.style.background = 'transparent';
                        boxDiv.style.borderTopWidth = item.lineWidth + 'px';
                        boxDiv.style.borderTopStyle = 'dotted';
                        boxDiv.style.borderTopColor = item.strokeStyle;
                    }
                    boxDiv.style.height = '4px';
                } else {
                    boxDiv.style.height = '20px';
                }

                boxDiv.style.marginRight = '10px';
                boxDiv.style.width = '40px';

                // Text
                const textContainer = document.createElement('p');
                textContainer.style.fontSize = '12px';
                textContainer.style.color = item.fontColor;
                textContainer.style.margin = 0;
                textContainer.style.padding = 0;
                textContainer.style.whiteSpace = 'nowrap';
                textContainer.style.textDecoration = item.hidden ? 'line-through' : '';

                const text = document.createTextNode(item.text);
                textContainer.appendChild(text);

                li.appendChild(boxDiv);
                li.appendChild(textContainer);
                ul.appendChild(li);
            });
        }
    };

    const horizontalLinePlugin = {
        id: 'horizontalLine',
        afterDraw: (chart, args, options) => {
            const { ctx, scales, chartArea: { top, bottom } } = chart;
            const { y } = scales;
            const { max_stock } = chartData;

            const yValue = y.getPixelForValue(max_stock);
            ctx.save();
            ctx.beginPath();
            ctx.strokeStyle = 'red';
            ctx.setLineDash([10, 6]);
            ctx.moveTo(0, yValue);
            ctx.lineTo(chart.width, yValue);
            ctx.stroke();
            ctx.restore();

            // Add text
            ctx.save();
            ctx.fillStyle = 'red';
            ctx.textAlign = 'center';
            ctx.font = '12px Arial';
            ctx.fillText('Maksimum Stok', chart.width / 2, yValue - 10);
            ctx.restore();
        }
    }

    // Create chartjs
    const ctx = chartElement.getContext('2d');

    const labels = getDatesInMonth(parseInt(chartData.year), parseInt(chartData.month));

    // console.log(getValuesToArray(chartData.material_ins, 'HDPE'));

    const barChartData = [
        // Bar Charts
        {
            label: 'HDPE Stok',
            data: getValuesToArray(labels, chartData.material_stock, 'HDPE'),
            backgroundColor: 'rgba(236, 132, 15, 0.8)',
            order: 10
        },
        {
            label: 'LDPE Stok',
            data: getValuesToArray(labels, chartData.material_stock, 'LDPE'),
            backgroundColor: 'rgba(25, 148, 239, 0.8)',
            order: 11
        },
        {
            label: 'LLDPE Stok',
            data: getValuesToArray(labels, chartData.material_stock, 'LLDPE'),
            backgroundColor: 'rgba(25, 200, 82, 0.8)',
            order: 12
        },
    ]

    const lineChartData = [
        {
            label: 'HDPE Masuk',
            data: getValuesToArray(labels, chartData.material_ins, 'HDPE'),
            borderColor: 'rgba(226, 90, 13, 0.8)',
            backgroundColor: 'rgba(226, 90, 13, 0.8)',
            type: 'line',
            order: 4
        },
        {
            label: 'HDPE Keluar',
            data: getValuesToArray(labels, chartData.material_outs, 'HDPE'),
            borderColor: 'rgba(226, 90, 13, 0.8)',
            backgroundColor: 'rgba(226, 90, 13, 0.8)',
            type: 'line',
            borderDash: [0, 6],
            borderCapStyle: 'round',
            order: 5
        },
        {
            label: 'LDPE Masuk',
            data: getValuesToArray(labels, chartData.material_ins, 'LDPE'),
            borderColor: 'rgba(13, 98, 221, 0.8)',
            backgroundColor: 'rgba(13, 98, 221, 0.8)',
            type: 'line',
            order: 6
        },
        {
            label: 'LDPE Keluar',
            data: getValuesToArray(labels, chartData.material_outs, 'LDPE'),
            borderColor: 'rgba(13, 98, 221, 0.8)',
            backgroundColor: 'rgba(13, 98, 221, 0.8)',
            type: 'line',
            borderDash: [0, 6],
            borderCapStyle: 'round',
            order: 7
        },
        {
            label: 'LLDPE Masuk',
            data: getValuesToArray(labels, chartData.material_ins, 'LLDPE'),
            borderColor: 'rgba(22, 176, 100, 0.8)',
            backgroundColor: 'rgba(22, 176, 100, 0.8)',
            type: 'line',
            order: 8
        },
        {
            label: 'LLDPE Keluar',
            data: getValuesToArray(labels, chartData.material_outs, 'LLDPE'),
            borderColor: 'rgba(22, 176, 100, 0.8)',
            backgroundColor: 'rgba(22, 176, 100, 0.8)',
            type: 'line',
            borderDash: [0, 6],
            borderCapStyle: 'round',
            order: 9
        },
    ]

    const datasets = barChartData.concat(lineChartData)

    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: datasets
        },
        plugins: [
            htmlLegendPlugin,
            horizontalLinePlugin
        ],
        options: {
            responsive: true,
            plugins: {
                htmlLegend: {
                    containerID: 'legend_chart_material'
                },
                legend: {
                    position: 'bottom',
                    display: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: function () {
                        const max = Math.max(...datasets.map(dataset => Math.max(...dataset.data)));
                        if (max < chartData.max_stock) {
                            const limit = Math.ceil(chartData.max_stock / 5) * 5;
                            if (limit == chartData.max_stock) {
                                return limit + 5;
                            }
                            return limit;
                        }
                    },
                }
            }
        },
    });
}
