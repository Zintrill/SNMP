Chart.defaults.color = '#FFF';

document.addEventListener('DOMContentLoaded', function () {
    const chartOptions = (title) => ({
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: { size: 15 },
                    usePointStyle: true,
                    padding: 20
                }
            },
            title: {
                display: true,
                text: title,
                font: { size: 20, weight: 'bold' },
                padding: 20
            }
        }
    });

    const initialData = {
        labels: ['Online', 'Offline', 'Waiting'],
        datasets: [{
            data: [0, 0, 0],
            backgroundColor: ['#7FC008', '#DB303F', '#F68D2B'],
            borderWidth: 0,
            hoverOffset: 4
        }]
    };

    const donutChartAllDevices = new Chart(document.getElementById('donutChart1'), {
        type: 'doughnut',
        data: structuredClone(initialData),
        options: chartOptions('All Devices')
    });

    const donutChartSwitches = new Chart(document.getElementById('donutChart2'), {
        type: 'doughnut',
        data: structuredClone(initialData),
        options: chartOptions('Switches')
    });

    function updateCharts() {
        fetch('/dashboard/getDeviceStatistics')
            .then(response => response.json())
            .then(data => {
                if (!data || !data.allDevices || !data.switches) {
                    throw new Error('Invalid data structure');
                }

                donutChartAllDevices.data.datasets[0].data = [
                    data.allDevices.Online,
                    data.allDevices.Offline,
                    data.allDevices.Waiting
                ];
                donutChartAllDevices.update();

                donutChartSwitches.data.datasets[0].data = [
                    data.switches.Online,
                    data.switches.Offline,
                    data.switches.Waiting
                ];
                donutChartSwitches.update();
            })
            .catch(error => console.error('Error updating charts:', error));
    }

    updateCharts();
    setInterval(updateCharts, 5000);
});
