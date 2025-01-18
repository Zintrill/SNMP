document.addEventListener('DOMContentLoaded', function () {
    fetchDevices();

    const sortIcons = document.querySelectorAll('.fa-sort');
    const searchInputs = document.querySelectorAll('.search-input');
    const filterSelects = document.querySelectorAll('.filter-select');

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

    function fetchDevices() {
        fetch('getDevices')
            .then(response => response.json())
            .then(devices => renderDeviceList(devices))
            .catch(error => console.error('Error:', error));
    }

    function renderDeviceList(devices) {
        const deviceList = document.getElementById('DeviceList');
        deviceList.innerHTML = '';

        devices.forEach(device => {
            const deviceItem = document.createElement('div');
            deviceItem.classList.add('device-item');

            let statusClass = '';
            if (device.status === 'online') statusClass = 'status-online';
            else if (device.status === 'offline') statusClass = 'status-offline';
            else if (device.status === 'waiting') statusClass = 'status-waiting';

            deviceItem.innerHTML = `
                <span class="device-info" data-column="device_name">${device.device_name}</span>
                <span class="device-info ${statusClass}" data-column="status">${device.status}</span>
                <span class="device-info" data-column="type">${device.type}</span>
                <span class="device-info" data-column="ip_address"><a href="http://${device.address_ip}" target="_blank">${device.address_ip}</a></span>
                <span class="device-info-phone" data-column="mac_address">${device.mac_address}</span>
                <span class="device-info-phone" data-column="uptime">${device.uptime}</span>
                <button class="ellipsisButton"><i class="fa-solid fa-ellipsis"></i></button>
            `;

            deviceList.appendChild(deviceItem);
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
});
