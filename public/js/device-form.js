// public/js/device-form.js

console.log('device-form.js loaded'); // Debugging

document.addEventListener('DOMContentLoaded', function() {
    // Definiowanie zmiennych na podstawie ról użytkownika
    const userRolesMeta = document.querySelector('meta[name="user-role"]');
    const userRoles = userRolesMeta ? userRolesMeta.content.split(',') : [];
    const isAdmin = userRoles.includes('ROLE_ADMIN');
    const isOperator = userRoles.includes('ROLE_OPERATOR');

    console.log('User Roles:', userRoles); // Debugging

    // Inicjalizacja elementów formularza i modali
    const addDeviceButton = document.getElementById('addDeviceButton');
    const deviceModal = document.getElementById('deviceModal');
    const closeDeviceModal = document.getElementById('closeDeviceModal');
    const cancelDeviceButton = document.getElementById('cancelDeviceButton');

    const deviceForm = document.getElementById('deviceForm');
    let isSubmitting = false; // Flaga zapobiegająca podwójnym wywołaniom

    if (deviceForm && !deviceForm.dataset.listenerAttached) {
        deviceForm.addEventListener('submit', handleFormSubmit);
        deviceForm.dataset.listenerAttached = 'true';
    }

    // Elementy formularza
    const deviceNameInput = document.getElementById('deviceName');
    const deviceTypeSelect = document.getElementById('deviceType');
    const deviceAddressInput = document.getElementById('deviceAddress');
    const snmpVersionSelect = document.getElementById('snmpVersion');
    const userNameInput = document.getElementById('userName');
    const passwordInput = document.getElementById('password');
    const descriptionInput = document.getElementById('description');
    const submitButton = deviceForm.querySelector('.confirm-button');

    // Elementy do wyświetlania błędów
    const deviceNameError = document.getElementById('deviceNameError');
    const deviceTypeError = document.getElementById('deviceTypeError');
    const deviceAddressError = document.getElementById('deviceAddressError');
    const snmpVersionError = document.getElementById('snmpVersionError');
    const userNameError = document.getElementById('userNameError');
    const passwordError = document.getElementById('passwordError');
    const descriptionError = document.getElementById('descriptionError');

    let deviceIdToDelete = null;
    let deviceIdToEdit = null;

    // Funkcje do zarządzania błędami
    function clearErrors() {
        const errorElements = document.querySelectorAll('.error-message');
        errorElements.forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });

        const formGroups = document.querySelectorAll('.form-group');
        formGroups.forEach(group => {
            group.classList.remove('error');
        });
    }

    function displayErrors(errors) {
        for (const [field, message] of Object.entries(errors)) {
            const errorElement = document.getElementById(`${field}Error`);
            if (errorElement) {
                errorElement.textContent = message;
                errorElement.style.display = 'block';

                // Dodaj klasę 'error' do grupy formularza dla stylizacji
                const formGroup = errorElement.parentElement;
                if (formGroup) {
                    formGroup.classList.add('error');
                }
            }
        }
    }

    // Obsługa przesyłania formularza dodawania/edycji urządzenia
    function handleFormSubmit(event) {
        event.preventDefault(); // Zapobiega domyślnemu wysłaniu formularza

        if (isSubmitting) {
            console.warn('Formularz jest już w trakcie wysyłania.');
            return;
        }

        isSubmitting = true; // Ustaw flagę na true, aby zablokować kolejne wysyłania

        clearErrors(); // Czyszczenie poprzednich komunikatów o błędach

        const formData = new FormData(deviceForm);
        const selectedSNMPVersionId = snmpVersionSelect.value;

        if (selectedSNMPVersionId === '4') { // ICMP
            formData.set('userName', '');
            formData.set('password', '');
        }

        const deviceId = formData.get('deviceId') || null;

        console.log('Wysyłanie danych formularza:', Object.fromEntries(formData.entries()));
        console.log('URL:', deviceId ? '/configuration/updateDevice' : '/configuration/addDevice');

        const url = deviceId ? '/configuration/updateDevice' : '/configuration/addDevice';

        fetch(url, {
            method: 'POST',
            body: formData, // Wysyłanie jako FormData
        })
        .then(response => {
            console.log('Fetch response status:', response.status); // Debugging
            return response.json();
        })
        .then(result => {
            console.log('Response received:', result); // Debugging
            isSubmitting = false; // Reset flagi po zakończeniu żądania
            if (result.status === 'success') {
                alert(result.message);
                fetchDevices();
                deviceForm.reset();
                deviceModal.style.display = 'none';
            } else if (result.status === 'error' && result.errors) {
                // Wyświetlanie błędów w modalnym oknie
                displayErrors(result.errors);
            } else {
                alert('Błąd: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            isSubmitting = false; // Reset flagi w przypadku błędu
            alert('Wystąpił nieoczekiwany błąd.');
        });
    }

    // Funkcja pobierająca listę urządzeń
    function fetchDevices() {
        fetch('/configuration/getDevices')
            .then(response => {
                return response.json();
            })
            .then(devices => {
                const deviceList = document.getElementById('DeviceList');
                deviceList.innerHTML = '';

                if (devices.length === 0) {
                    deviceList.innerHTML = `
                        <div class="device-item">
                            <span>Brak urządzeń w bazie.</span>
                        </div>
                    `;
                    return;
                }

                devices.forEach(device => {
                    const deviceItem = document.createElement('div');
                    deviceItem.classList.add('device-item');

                    deviceItem.innerHTML = `
                        <span class="device-info">${sanitizeHTML(device.device_name)}</span>
                        <span class="device-info">${sanitizeHTML(device.type)}</span>
                        <span class="device-info"><a href="http://${sanitizeHTML(device.address_ip)}" target="_blank">${sanitizeHTML(device.address_ip)}</a></span>
                        <span class="device-info-phone">${sanitizeHTML(device.snmp_version)}</span>
                        <span class="device-info-phone">${sanitizeHTML(device.username || 'N/A')}</span>
                        <span class="device-info-phone">
                            <span class="hidden-password" data-password="${sanitizeHTML(device.password || '')}">********</span>
                            ${isAdmin ? '<i class="eye-icon fa-solid fa-eye-low-vision"></i>' : ''}
                        </span>
                        <span class="device-info-phone">
                            ${device.snmp_version === 'ICMP' ? 'N/A' : sanitizeHTML(device.mac_address || 'N/A')}
                        </span>
                        <span class="device-info-phone">${sanitizeHTML(device.description || 'N/A')}</span>
                        <span class="device-actions">
                            ${isAdmin ? `<button class="editButton" data-id="${device.id}"><i class="fa-solid fa-pencil"></i></button>` : ''}
                            ${isAdmin ? `<button class="deleteButton" data-id="${device.id}"><i class="fa-solid fa-trash"></i></button>` : ''}
                        </span>
                    `;

                    deviceList.appendChild(deviceItem);
                });

                if (isAdmin) {
                    addPasswordRevealListeners();
                    addEditButtonsListeners();
                    addDeleteButtonsListeners();
                }
            })
            .catch(error => {
                console.error('Error fetching devices:', error);
                alert('Wystąpił błąd podczas pobierania urządzeń.');
            });
    }

    // Funkcja sanitizująca dane przed wstawieniem do HTML
    function sanitizeHTML(str) {
        const temp = document.createElement('div');
        temp.textContent = str;
        return temp.innerHTML;
    }

    // Funkcja dodająca event listeners do ikon oka dla hasła
    function addPasswordRevealListeners() {
        console.log('Adding password reveal listeners'); // Debugging
        document.querySelectorAll('.eye-icon').forEach(icon => {
            // Event listener dla najechania myszą (mouseenter)
            icon.addEventListener('mouseenter', function() {
                const hiddenPasswordSpan = this.previousElementSibling;
                const actualPassword = hiddenPasswordSpan.getAttribute('data-password');
                if (actualPassword) {
                    hiddenPasswordSpan.textContent = actualPassword;
                    console.log('Password revealed on mouse enter');
                }
            });

            // Event listener dla opuszczenia myszki (mouseleave)
            icon.addEventListener('mouseleave', function() {
                const hiddenPasswordSpan = this.previousElementSibling;
                hiddenPasswordSpan.textContent = '********';
                console.log('Password hidden on mouse leave');
            });

            // Opcjonalnie: Event listener dla kliknięcia, jeśli chcesz, aby hasło było widoczne po kliknięciu
            /*
            icon.addEventListener('click', function() {
                const hiddenPasswordSpan = this.previousElementSibling;
                const actualPassword = hiddenPasswordSpan.getAttribute('data-password');
                if (hiddenPasswordSpan.textContent === '********') {
                    hiddenPasswordSpan.textContent = actualPassword;
                    console.log('Password revealed on click');
                } else {
                    hiddenPasswordSpan.textContent = '********';
                    console.log('Password hidden on click');
                }
            });
            */
        });
    }

    // Funkcja dodająca event listeners do przycisków edycji
    function addEditButtonsListeners() {
        document.querySelectorAll('.editButton').forEach(button => {
            button.addEventListener('click', function() {
                const deviceIdToEdit = this.getAttribute('data-id');
                console.log('Fetching device details for ID:', deviceIdToEdit); // Debugging

                fetch(`/configuration/getDeviceById?id=${encodeURIComponent(deviceIdToEdit)}`)
                    .then(response => {
                        console.log('Fetch response status:', response.status); // Debugging
                        return response.json();
                    })
                    .then(device => {
                        console.log('Device data received:', device); // Debugging
                        if (device.status === 'error') {
                            alert(device.message);
                            return;
                        }

                        const deviceData = device.device;

                        deviceForm.reset();
                        document.getElementById('deviceId').value = deviceData.id;
                        deviceNameInput.value = deviceData.device_name;
                        deviceTypeSelect.value = deviceData.type_id;
                        deviceAddressInput.value = deviceData.address_ip;
                        snmpVersionSelect.value = deviceData.snmp_version_id;
                        userNameInput.value = deviceData.username || '';
                        passwordInput.value = deviceData.password || '';
                        descriptionInput.value = deviceData.description || '';
                        submitButton.textContent = 'Update';
                        deviceModal.style.display = 'block';
                        checkSNMPVersion();
                        clearErrors();
                        addPasswordRevealListeners(); // Upewnij się, że listener jest dodany
                    })
                    .catch(error => {
                        console.error('Error fetching device details:', error);
                        alert('Wystąpił błąd podczas pobierania szczegółów urządzenia.');
                    });
            });
        });
    }

    // Funkcja dodająca event listeners do przycisków usuwania
    function addDeleteButtonsListeners() {
        document.querySelectorAll('.deleteButton').forEach(button => {
            button.addEventListener('click', function() {
                deviceIdToDelete = this.getAttribute('data-id');
                deleteModal.style.display = 'block';
            });
        });
    }

    // Obsługa potwierdzenia usunięcia urządzenia
    const confirmDeleteButton = document.getElementById('confirmDeleteButton');
    const cancelDeleteButton = document.getElementById('cancelDeleteButton');
    const deleteModal = document.getElementById('deleteModal');

    confirmDeleteButton.addEventListener('click', function() {
        if (deviceIdToDelete) {
            fetch('/configuration/deleteDevice', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `deviceId=${encodeURIComponent(deviceIdToDelete)}`
            })
            .then(response => {
                console.log('Delete device response status:', response.status); // Debugging
                return response.json();
            })
            .then(data => {
                console.log('Delete device response:', data); // Debugging
                if (data.status === 'success') {
                    fetchDevices();
                    deleteModal.style.display = 'none';
                } else {
                    console.error('Error:', data.message);
                    alert(`Błąd: ${data.message}`);
                }
            })
            .catch(error => {
                console.error('Error deleting device:', error);
                alert(`Błąd: ${error.message}`);
            });
        }
    });

    // Obsługa anulowania usunięcia urządzenia
    cancelDeleteButton.addEventListener('click', function() {
        deleteModal.style.display = 'none';
    });

    // Funkcja sprawdzająca wersję SNMP i odpowiednio blokująca pola
    function checkSNMPVersion() {
        const selectedValue = snmpVersionSelect.value; // ID wersji SNMP

        if (selectedValue === '4') { // ICMP
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

    // Event listener dla zmiany SNMP Version
    snmpVersionSelect.addEventListener('change', checkSNMPVersion);

    // Obsługa zamykania modali po kliknięciu poza treścią
    closeDeviceModal.addEventListener('click', function() {
        deviceModal.style.display = 'none';
    });

    cancelDeviceButton.addEventListener('click', function() {
        deviceModal.style.display = 'none';
    });

    window.addEventListener('click', function(event) {
        if (event.target == deviceModal) {
            deviceModal.style.display = 'none';
        }
        if (event.target == deleteModal) {
            deleteModal.style.display = 'none';
        }
    });

    // Dodanie event listenera dla przycisku "Add Device"
    if (addDeviceButton) {
        addDeviceButton.addEventListener('click', function() {
            console.log('Add Device button clicked'); // Debugging
            deviceForm.reset();
            document.getElementById('deviceId').value = '';
            submitButton.textContent = 'Submit';
            checkSNMPVersion();
            clearErrors();
            deviceModal.style.display = 'block';
        });
    } else {
        console.error('Add Device button not found');
    }

    // Funkcja pobierająca listę urządzeń na starcie
    fetchDevices();
});
