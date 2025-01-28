document.addEventListener('DOMContentLoaded', function () {
    // Elementy listy użytkowników i formularza
    const userList = document.getElementById('userList');
    const userForm = document.getElementById('userForm');
    
    // Elementy modali
    const userModal = document.getElementById('userModal');
    const deleteModal = document.getElementById('deleteModal');
    
    // Przyciski zamykające modale
    const closeUserModal = document.getElementById('closeUserModal');
    const closeDeleteModal = document.getElementById('closeDeleteModal');
    
    // Przyciski akcji w modalach
    const confirmDeleteButton = document.getElementById('confirmDeleteButton');
    const cancelDeleteButton = document.getElementById('cancelDeleteButton');
    const addUserButton = document.getElementById('addUserButton');
    
    // Pole ukryte do przechowywania ID użytkownika
    const userIdInput = document.getElementById('userId');

    let userIdToDelete = null;

    // Funkcja do pobierania listy użytkowników
    function fetchUsers() {
        fetch('/getUsers')
            .then(response => response.json())
            .then(users => {
                userList.innerHTML = '';

                users.forEach(user => {
                    const userItem = document.createElement('div');
                    userItem.classList.add('user-item');

                    userItem.innerHTML = `
                        <span class="user-info-phone">${user.fullname}</span>
                        <span class="user-info">${user.username}</span>
                        <span class="user-info-phone">
                            <span class="hidden-password" data-password="${user.password}">********</span>
                            <i class="eye-icon fa-solid fa-eye-low-vision"></i>
                        </span>
                        <span class="user-info">${user.role}</span>
                        <span class="user-info">${user.email}</span>
                        <button class="editButton" data-id="${user.id}"><i class="fa-solid fa-pencil"></i></button>
                        <button class="deleteButton" data-id="${user.id}"><i class="fa-solid fa-trash"></i></button>
                    `;

                    userList.appendChild(userItem);
                });

                // Obsługa podglądu hasła
                document.querySelectorAll('.eye-icon').forEach(icon => {
                    icon.addEventListener('mousedown', async function() {
                        const hiddenPassword = this.previousElementSibling;
                        const decryptedPassword = await decryptPassword(hiddenPassword.getAttribute('data-password'));
                        hiddenPassword.textContent = decryptedPassword;
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

                // Obsługa edycji użytkownika
                document.querySelectorAll('.editButton').forEach(button => {
                    button.addEventListener('click', function () {
                        const userId = this.getAttribute('data-id');

                        fetch(`getUserById?id=${userId}`)
                            .then(response => response.json())
                            .then(user => {
                                userIdInput.value = user.id;
                                document.getElementById('fullName').value = user.fullname || '';
                                document.getElementById('username').value = user.username || '';
                                document.getElementById('userPassword').value = '';
                                document.getElementById('userRole').value = user.role || '';
                                document.getElementById('email').value = user.email || '';

                                document.querySelector('.confirm-button').textContent = 'Update';
                                userModal.style.display = 'block';
                            });
                    });
                });

                // Obsługa usuwania użytkownika
                document.querySelectorAll('.deleteButton').forEach(button => {
                    button.addEventListener('click', function () {
                        userIdToDelete = this.getAttribute('data-id');
                        deleteModal.style.display = 'block';
                    });
                });
            })
            .catch(error => console.error('Error fetching users:', error));
    }

    // Funkcja do zamykania modali
    function closeModal(modal) {
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Obsługa zamknięcia modala edycji/dodawania użytkownika
    if (closeUserModal) {
        closeUserModal.addEventListener('click', function () {
            closeModal(userModal);
        });
    }

    // Obsługa zamknięcia modala edycji/dodawania użytkownika za pomocą przycisku anulowania
    const cancelUserButton = document.getElementById('cancelUserButton');
    if (cancelUserButton) {
        cancelUserButton.addEventListener('click', function () {
            closeModal(userModal);
        });
    }

    // Obsługa zamknięcia modala usuwania użytkownika
    if (closeDeleteModal) {
        closeDeleteModal.addEventListener('click', function () {
            closeModal(deleteModal);
        });
    }

    if (cancelDeleteButton) {
        cancelDeleteButton.addEventListener('click', function () {
            closeModal(deleteModal);
        });
    }

    // Obsługa potwierdzenia usunięcia użytkownika
    if (confirmDeleteButton) {
        confirmDeleteButton.addEventListener('click', function () {
            if (userIdToDelete) {
                fetch('/deleteUser', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `userId=${encodeURIComponent(userIdToDelete)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        fetchUsers();
                        closeModal(deleteModal);
                        userIdToDelete = null;
                    } else {
                        console.error('Error:', data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        });
    }

    // Obsługa dodawania/edycji użytkownika
    userForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const userId = userIdInput.value.trim();
        const url = userId ? '/updateUser' : '/addUser';

        const formData = new FormData(userForm);
        if (userId) {
            formData.append('userId', userId);
        }

        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                fetchUsers();
                closeModal(userModal);
                userForm.reset();
                userIdInput.value = '';
            } else {
                console.error('Error:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });

    // Obsługa otwierania modala dodawania użytkownika
    if (addUserButton) {
        addUserButton.addEventListener('click', function () {
            userIdInput.value = '';
            userForm.reset();
            document.querySelector('.confirm-button').textContent = 'Submit';
            userModal.style.display = 'block';
        });
    }

    // 🛠️ Obsługa zamykania modali po kliknięciu poza modalem
    window.addEventListener('click', function (event) {
        if (event.target === userModal) {
            closeModal(userModal);
        }
        if (event.target === deleteModal) {
            closeModal(deleteModal);
        }
    });

    // 🛠️ Obsługa zamykania modali klawiszem Escape
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeModal(userModal);
            closeModal(deleteModal);
        }
    });

    // Inicjalizacja pobierania użytkowników
    fetchUsers();
});

// Przykładowa funkcja decryptPassword (należy zastąpić rzeczywistą implementacją)
async function decryptPassword(encryptedPassword) {
    // Implementacja deszyfrowania hasła
    // Na potrzeby przykładu zwrócimy samo zaszyfrowane hasło
    return encryptedPassword;
}
