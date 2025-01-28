document.addEventListener('DOMContentLoaded', function () {
    let LineData = {
        labels: [],
        datasets: [
            { label: 'Online', borderColor: '#7FC008', backgroundColor: '#7FC008', data: [] },
            { label: 'Offline', borderColor: '#DB303F', backgroundColor: '#DB303F', data: [] },
            { label: 'Waiting', borderColor: '#F68D2B', backgroundColor: '#F68D2B', data: [] }
        ]
    };

    const canvasWidth = '100%';
    const canvasHeight = '300';

    let canvas = document.getElementById('myChart');
    canvas.width = canvasWidth;
    canvas.height = canvasHeight;

    let ctx = document.getElementById('myChart').getContext('2d');
    let myChart = new Chart(ctx, {
        type: 'line',
        data: LineData,
        options: {
            animation: { duration: 0 },
            scales: {
                x: { ticks: { color: 'black', font: { size: 15, weight: 'bold' } } },
                y: { ticks: { color: 'black', font: { size: 15, weight: 'bold' } } }
            },
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 15 }, usePointStyle: true } }
            }
        }
    });

    function addData(time, counts) {
        myChart.data.labels.push(time);
        myChart.data.datasets[0].data.push(counts.Online);
        myChart.data.datasets[1].data.push(counts.Offline);
        myChart.data.datasets[2].data.push(counts.Waiting);

        if (myChart.data.labels.length > 10) {
            myChart.data.labels.shift();
            myChart.data.datasets.forEach(dataset => dataset.data.shift());
        }

        myChart.update();
    }

    function fetchRealTimeData() {
        fetch('/dashboard/getDeviceStatistics')
            .then(response => response.json())
            .then(data => {
                if (!data || !data.allDevices) throw new Error('Invalid data structure');

                const currentTime = new Date().toLocaleTimeString();
                addData(currentTime, data.allDevices);
            })
            .catch(error => console.error('Error fetching chart data:', error));
    }

    function generateRealTimeData() {
        fetchRealTimeData();
        setTimeout(generateRealTimeData, 5000);
    }

    generateRealTimeData();
});
