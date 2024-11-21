
function toggleDropdown() {
    const dropdown = document.querySelector('.profile-dropdown');
    dropdown.classList.toggle('show');
}


document.addEventListener('click', function (event) {
    const profileButton = document.querySelector('.profile-button');
    const dropdown = document.querySelector('.profile-dropdown');
    
    
    if (!profileButton.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});
