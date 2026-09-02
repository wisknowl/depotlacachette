document.addEventListener('DOMContentLoaded', function() {
    // Sync CSS var --header-height with the actual logo height
    const logoImg = document.querySelector('.sidebar-header .logo-full');
    if (logoImg) {
        const updateHeaderHeight = () => {
            if (logoImg.offsetHeight > 0) {
                document.documentElement.style.setProperty('--header-height', logoImg.offsetHeight + 'px');
            }
        };
        logoImg.addEventListener('load', updateHeaderHeight);
        window.addEventListener('resize', updateHeaderHeight);
        if (logoImg.complete) {
            updateHeaderHeight();
        }
    }
    
    // Sidebar Toggle Logic
    const toggleBtn = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const appContainer = document.getElementById('app-container');
    
    if (toggleBtn && sidebar && appContainer) {
        toggleBtn.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('mobile-open');
            } else {
                sidebar.classList.toggle('collapsed');
                appContainer.classList.toggle('collapsed');
            }
        });
    }

    // Close sidebar on mobile when clicking outside
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 768 && sidebar) {
            if (sidebar.classList.contains('mobile-open')) {
                if (!sidebar.contains(event.target) && event.target !== toggleBtn && !toggleBtn.contains(event.target)) {
                    sidebar.classList.remove('mobile-open');
                }
            }
        }
    });

    // Sidebar Submenu Dropdown Accordion Toggle
    document.querySelectorAll('.sidebar-dropdown > .dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const parent = this.parentElement;
            const submenu = parent.querySelector('.sidebar-submenu');
            
            // If sidebar is collapsed on desktop, expand it upon clicking dropdown
            if (sidebar && sidebar.classList.contains('collapsed')) {
                sidebar.classList.remove('collapsed');
                if (appContainer) appContainer.classList.remove('collapsed');
            }

            if (submenu) {
                parent.classList.toggle('open');
                if (parent.classList.contains('open')) {
                    submenu.style.display = 'block';
                } else {
                    submenu.style.display = 'none';
                }
            }
        });
    });

    // User Profile Dropdown Toggle
    const userTrigger = document.getElementById('user-profile-trigger');
    const userContainer = document.getElementById('user-dropdown-container');

    if (userTrigger && userContainer) {
        userTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            userContainer.classList.toggle('open');
        });

        document.addEventListener('click', function(e) {
            if (!userContainer.contains(e.target)) {
                userContainer.classList.remove('open');
            }
        });
    }
});
