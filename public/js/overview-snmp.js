document.addEventListener('DOMContentLoaded', function () {
    fetchDevices();

    const sortIcons = document.querySelectorAll('.fa-sort');
    const searchInputs = document.querySelectorAll('.search-input');
    const filterSelects = document.querySelectorAll('.filter-select');
    const pingAllBtn = document.getElementById('ping-all-btn');

    sortIcons.forEach(icon => {
        icon.addEventListener('click', function () {
            const column = this.getAttribute('data-column');
            const direction = this.parentElement.getAttribute('data-sort');
            sortTable(column, direction);
            this.parentElement.setAttribute('data-sort', direction === 'asc' ? 'desc' : 'asc');
        });
    });

    searchInputs.forEach(input => {
        input.addEventListener('input', function () {
            const column = this.getAttribute('data-column');
            const value = this.value.toLowerCase();
            filterTable(column, value);
        });
    });

    filterSelects.forEach(select => {
        select.addEventListener('change', function () {
            const column = this.getAttribute('data-column');
            const value = this.value.toLowerCase();
            filterTable(column, value);
        });
    });

    if (pingAllBtn) {
        pingAllBtn.addEventListener('click', function () {
            pingAllDevices();
        });
    }

    function fetchDevices() {
        fetch('/snmp/getDeviceStatuses')
            .then(response => response.json())
            .then(devices => renderDeviceList(devices))
            .catch(error => console.error('Błąd podczas pobierania urządzeń:', error));
    }

    function renderDeviceList(devices) {
        const deviceList = document.getElementById('DeviceList');
        deviceList.innerHTML = '';

        devices.forEach(device => {
            const deviceItem = document.createElement('div');
            deviceItem.classList.add('device-item');
            deviceItem.id = `device-${device.id}`;

            let statusClass = getStatusClass(device.status);

            deviceItem.innerHTML = `
                <span class="device-info" data-column="device_name">${device.device_name}</span>
                <span class="device-info status ${statusClass}" data-column="status">${device.status}</span>
                <span class="device-info" data-column="type">${device.type}</span>
                <span class="device-info" data-column="ip_address">
                    <a href="http://${device.address_ip}" target="_blank">${device.address_ip}</a>
                </span>
                <span class="device-info-phone" data-column="mac_address">${device.mac_address}</span>
                <span class="device-info-phone" data-column="uptime">${device.uptime}</span>
                <span class="device-actions">
                    <button class="btn btn-sm ping-btn" data-id="${device.id}">
                        <i class="fas fa-network-wired"></i> Pinguj
                    </button>
                </span>
            `;

            deviceList.appendChild(deviceItem);
        });

        document.querySelectorAll('.ping-btn').forEach(button => {
            button.addEventListener('click', function () {
                const deviceId = this.getAttribute('data-id');
                pingDevice(deviceId);
            });
        });
    }

    function sortTable(column, direction) {
        const deviceList = document.getElementById('DeviceList');
        const rows = Array.from(deviceList.querySelectorAll('.device-item'));

        const sortedRows = rows.sort((a, b) => {
            const aValue = a.querySelector(`[data-column="${column}"]`).innerText.toLowerCase();
            const bValue = b.querySelector(`[data-column="${column}"]`).innerText.toLowerCase();

            return direction === 'asc' ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
        });

        deviceList.innerHTML = '';
        sortedRows.forEach(row => deviceList.appendChild(row));
    }

    function filterTable(column, value) {
        const deviceList = document.getElementById('DeviceList');
        const rows = deviceList.querySelectorAll('.device-item');

        rows.forEach(row => {
            const cellValue = row.querySelector(`[data-column="${column}"]`).innerText.toLowerCase();
            row.style.display = cellValue.includes(value) ? '' : 'none';
        });
    }

    function pingDevice(deviceId) {
        const deviceItem = document.getElementById(`device-${deviceId}`);
        if (deviceItem) {
            const statusElement = deviceItem.querySelector(`[data-column="status"]`);
            statusElement.textContent = "Pinging...";
            statusElement.classList.add('status-waiting');
        }
    
        fetch(`/snmp/ping/${deviceId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('Błąd: ' + data.error);
            } else {
                if (deviceItem) {
                    const statusElement = deviceItem.querySelector(`[data-column="status"]`);
                    statusElement.textContent = data.status;
                    statusElement.className = `device-info status ${getStatusClass(data.status)}`;
                }
                fetchDevices(); // 🔄 Odswieżenie statusu w tabeli po zapisaniu do bazy
            }
        })
        .catch(error => {
            console.error('Błąd podczas pingowania:', error);
        });
    }
    

    function pingAllDevices() {
        fetch('/snmp/getDeviceStatuses')
            .then(response => response.json())
            .then(devices => {
                devices.forEach(device => {
                    pingDevice(device.id);
                });
            })
            .catch(error => {
                console.error('Błąd podczas pobierania urządzeń do pingowania:', error);
            });
    }

    function getStatusClass(status) {
        switch (status.toLowerCase()) {
            case 'online': return 'status-online';
            case 'offline': return 'status-offline';
            case 'waiting': return 'status-waiting';
            default: return '';
        }
    }
});
