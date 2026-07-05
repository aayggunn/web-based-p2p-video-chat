$(document).ready(function() {
    $("a.nav-link").on('click', function(event) {
        if (this.hash !== "") {
            event.preventDefault();
            var hash = this.hash;
            $('html, body').animate({
                scrollTop: $(hash).offset().top
            }, 800, function(){
                window.location.hash = hash;
            });
        } 
    });
});

function changeLanguage(lang) {
    const translateElements = document.querySelectorAll('[data-tr], [data-en], [data-de]');
    translateElements.forEach(el => {
        el.textContent = el.getAttribute(`data-${lang}`);
    });
    document.querySelectorAll('.nav-link img').forEach(img => {
        img.classList.remove('active');
    });
    document.querySelector(`.nav-link[onclick="changeLanguage('${lang}')"] img`).classList.add('active');

    // Collapse the navbar after changing language
    const navbarCollapse = document.querySelector('#navbarSupportedContent');
    navbarCollapse.classList.remove('show');
}

var userLang = navigator.language || navigator.userLanguage;
userLang = userLang.toLowerCase();
if (userLang !== 'de' && userLang !== 'en' && userLang !== 'tr') {
    userLang = 'en';
} else if (userLang.startsWith('tr')) {
    userLang = 'tr';
} else if (userLang.startsWith('de')) {
    userLang = 'de';
} else {
    userLang = 'en';
}
changeLanguage(userLang);

document.addEventListener('click', function(event) {
    const toggleButton = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('#navbarSupportedContent');
    const isClickInsideNavbar = navbarCollapse.contains(event.target);
    const isToggleButton = toggleButton.contains(event.target);

    if (!isClickInsideNavbar && !isToggleButton) {
        navbarCollapse.classList.remove('show');
    }

    // Close menu when a nav-link or language link is clicked
    if (event.target.classList.contains('nav-link') || event.target.classList.contains('lang-link')) {
        navbarCollapse.classList.remove('show');
    }
});