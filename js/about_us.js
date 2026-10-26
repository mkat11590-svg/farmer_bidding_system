// Counter Animation for Stats
function animateCounters() {
    const counters = document.querySelectorAll('.stat-number');
    
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.getAttribute('data-count'));
                const duration = 2000; // 2 seconds
                const increment = target / (duration / 16); // 60 FPS
                let current = 0;
                
                const updateCounter = () => {
                    current += increment;
                    if (current < target) {
                        counter.textContent = Math.floor(current).toLocaleString();
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = target.toLocaleString() + (counter.textContent.includes('Million') ? '' : '');
                    }
                };
                
                updateCounter();
                observer.unobserve(counter);
            }
        });
    }, observerOptions);
    
    counters.forEach(counter => observer.observe(counter));
}

// Simple AOS (Animate On Scroll) Implementation
function initScrollAnimations() {
    const elements = document.querySelectorAll('[data-aos]');
    
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const element = entry.target;
                const animation = element.getAttribute('data-aos');
                const delay = element.getAttribute('data-aos-delay') || 0;
                
                setTimeout(() => {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0) translateX(0) scale(1) rotateY(0)';
                }, delay);
                
                observer.unobserve(element);
            }
        });
    }, observerOptions);
    
    elements.forEach(element => {
        const animation = element.getAttribute('data-aos');
        
        // Set initial states based on animation type
        element.style.transition = 'all 0.6s ease';
        element.style.opacity = '0';
        
        if (animation === 'fade-up') {
            element.style.transform = 'translateY(50px)';
        } else if (animation === 'fade-down') {
            element.style.transform = 'translateY(-50px)';
        } else if (animation === 'fade-left') {
            element.style.transform = 'translateX(50px)';
        } else if (animation === 'fade-right') {
            element.style.transform = 'translateX(-50px)';
        } else if (animation === 'zoom-in') {
            element.style.transform = 'scale(0.8)';
        } else if (animation === 'flip-left') {
            element.style.transform = 'rotateY(-180deg)';
        }
        
        observer.observe(element);
    });
}

// Smooth Scroll for Anchor Links
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && document.querySelector(href)) {
                e.preventDefault();
                document.querySelector(href).scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Add Active State to Navigation
function updateActiveNav() {
    const currentPage = window.location.pathname.split('/').pop();
    
    document.querySelectorAll('.nav-link').forEach(link => {
        const href = link.getAttribute('href');
        if (href && href.includes('aboutUs.html')) {
            link.classList.add('active');
        }
    });
}

// Initialize all functions when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('About Us page loaded');
    
    // Wait a bit for content to load
    setTimeout(() => {
        animateCounters();
        initScrollAnimations();
        initSmoothScroll();
        updateActiveNav();
    }, 100);
});

