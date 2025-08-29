// Mobile Menu Functionality
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenuClose = document.querySelector('.mobile-menu-close');
    const mobileMenu = document.querySelector('.mobile-menu');
    const mobileMenuOverlay = document.querySelector('.mobile-menu-overlay');
    const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');
    const body = document.body;

    // Function to open mobile menu
    function openMobileMenu() {
        mobileMenu.classList.add('active');
        mobileMenuOverlay.classList.add('active');
        body.style.overflow = 'hidden'; // Prevent background scrolling
        
        // Focus management for accessibility
        mobileMenuClose.focus();
        
        // Add escape key listener
        document.addEventListener('keydown', handleEscapeKey);
    }

    // Function to close mobile menu
    function closeMobileMenu() {
        mobileMenu.classList.remove('active');
        mobileMenuOverlay.classList.remove('active');
        body.style.overflow = ''; // Restore scrolling
        
        // Return focus to toggle button
        mobileMenuToggle.focus();
        
        // Remove escape key listener
        document.removeEventListener('keydown', handleEscapeKey);
    }

    // Handle escape key press
    function handleEscapeKey(e) {
        if (e.key === 'Escape') {
            closeMobileMenu();
        }
    }

    // Toggle mobile menu
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            openMobileMenu();
        });
    }

    // Close mobile menu
    if (mobileMenuClose) {
        mobileMenuClose.addEventListener('click', function(e) {
            e.preventDefault();
            closeMobileMenu();
        });
    }

    // Close menu when clicking overlay
    if (mobileMenuOverlay) {
        mobileMenuOverlay.addEventListener('click', function(e) {
            e.preventDefault();
            closeMobileMenu();
        });
    }

    // Close menu when clicking navigation links
    mobileNavLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Small delay to allow navigation to start
            setTimeout(closeMobileMenu, 100);
        });
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 991.98) {
            // Close mobile menu on desktop
            closeMobileMenu();
        }
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Close mobile menu if open
                if (mobileMenu.classList.contains('active')) {
                    setTimeout(closeMobileMenu, 300);
                }
            }
        });
    });

    // Enhanced touch support for mobile
    let touchStartY = 0;
    let touchEndY = 0;

    if (mobileMenu) {
        mobileMenu.addEventListener('touchstart', function(e) {
            touchStartY = e.changedTouches[0].screenY;
        });

        mobileMenu.addEventListener('touchend', function(e) {
            touchEndY = e.changedTouches[0].screenY;
            handleSwipeGesture();
        });
    }

    function handleSwipeGesture() {
        const swipeDistance = touchStartY - touchEndY;
        const minSwipeDistance = 50;
        
        // Close menu on swipe left (when menu is at left edge)
        if (Math.abs(swipeDistance) > minSwipeDistance && touchStartY < 100) {
            if (swipeDistance > 0) { // Swipe up
                // Could add functionality for swipe up gesture
            } else { // Swipe down
                // Could add functionality for swipe down gesture
            }
        }
    }

    // Search functionality enhancement for mobile
    const searchIconBtn = document.querySelector('.search-icon-btn');
    const searchInput = document.querySelector('.search-input');
    const searchResults = document.querySelector('.search-results');

    if (searchIconBtn && searchInput) {
        // Handle search input focus on mobile
        searchInput.addEventListener('focus', function() {
            if (window.innerWidth <= 767.98) {
                // Scroll to top to ensure search is visible
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });

        // Close search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchIconBtn.contains(e.target)) {
                searchIconBtn.classList.remove('active');
                if (searchResults) {
                    searchResults.classList.remove('active');
                }
            }
        });
    }

    // Add loading states for better UX
    function showLoadingState(element) {
        const originalContent = element.innerHTML;
        element.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        element.disabled = true;
        
        return function() {
            element.innerHTML = originalContent;
            element.disabled = false;
        };
    }

    // Handle modal triggers from mobile menu
    const modalTriggers = document.querySelectorAll('[data-bs-toggle="modal"]');
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function() {
            // Close mobile menu when opening modals
            if (mobileMenu.classList.contains('active')) {
                closeMobileMenu();
            }
        });
    });
});