
const menuToggle = document.getElementById('menu-toggle');
const sidebar = document.querySelector('.sidebar');
const profileButton = document.querySelector('.profile-button');
const profileMenu = document.getElementById('profile-menu');
const dropdownToggles = document.querySelectorAll('.dropdown-toggle');


menuToggle?.addEventListener('click', () => {
    sidebar?.classList.toggle('active');
    menuToggle.setAttribute('aria-expanded', 
        sidebar?.classList.contains('active').toString());
});


document.addEventListener('click', (e) => {
    if (sidebar?.classList.contains('active') &&
        !sidebar.contains(e.target) &&
        !menuToggle?.contains(e.target)) {
        sidebar.classList.remove('active');
        menuToggle?.setAttribute('aria-expanded', 'false');
    }
});


const toggleProfileMenu = (show) => {
    profileMenu?.toggleAttribute('hidden', !show);
    profileButton?.setAttribute('aria-expanded', show.toString());
};

profileButton?.addEventListener('click', (e) => {
    e.stopPropagation();
    const isHidden = profileMenu?.hasAttribute('hidden');
    toggleProfileMenu(isHidden);
});


document.addEventListener('click', (e) => {
    if (!profileButton?.contains(e.target)) {
        toggleProfileMenu(false);
    }
});


dropdownToggles.forEach(toggle => {
    toggle.addEventListener('click', (e) => {
        e.preventDefault();
        const dropdown = toggle.nextElementSibling;
        const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
        
        dropdownToggles.forEach(otherToggle => {
            if (otherToggle !== toggle) {
                otherToggle.setAttribute('aria-expanded', 'false');
                otherToggle.nextElementSibling?.setAttribute('hidden', '');
            }
        });

        
        toggle.setAttribute('aria-expanded', (!isExpanded).toString());
        dropdown?.toggleAttribute('hidden', isExpanded);
    });
});


document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        toggleProfileMenu(false);
        sidebar?.classList.remove('active');
        menuToggle?.setAttribute('aria-expanded', 'false');
    }
});


const profileImg = document.getElementById('profile-img');
const uploadInput = document.getElementById('upload');

uploadInput?.addEventListener('change', (e) => {
    const file = e.target.files?.[0];
    if (file && profileImg) {
        const reader = new FileReader();
        reader.onload = (event) => {
            profileImg.src = event.target?.result?.toString() ?? '';
        };
        reader.readAsDataURL(file);
    }
});


const setTheme = (theme) => {
    document.documentElement.className = theme;
    localStorage.setItem('theme', theme);
};

const savedTheme = localStorage.getItem('theme');
if (savedTheme) {
    setTheme(savedTheme);
}


const setLoadingState = (element, isLoading) => {
    if (isLoading) {
        element.classList.add('loading');
        element.setAttribute('aria-busy', 'true');
    } else {
        element.classList.remove('loading');
        element.setAttribute('aria-busy', 'false');
    }
};


const trackEvent = (category, action, label) => {
    if (typeof window.gtag === 'function') {
        window.gtag('event', action, {
            'event_category': category,
            'event_label': label
        });
    }
};


const validateForm = (form) => {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
    });

    return isValid;
};


const initTooltips = () => {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(tooltip => {
        tooltip.addEventListener('mouseenter', (e) => {
            const text = tooltip.getAttribute('data-tooltip');
            if (text) {
                const tooltipEl = document.createElement('div');
                tooltipEl.className = 'tooltip';
                tooltipEl.textContent = text;
                document.body.appendChild(tooltipEl);

                const rect = tooltip.getBoundingClientRect();
                tooltipEl.style.top = `${rect.top - tooltipEl.offsetHeight - 5}px`;
                tooltipEl.style.left = `${rect.left + (rect.width - tooltipEl.offsetWidth) / 2}px`;
            }
        });

        tooltip.addEventListener('mouseleave', () => {
            const tooltip = document.querySelector('.tooltip');
            tooltip?.remove();
        });
    });
};


document.addEventListener('DOMContentLoaded', () => {
    initTooltips();
});