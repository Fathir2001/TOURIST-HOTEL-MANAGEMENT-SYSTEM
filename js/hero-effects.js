// Hero Section Effects - Parallax Scrolling
document.addEventListener('DOMContentLoaded', function() {
    const heroSection = document.querySelector('.hero-section');
    const heroImage = document.querySelector('.hero-image');
    
    if (heroSection && heroImage) {
        // Parallax scrolling effect
        window.addEventListener('scroll', function() {
            const scrollPosition = window.pageYOffset;
            const heroHeight = heroSection.offsetHeight;
            
            // Only apply parallax if user hasn't scrolled past the hero section
            if (scrollPosition < heroHeight) {
                // Move image slower than scroll (0.5 = half speed)
                heroImage.style.transform = `translateY(${scrollPosition * 0.5}px) scale(${1 + scrollPosition * 0.0001})`;
            }
        });
        
        // Smooth fade out of scroll indicator when scrolling
        const scrollIndicator = document.querySelector('.scroll-indicator');
        if (scrollIndicator) {
            window.addEventListener('scroll', function() {
                const scrollPosition = window.pageYOffset;
                const opacity = Math.max(1 - scrollPosition / 300, 0);
                scrollIndicator.style.opacity = opacity;
            });
        }
    }
});
