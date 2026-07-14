document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Force hardware acceleration
            navMenu.style.willChange = 'transform';
            navMenu.classList.toggle('active');
            
            // Cleanup
            setTimeout(() => {
                navMenu.style.willChange = 'auto';
            }, 300);
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!navMenu.contains(e.target) && !menuToggle.contains(e.target)) {
                navMenu.classList.remove('active');
            }
        });
    }
});