document.addEventListener('DOMContentLoaded', function() {
    const userRole = parseInt(document.querySelector('meta[name="user-role"]').content, 10);
    const addDeviceButton = document.getElementById('addDeviceButton');

    if (userRole === 3) { // Operator
        addDeviceButton.style.display = 'none';
    }

    fetchDevices();
    fetchOptions();

    let deviceIdToDelete = null;
    let deviceIdToEdit = null;

    const deleteModal = document.getElementById('deleteModal');
    const closeModal = document.getElementById('closeModal');
    const confirmDeleteButton = document.getElementById('confirmDeleteButton');
    const cancelDeleteButton = document.getElementById('cancelDeleteButton');

    const deviceModal = document.getElementById('deviceModal');
    const closeDeviceModal = document.getElementById('closeDeviceModal');
    const cancelDeviceButton = document.getElementById('cancelDeviceButton');
    const deviceForm = document.getElementById('deviceForm');
    const deviceNameInput = document.getElementById('deviceName');
    const deviceTypeSelect = document.getElementById('deviceType');
    const deviceAddressInput = document.getElementById('deviceAddress');
    const snmpVersionSelect = document.getElementById('snmpVersion');
    const userNameInput = document.getElementById('userName');
    const passwordInput = document.getElementById('password');
    const descriptionInput = document.getElementById('description');
    const submitButton = deviceForm.querySelector('.confirm-button');
    const deviceNameError = document.getElementById('deviceNameError');
    const deviceAddressError = document.getElementById('deviceAddressError');

    let originalDeviceName = '';
    let originalDeviceAddress = '';

    addDeviceButton.addEventListener('click', function() {
        deviceIdToEdit = null;
        deviceForm.reset();
        submitButton.textContent = 'Submit';
        deviceModal.style.display = 'block';
        deviceNameError.textContent = '';
        deviceAddressError.textContent = '';
        checkSNMPVersion();
    });

    closeDeviceModal.addEventListener('click', function() {
        deviceModal.style.display = 'none';
    });

    cancelDeviceButton.addEventListener('click', function() {
        deviceModal.style.display = 'none';
    });

    closeModal.addEventListener('click', function() {
        deleteModal.style.display = 'none';
    });

    cancelDeleteButton.addEventListener('click', function() {
        deleteModal.style.display = 'none';
    });

    window.addEventListener('click', function(event) {
        if (event.target == deviceModal) {
            deviceModal.style.display = 'none';
        }
        if (event.target == deleteModal) {
            deleteModal.style.display = 'none';
        }
    });

    snmpVersionSelect.addEventListener('change', checkSNMPVersion);

    function checkSNMPVersion() {
        const selectedValue = snmpVersionSelect.value;
        if (selectedValue === '4') { // ID 4 to ICMP
            userNameInput.disabled = true;
            passwordInput.disabled = true;
            userNameInput.removeAttribute('required');
            passwordInput.removeAttribute('required');
            userNameInput.value = '';
            passwordInput.value = '';
        } else {
            userNameInput.disabled = false;
            passwordInput.disabled = false;
            userNameInput.setAttribute('required', 'required');
            passwordInput.setAttribute('required', 'required');
        }
    }

    confirmDeleteButton.addEventListener('click', function() {
        if (deviceIdToDelete) {
            fetch('deleteDevice', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `deviceId=${deviceIdToDelete}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    fetchDevices();
                    deleteModal.style.display = 'none';
                } else {
                    console.error('Error:', data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });

    function fetchDevices() {
        fetch('getDevices')
            .then(response => response.json())
            .then(devices => {
                const deviceList = document.getElementById('DeviceList');
                deviceList.innerHTML = '';
                devices.forEach(device => {
                    const deviceItem = document.createElement('div');
                    deviceItem.classList.add('device-item');

                    deviceItem.innerHTML = `
                        <span class="device-info">${device.device_name}</span>
                        <span class="device-info">${device.type}</span>
                        <span class="device-info"><a href="http://${device.address_ip}" target="_blank">${device.address_ip}</a></span>
                        <span class="device-info-phone">${device.snmp_version}</span>
                        <span class="device-info-phone">${device.username}</span>
                        <span class="device-info-phone">
                            <span class="hidden-password" data-password="${device.password}">********</span>${userRole < 3 ? '<i class="eye-icon fa-solid fa-eye-low-vision"></i>' : ''}</span>
                        <span class="device-info-phone">${device.description}</span>
                        ${userRole < 3 ? `<button class="editButton" data-id="${device.id}"><i class="fa-solid fa-pencil"></i></button>` : ''}
                        ${userRole < 3 ? `<button class="deleteButton" data-id="${device.id}"><i class="fa-solid fa-trash"></i></button>` : ''}
                    `;

                    deviceList.appendChild(deviceItem);
                });

                if (userRole < 3) {
                    document.querySelectorAll('.eye-icon').forEach(icon => {
                        icon.addEventListener('mousedown', function() {
                            const hiddenPassword = this.previousElementSibling;
                            hiddenPassword.textContent = hiddenPassword.getAttribute('data-password');
                        });

                        icon.addEventListener('mouseup', function() {
                            const hiddenPassword = this.previousElementSibling;
                            hiddenPassword.textContent = '********';
                        });

                        icon.addEventListener('mouseleave', function() {
                            const hiddenPassword = this.previousElementSibling;
                            hiddenPassword.textContent = '********';
                        });
                    });

                    document.querySelectorAll('.editButton').forEach(button => {
                        button.addEventListener('click', function() {
                            deviceIdToEdit = this.getAttribute('data-id');
                            fetch(`getDeviceById?id=${deviceIdToEdit}`)
                                .then(response => response.json())
                                .then(device => {
                                    originalDeviceName = device.device_name;
                                    originalDeviceAddress = device.address_ip;
                                    deviceNameInput.value = device.device_name;
                                    deviceTypeSelect.value = device.type_id;
                                    deviceAddressInput.value = device.address_ip;
                                    snmpVersionSelect.value = device.snmp_version_id;
                                    userNameInput.value = device.username;
                                    passwordInput.value = device.password;
                                    descriptionInput.value = device.description;
                                    submitButton.textContent = 'Update';
                                    deviceModal.style.display = 'block';
                                    deviceNameError.textContent = '';
                                    deviceAddressError.textContent = '';
                                    checkSNMPVersion(); // Check SNMP version when editing
                                })
                                .catch(error => console.error('Error:', error));
                        });
                    });

                    document.querySelectorAll('.deleteButton').forEach(button => {
                        button.addEventListener('click', function() {
                            deviceIdToDelete = this.getAttribute('data-id');
                            deleteModal.style.display = 'block';
                        });
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred: ' + error.message);
            });
    }

    function fetchOptions() {
        // Fetch options for deviceType
        fetch('getDeviceTypes')
            .then(response => response.json())
            .then(types => {
                const deviceTypeSelect = document.getElementById('deviceType');
                types.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.type_id;
                    option.textContent = type.type;
                    deviceTypeSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error:', error));

        // Fetch options for snmpVersion
        fetch('getSnmpVersions')
            .then(response => response.json())
            .then(versions => {
                const snmpVersionSelect = document.getElementById('snmpVersion');
                versions.forEach(version => {
                    const option = document.createElement('option');
                    option.value = version.snmp_version_id;
                    option.textContent = version.snmp;
                    snmpVersionSelect.appendChild(option); // Adding to the correct dropdown
                });
            })
            .catch(error => console.error('Error:', error));
    }

    function isValidIpAddress(ip) {
        const ipPattern = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
        return ipPattern.test(ip);
    }

    deviceForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const url = deviceIdToEdit ? 'updateDevice' : 'addDevice';
        const formData = new FormData(deviceForm);
    
        if (!isValidIpAddress(deviceAddressInput.value)) {
            deviceAddressError.textContent = 'Invalid IP address format';
            return;
        } else {
            deviceAddressError.textContent = '';
        }
    
        if (deviceIdToEdit) {
            let checkDeviceNamePromise = Promise.resolve();
            let checkAddressIpPromise = Promise.resolve();
    
            if (originalDeviceName.toLowerCase() !== deviceNameInput.value.toLowerCase()) {
                checkDeviceNamePromise = fetch(`checkDeviceName?deviceName=${deviceNameInput.value}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.isTaken) {
                            deviceNameError.textContent = 'Device name is already taken';
                            return Promise.reject('Validation failed');
                        } else {
                            deviceNameError.textContent = '';
                        }
                    });
            }
    
            if (originalDeviceAddress !== deviceAddressInput.value) {
                checkAddressIpPromise = fetch(`checkAddressIp?addressIp=${deviceAddressInput.value}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.isTaken) {
                            deviceAddressError.textContent = 'Address IP is already taken';
                            return Promise.reject('Validation failed');
                        } else {
                            deviceAddressError.textContent = '';
                        }
                    });
            }
    
            Promise.all([checkDeviceNamePromise, checkAddressIpPromise])
                .then(() => {
                    formData.append('deviceId', deviceIdToEdit);
                    return fetch(url, {
                        method: 'POST',
                        body: formData
                    });
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        fetchDevices();
                        deviceModal.style.display = 'none';
                        deviceForm.reset();
                    } else {
                        console.error('Error:', data.message);
                    }
                })
                .catch(error => {
                    if (error !== 'Validation failed') {
                        console.error('Error:', error);
                    }
                });
        } else {
            fetch(`checkDeviceName?deviceName=${deviceNameInput.value}`)
                .then(response => response.json())
                .then(data => {
                    if (data.isTaken) {
                        deviceNameError.textContent = 'Device name is already taken';
                        return Promise.reject('Validation failed');
                    } else {
                        deviceNameError.textContent = '';
                    }
    
                    return fetch(`checkAddressIp?addressIp=${deviceAddressInput.value}`);
                })
                .then(response => response.json())
                .then(data => {
                    if (data.isTaken) {
                        deviceAddressError.textContent = 'Address IP is already taken';
                        return Promise.reject('Validation failed');
                    } else {
                        deviceAddressError.textContent = '';
    
                        return fetch(url, {
                            method: 'POST',
                            body: formData
                        });
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        fetchDevices();
                        deviceModal.style.display = 'none';
                        deviceForm.reset();
                    } else {
                        console.error('Error:', data.message);
                    }
                })
                .catch(error => {
                    if (error !== 'Validation failed') {
                        console.error('Error:', error);
                    }
                });
        }
    });
});
