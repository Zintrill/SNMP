document.addEventListener("DOMContentLoaded", function() {
    const userButton = document.getElementById('userButton');
    const optionsMenu = document.getElementById('optionsMenu');

    userButton.addEventListener('click', () => {
        optionsMenu.classList.toggle('active');
    });
});
