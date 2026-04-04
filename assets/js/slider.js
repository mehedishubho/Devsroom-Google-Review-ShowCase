(function () {
    'use strict';

    /**
     * Initialize Swiper on all slider containers.
     */
    function initSliders() {
        var containers = document.querySelectorAll('.devsroom-greviews-slider');

        if (typeof Swiper === 'undefined' || containers.length === 0) {
            return;
        }

        containers.forEach(function (container) {
            if (container.dataset.swiperInit === 'true') return;

            var autoplay = container.dataset.autoplay === 'true';
            var speed = parseInt(container.dataset.speed, 10) || 3000;
            var gap = parseInt(container.dataset.gap, 10) || 20;

            var config = {
                slidesPerView: 1,
                spaceBetween: gap,
                pagination: {
                    el: container.querySelector('.swiper-pagination'),
                    clickable: true,
                },
                navigation: {
                    nextEl: container.querySelector('.swiper-button-next'),
                    prevEl: container.querySelector('.swiper-button-prev'),
                },
                breakpoints: {
                    640: {
                        slidesPerView: 2,
                    },
                    1024: {
                        slidesPerView: 3,
                    },
                },
            };

            if (autoplay) {
                config.autoplay = {
                    delay: speed,
                    disableOnInteraction: false,
                };
            }

            new Swiper(container, config);
            container.dataset.swiperInit = 'true';
        });
    }

    // Initialize on DOM ready.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSliders);
    } else {
        initSliders();
    }
})();
