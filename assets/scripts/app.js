/**
 * Movie Collection App JavaScript
 * Vanilla JavaScript for minimal functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initImageLoading();
    initSearch();
    initMobileMenu();
    
    console.log('Movie Collection App loaded');
});

/**
 * Image loading with fallback and lazy loading
 */
function initImageLoading() {
    // Handle broken images
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.addEventListener('error', function() {
            // Create fallback placeholder
            const fallback = document.createElement('div');
            fallback.className = 'bg-light d-flex align-items-center justify-content-center';
            fallback.style.height = this.offsetHeight + 'px';
            fallback.innerHTML = '<span class="text-muted fs-1">🎬</span>';
            
            // Replace broken image
            this.parentNode.replaceChild(fallback, this);
        });
        
        // Add loading class for smooth transitions
        img.addEventListener('load', function() {
            this.classList.remove('loading');
        });
    });
    
    // Intersection Observer for lazy loading (if not natively supported)
    if (!('loading' in HTMLImageElement.prototype)) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('loading');
                    observer.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            img.classList.add('loading');
            imageObserver.observe(img);
        });
    }
}

/**
 * Search functionality
 */
function initSearch() {
    const searchForms = document.querySelectorAll('form[action*="search"]');
    
    searchForms.forEach(form => {
        const searchInput = form.querySelector('input[name="q"]');
        
        if (searchInput) {
            // Auto-focus on search page
            if (window.location.pathname.includes('/search')) {
                searchInput.focus();
            }
            
            // Search validation
            form.addEventListener('submit', function(e) {
                const query = searchInput.value.trim();
                
                if (query.length < 2) {
                    e.preventDefault();
                    showAlert('Veuillez saisir au moins 2 caractères pour la recherche.', 'warning');
                    searchInput.focus();
                    return false;
                }
                
                // Add loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Recherche...';
                    submitBtn.disabled = true;
                    
                    // Reset after timeout (in case form doesn't submit)
                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 5000);
                }
            });
            
            // Clear search
            const clearBtn = document.createElement('button');
            clearBtn.type = 'button';
            clearBtn.className = 'btn-close btn-close-search';
            clearBtn.style.cssText = 'position: absolute; right: 45px; top: 50%; transform: translateY(-50%); z-index: 5;';
            clearBtn.style.display = searchInput.value ? 'block' : 'none';
            
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                this.style.display = 'none';
                searchInput.focus();
            });
            
            const inputGroup = searchInput.closest('.input-group');
            if (inputGroup) {
                inputGroup.style.position = 'relative';
                inputGroup.appendChild(clearBtn);
                
                searchInput.addEventListener('input', function() {
                    clearBtn.style.display = this.value ? 'block' : 'none';
                });
            }
        }
    });
}

/**
 * Mobile menu functionality
 */
function initMobileMenu() {
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    if (navbarToggler && navbarCollapse) {
        // Close mobile menu when clicking on a link
        const navLinks = navbarCollapse.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.getComputedStyle(navbarToggler).display !== 'none') {
                    navbarCollapse.classList.remove('show');
                }
            });
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!navbarCollapse.contains(e.target) && !navbarToggler.contains(e.target)) {
                navbarCollapse.classList.remove('show');
            }
        });
    }
}

/**
 * Utility function to show alerts
 */
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

/**
 * Smooth scroll to top
 */
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Add scroll to top button if page is long enough
window.addEventListener('scroll', function() {
    const scrollBtn = document.getElementById('scrollToTop');
    if (window.pageYOffset > 300) {
        if (!scrollBtn) {
            const btn = document.createElement('button');
            btn.id = 'scrollToTop';
            btn.innerHTML = '↑';
            btn.className = 'btn btn-primary rounded-circle';
            btn.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 1000;
                width: 50px;
                height: 50px;
                font-size: 20px;
                opacity: 0.8;
                display: none;
            `;
            btn.onclick = scrollToTop;
            document.body.appendChild(btn);
        }
        document.getElementById('scrollToTop').style.display = 'block';
    } else if (scrollBtn) {
        scrollBtn.style.display = 'none';
    }
});

/**
 * Keyboard shortcuts
 */
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K to focus search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.querySelector('input[name="q"]');
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    }
    
    // Escape to clear search or close modals
    if (e.key === 'Escape') {
        const activeInput = document.activeElement;
        if (activeInput && activeInput.name === 'q') {
            activeInput.blur();
        }
    }
});