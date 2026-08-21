/**
 * Lightweight Lazy Loading & Image Shimmer Helper
 * Automatically handles IntersectionObserver lazy loading with skeleton animation.
 */

document.addEventListener('DOMContentLoaded', () => {
    initLazyImages();
});

function initLazyImages() {
    const lazyImages = document.querySelectorAll('img[data-src], img.lazy-img');

    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    const src = img.getAttribute('data-src') || img.src;

                    if (img.getAttribute('data-src')) {
                        img.src = src;
                        img.removeAttribute('data-src');
                    }

                    img.onload = () => {
                        img.classList.remove('opacity-0', 'scale-95', 'blur-sm');
                        img.classList.add('opacity-100', 'scale-100', 'blur-0');
                        // Remove parent shimmer placeholder if exists
                        const shimmer = img.parentElement?.querySelector('.img-skeleton-shimmer');
                        if (shimmer) shimmer.remove();
                    };

                    imageObserver.unobserve(img);
                }
            });
        }, {
            rootMargin: '100px 0px',
            threshold: 0.01
        });

        lazyImages.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback for older browsers
        lazyImages.forEach(img => {
            if (img.getAttribute('data-src')) {
                img.src = img.getAttribute('data-src');
            }
        });
    }
}

window.initLazyImages = initLazyImages;
