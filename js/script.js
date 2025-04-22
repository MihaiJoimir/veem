document.addEventListener('DOMContentLoaded', function() {
    // 1. Menu toggle
    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks   = document.querySelector('.nav-links');
    if (menuToggle) {
        menuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });
        document.addEventListener('click', e => {
            if (!e.target.closest('nav')) {
                navLinks.classList.remove('active');
            }
        });
    }

    // 2. Description accordion
    document.querySelectorAll('.accordion-card').forEach(card => {
        const header = card.querySelector('.accordion-header');
        header.addEventListener('click', () => {
            card.classList.toggle('open');
        });
    });
});