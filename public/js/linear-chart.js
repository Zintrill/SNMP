document.addEventListener('DOMContentLoaded', function () {
    let LineData = {
        labels: [],
        datasets: [
            {
                label: 'Online',
                borderColor: '#7FC008',
                backgroundColor: '#7FC008',
                data: [],
            },
            {
                label: 'Offline',
                borderColor: '#DB303F',
                backgroundColor: '#DB303F',
                data: [],
            },
            {
                label: 'Waiting',
                backgroundColor: '#F68D2B',
                borderColor: '#F68D2B',
                data: [],
            }
        ]
    };

    const canvasWidth = '100%';
    const canvasHeight = '300';

    let canvas = document.getElementById('myChart');
    canvas.width = canvasWidth;
    canvas.height = canvasHeight;

    let ctx = canvas.getContext('2d');
    let myChart = new Chart(ctx, {
        type: 'line',
        data: LineData,
        options: {
            animation: {
                duration: 0,
            },
            scales: {
                x: {
                    ticks: {
                        color: 'black',
                        font: {
                            size: "15px",
                            weight: 'bold'
                        }
                    }
                },
                y: {
                    ticks: {
                        color: 'black',
                        font: {
                            size: "15px",
                            weight: 'bold'
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 15,
                        },
                        usePointStyle: true,
                    }
                }
            }
        }
    });

    function addData(label, time, counts) {
        myChart.data.labels.push(time);
        myChart.data.datasets[0].data.push(counts.Online);
        myChart.data.datasets[1].data.push(counts.Offline);
        myChart.data.datasets[2].data.push(counts.Waiting);

        if (myChart.data.labels.length > 10) {
            myChart.data.labels.shift();
            myChart.data.datasets.forEach((dataset) => {
                dataset.data.shift();
            });
        }

        myChart.update();
    }

    function fetchRealTimeData() {
        fetch('getDeviceStatuses')
            .then(response => response.json())
            .then(data => {
                const statusCounts = {
                    'Online': 0,
                    'Offline': 0,
                    'Waiting': 0
                };

                data.forEach(status => {
                    if (statusCounts.hasOwnProperty(status.status)) {
                        statusCounts[status.status] = parseInt(status.count);
                    }
                });

                const currentTime = new Date().toLocaleTimeString();
                addData('Status', currentTime, statusCounts);
            })
            .catch(error => console.error('Error:', error));
    }

    function generateRealTimeData() {
        fetchRealTimeData();
        setTimeout(generateRealTimeData, 5000);
    }

    generateRealTimeData();
});
