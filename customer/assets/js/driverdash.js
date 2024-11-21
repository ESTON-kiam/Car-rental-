document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const profileButton = document.querySelector('.profile-button');
    const profileDropdown = document.getElementById('profile-menu');
    
    // Toggle dropdown on button click
    profileButton.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const isExpanded = profileButton.getAttribute('aria-expanded') === 'true';
        
        if (isExpanded) {
            closeDropdown();
        } else {
            openDropdown();
        }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!profileDropdown.contains(e.target) && !profileButton.contains(e.target)) {
            closeDropdown();
        }
    });
    
    function openDropdown() {
        profileDropdown.removeAttribute('hidden');
        profileButton.setAttribute('aria-expanded', 'true');
    }
    
    function closeDropdown() {
        profileDropdown.setAttribute('hidden', '');
        profileButton.setAttribute('aria-expanded', 'false');
    }
});
