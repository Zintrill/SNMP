Chart.defaults.color = '#FFF';
document.addEventListener('DOMContentLoaded', function () {
    const options1 = {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: {
                        size: 15,
                    },
                    usePointStyle: true,
                    padding: 20
                }
            },
            title: {
                display: true,
                text: 'Switches',
                font: {
                    size: 20,
                    weight: 'bold'
                },
                padding: 20
            }
        }
    };

    const options2 = {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: {
                        size: 15,
                    },
                    usePointStyle: true,
                    padding: 20
                }
            },
            title: {
                display: true,
                text: 'All Devices',
                font: {
                    size: 20,
                    weight: 'bold'
                },
                padding: 20
            },
        },
    };

    const initialData = {
        labels: ['Online', 'Offline', 'Waiting'],
        datasets: [{
            data: [0, 0, 0],
            backgroundColor: [
                '#7FC008', // Zielony
                '#DB303F', // Czerwony
                '#F68D2B' // Pomarańczowy
            ],
            borderWidth: 0,
            hoverOffset: 4
        }]
    };

    const donutChart1 = new Chart(document.getElementById('donutChart1'), {
        type: 'doughnut',
        data: initialData,
        options: options1
    });

    const donutChart2 = new Chart(document.getElementById('donutChart2'), {
        type: 'doughnut',
        data: initialData,
        options: options2
    });

    fetch('getDeviceStatusesByType')
        .then(response => response.json())
        .then(data => {
            if (!Array.isArray(data)) {
                throw new Error('Data is not an array');
            }

            const statusCountsSwitches = {
                'Online': 0,
                'Offline': 0,
                'Waiting': 0
            };

            const statusCountsAll = {
                'Online': 0,
                'Offline': 0,
                'Waiting': 0
            };

            data.forEach(status => {
                if (status.type === 'Switch' && statusCountsSwitches.hasOwnProperty(status.status)) {
                    statusCountsSwitches[status.status] += parseInt(status.count);
                }
                if (statusCountsAll.hasOwnProperty(status.status)) {
                    statusCountsAll[status.status] += parseInt(status.count);
                }
            });

            donutChart1.data.datasets[0].data = [
                statusCountsSwitches['Online'],
                statusCountsSwitches['Offline'],
                statusCountsSwitches['Waiting']
            ];
            donutChart1.update();

            donutChart2.data.datasets[0].data = [
                statusCountsAll['Online'],
                statusCountsAll['Offline'],
                statusCountsAll['Waiting']
            ];
            donutChart2.update();
        })
        .catch(error => console.error('Error:', error));
});
