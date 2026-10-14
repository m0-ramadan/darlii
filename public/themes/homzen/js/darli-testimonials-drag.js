(function () {
    'use strict';

    /**
     * Attach smooth mouse drag-to-scroll to any horizontal overflow element.
     */
    function enableDraggableScroll(slider) {
        if (!slider || slider.dataset.darliMouseDrag === '1') {
            return;
        }

        slider.dataset.darliMouseDrag = '1';
        slider.style.cursor = 'grab';
        slider.style.userSelect = 'none';
        slider.style.webkitUserSelect = 'none';

        slider.querySelectorAll('img').forEach(function (image) {
            image.draggable = false;
        });

        // 1. If this element is a Swiper container, configure Swiper parameters
        if (slider.swiper || slider.classList.contains('swiper')) {
            function configureSwiper() {
                if (!slider.swiper) return false;
                slider.swiper.allowTouchMove = true;
                if (slider.swiper.params) {
                    slider.swiper.params.allowTouchMove = true;
                    slider.swiper.params.simulateTouch = true;
                    slider.swiper.params.grabCursor = true;
                    slider.swiper.params.preventClicks = true;
                    slider.swiper.params.preventClicksPropagation = true;
                }
                if (typeof slider.swiper.setGrabCursor === 'function') {
                    slider.swiper.setGrabCursor();
                }
                return true;
            }

            if (!configureSwiper()) {
                var attempts = 0;
                var timer = window.setInterval(function () {
                    attempts += 1;
                    if (configureSwiper() || attempts >= 20) {
                        window.clearInterval(timer);
                    }
                }, 100);
            }
        }

        // 2. Generic Drag-to-Scroll for CSS flex/overflow-x containers
        var isDown = false;
        var startX = 0;
        var startScrollLeft = 0;
        var clickPrevented = false;

        slider.addEventListener('mousedown', function (e) {
            if (e.button !== 0) return; // Only primary button (left click)
            isDown = true;
            clickPrevented = false;
            slider.classList.add('darli-is-dragging');
            slider.classList.add('is-dragging');
            slider.style.cursor = 'grabbing';
            slider.style.scrollBehavior = 'auto';
            slider.style.scrollSnapType = 'none';
            startX = e.pageX;
            startScrollLeft = slider.scrollLeft;
        });

        window.addEventListener('mousemove', function (e) {
            if (!isDown) return;
            var dx = e.pageX - startX;
            if (Math.abs(dx) > 4) {
                clickPrevented = true;
            }
            slider.scrollLeft = startScrollLeft - dx;
        });

        function finishDrag() {
            if (!isDown) return;
            isDown = false;
            slider.classList.remove('darli-is-dragging');
            slider.classList.remove('is-dragging');
            slider.style.cursor = 'grab';
            slider.style.scrollBehavior = 'smooth';
            slider.style.scrollSnapType = 'x mandatory';
            setTimeout(function () {
                clickPrevented = false;
            }, 50);
        }

        window.addEventListener('mouseup', finishDrag);
        slider.addEventListener('mouseleave', finishDrag);

        // Prevent clicking links/cards when user was actually dragging
        slider.addEventListener('click', function (e) {
            if (clickPrevented) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, true);

        // Mouse wheel horizontal scrolling
        slider.addEventListener('wheel', function (e) {
            if (Math.abs(e.deltaY) > Math.abs(e.deltaX) && slider.scrollWidth > slider.clientWidth) {
                e.preventDefault();
                var step = 280;
                var dir = document.dir === 'rtl' ? -1 : 1;
                slider.scrollBy({
                    left: (e.deltaY > 0 ? step : -step) * dir,
                    behavior: 'smooth'
                });
            }
        }, { passive: false });
    }

    function init() {
        var selectors = [
            '.darli-testimonials-grid',
            '.darli-services-testi-track',
            '#darli-services-testi-track',
            '#darli-about-testimonials-container',
            '.darli-partners-grid',
            '.tf-sw-testimonial',
            '.tf-sw-property',
            '.tf-sw-location',
            '.darli-drag-scroll',
            '[data-darli-drag]'
        ];

        selectors.forEach(function (sel) {
            document.querySelectorAll(sel).forEach(enableDraggableScroll);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    document.addEventListener('shortcode.loaded', init);

    if (window.MutationObserver) {
        new MutationObserver(init).observe(document.documentElement, {
            childList: true,
            subtree: true
        });
    }
})();
