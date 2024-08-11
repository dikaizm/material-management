document.addEventListener('DOMContentLoaded', function () {
    const chartData = window.chartData;
    if (!window.chartData) {
        console.error('chartData is not defined');
        return;
    }

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
                const boxSpan = document.createElement('span');
                boxSpan.style.background = item.fillStyle;
                boxSpan.style.borderColor = item.strokeStyle;
                boxSpan.style.borderWidth = item.lineWidth + 'px';
                boxSpan.style.display = 'inline-block';
                boxSpan.style.flexShrink = 0;

                const isKeluar = item.text.includes('Keluar');
                const isMasuk = item.text.includes('Masuk');
                boxSpan.style.height = isKeluar ? '8px' : isMasuk ? '4px' : '20px';

                boxSpan.style.marginRight = '10px';
                boxSpan.style.width = '40px';

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

                li.appendChild(boxSpan);
                li.appendChild(textContainer);
                ul.appendChild(li);
            });
        }
    };

    // Create chartjs
    const ctx = chartElement.getContext('2d');

    const labels = chartData.dates.reverse();

    function getValuesToArray(obj, key) {
        return Object.values(obj).map(item => item[key]).reverse() || [];
    }

    const barChartData = [
        // Bar Charts
        {
            label: 'HDPE Stok',
            data: getValuesToArray(chartData.material_stock, 'HDPE'),
            backgroundColor: 'rgba(236, 132, 15, 0.8)',
            order: 10
        },
        {
            label: 'LDPE Stok',
            data: getValuesToArray(chartData.material_stock, 'LDPE'),
            backgroundColor: 'rgba(25, 148, 239, 0.8)',
            order: 11
        },
        {
            label: 'LLDPE Stok',
            data: getValuesToArray(chartData.material_stock, 'LLDPE'),
            backgroundColor: 'rgba(25, 200, 82, 0.8)',
            order: 12
        },
    ]

    const lineChartData = [
        {
            label: 'HDPE Masuk',
            data: getValuesToArray(chartData.material_in, 'HDPE'),
            borderColor: 'rgba(226, 90, 13, 0.8)',
            backgroundColor: 'rgba(226, 90, 13, 0.8)',
            type: 'line',
            order: 4
        },
        {
            label: 'HDPE Keluar',
            data: getValuesToArray(chartData.material_out, 'HDPE'),
            borderColor: 'rgba(226, 90, 13, 0.8)',
            backgroundColor: 'rgba(226, 90, 13, 0.8)',
            type: 'line',
            borderWidth: 6,
            order: 5
        },
        {
            label: 'LDPE Masuk',
            data: getValuesToArray(chartData.material_in, 'LDPE'),
            borderColor: 'rgba(13, 98, 221, 0.8)',
            backgroundColor: 'rgba(13, 98, 221, 0.8)',
            type: 'line',
            order: 6
        },
        {
            label: 'LDPE Keluar',
            data: getValuesToArray(chartData.material_out, 'LDPE'),
            borderColor: 'rgba(13, 98, 221, 0.8)',
            backgroundColor: 'rgba(13, 98, 221, 0.8)',
            type: 'line',
            borderWidth: 6,
            order: 7
        },
        {
            label: 'LLDPE Masuk',
            data: getValuesToArray(chartData.material_in, 'LLDPE'),
            borderColor: 'rgba(22, 176, 100, 0.8)',
            backgroundColor: 'rgba(22, 176, 100, 0.8)',
            type: 'line',
            order: 8
        },
        {
            label: 'LLDPE Keluar',
            data: getValuesToArray(chartData.material_out, 'LLDPE'),
            borderColor: 'rgba(22, 176, 100, 0.8)',
            backgroundColor: 'rgba(22, 176, 100, 0.8)',
            type: 'line',
            borderWidth: 6,
            order: 9
        },
    ]

    const datasets = barChartData.concat(lineChartData)

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: datasets
        },
        plugins: [htmlLegendPlugin],
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
                    border: {
                        dash: function (context) {
                            if (context.tick.value === 20) {
                                return [6, 6];
                            }
                        }
                    },
                    grid: {
                        color: function (context) {
                            if (context.tick.value === 20) {
                                return 'red';
                            }
                            return 'rgba(0, 0, 0, 0.1)';
                        }
                    }
                }
            }
        },
    });
});
