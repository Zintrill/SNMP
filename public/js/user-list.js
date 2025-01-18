document.addEventListener('DOMContentLoaded', function() {
    const userRole = parseInt(document.querySelector('meta[name="user-role"]').content, 10);

    if (userRole > 1) {
        document.getElementById('addUserButton').style.display = 'none';
    }

    fetchUsers();

    let userIdToDelete = null;
    let userIdToEdit = null;

    const deleteModal = document.getElementById('deleteModal');
    const closeModal = document.getElementById('closeModal');
    const confirmDeleteButton = document.getElementById('confirmDeleteButton');
    const cancelDeleteButton = document.getElementById('cancelDeleteButton');

    const userModal = document.getElementById('userModal');
    const closeUserModal = document.getElementById('closeUserModal');
    const cancelUserButton = document.getElementById('cancelUserButton');
    const userForm = document.getElementById('userForm');
    const fullNameInput = document.getElementById('fullName');
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('userPassword');
    const roleSelect = document.getElementById('userRole');
    const usernameError = document.getElementById('usernameError');
    const emailError = document.getElementById('emailError');
    const submitButton = userForm.querySelector('.confirm-button');
    const addUserButton = document.getElementById('addUserButton');

    addUserButton.addEventListener('click', function() {
        userIdToEdit = null;
        userForm.reset();
        usernameError.textContent = '';
        emailError.textContent = '';
        submitButton.textContent = 'Submit';
        userModal.style.display = 'block';
    });

    closeUserModal.addEventListener('click', function() {
        userModal.style.display = 'none';
    });

    cancelUserButton.addEventListener('click', function() {
        userModal.style.display = 'none';
    });

    window.addEventListener('click', function(event) {
        if (event.target == userModal) {
            userModal.style.display = 'none';
        }
        if (event.target == deleteModal) {
            deleteModal.style.display = 'none';
        }
    });

    function openModal() {
        deleteModal.style.display = 'block';
    }

    function closeModalWindow() {
        deleteModal.style.display = 'none';
    }

    closeModal.addEventListener('click', closeModalWindow);
    cancelDeleteButton.addEventListener('click', closeModalWindow);

    confirmDeleteButton.addEventListener('click', function() {
        if (userIdToDelete) {
            fetch('deleteUser', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `userId=${userIdToDelete}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    fetchUsers();
                    closeModalWindow();
                } else {
                    console.error('Error:', data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });

    function fetchUsers() {
        fetch('getUsers')
            .then(response => response.json())
            .then(users => {
                const userList = document.getElementById('userList');
                userList.innerHTML = '';
                users.forEach(user => {
                    const userItem = document.createElement('div');
                    userItem.classList.add('user-item');

                    userItem.innerHTML = `
                        <span class="user-info-phone">${user.fullname}</span>
                        <span class="user-info">${user.username}</span>
                        <span class="user-info-phone">
                        <span class="hidden-password" data-password="${user.password}">********</span>${userRole == 1 ? '<i class="eye-icon fa-solid fa-eye-low-vision"></i>' : ''}</span>
                        <span class="user-info">${user.role}</span>
                        <span class="user-info">${user.email}</span>
                        ${userRole == 1 ? `<button class="editButton" data-id="${user.id}"><i class="fa-solid fa-pencil"></i></button>` : ''}
                        ${userRole == 1 ? `<button class="deleteButton" data-id="${user.id}"><i class="fa-solid fa-trash"></i></button>` : ''}
                    `;

                    userList.appendChild(userItem);
                });

                if (userRole == 1) {
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

                    document.querySelectorAll('.editButton').forEach(button => {
                        button.addEventListener('click', function() {
                            userIdToEdit = this.getAttribute('data-id');
                            fetch(`getUserById?id=${userIdToEdit}`)
                                .then(response => response.json())
                                .then(user => {
                                    if (user) {
                                        fullNameInput.value = user.fullname || '';
                                        usernameInput.value = user.username || '';
                                        decryptPassword(user.password).then(decryptedPassword => {
                                            passwordInput.value = decryptedPassword || '';
                                        });
                                        roleSelect.value = user.permission_id || '';
                                        emailInput.value = user.email || '';
                                    }
                                    usernameError.textContent = '';
                                    emailError.textContent = '';
                                    submitButton.textContent = 'Update';
                                    userModal.style.display = 'block';
                                })
                                .catch(error => console.error('Error:', error));
                        });
                    });

                    document.querySelectorAll('.deleteButton').forEach(button => {
                        button.addEventListener('click', function() {
                            userIdToDelete = this.getAttribute('data-id');
                            openModal();
                        });
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred: ' + error.message);
            });
    }

    userForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const url = userIdToEdit ? 'updateUser' : 'addUser';
        const formData = new FormData(userForm);
        if (userIdToEdit) {
            formData.append('userId', userIdToEdit);
        }
    
        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                fetchUsers();
                userModal.style.display = 'none';
                userForm.reset();
            } else {
                if (data.message.includes('Username is already taken')) {
                    usernameError.textContent = data.message;
                } else {
                    usernameError.textContent = '';
                }
    
                if (data.message.includes('Email is already taken')) {
                    emailError.textContent = data.message;
                } else {
                    emailError.textContent = '';
                }
    
                console.error('Error:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });

    async function decryptPassword(encryptedPassword) {
        const response = await fetch('decryptPassword', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ password: encryptedPassword })
        });
        const decrypted = await response.text();
        return decrypted;
    }
});
