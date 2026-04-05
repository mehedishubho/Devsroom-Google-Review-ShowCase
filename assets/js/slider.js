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
            var slidesDesktop = parseInt(container.dataset.slidesPerView, 10) || 3;
            var slidesTablet = parseInt(container.dataset.slidesPerViewTablet, 10) || Math.max(1, slidesDesktop - 1);
            var slidesMobile = parseInt(container.dataset.slidesPerViewMobile, 10) || 1;
            var scrollDesktop = parseInt(container.dataset.slidesPerScroll, 10) || 1;
            var scrollTablet = parseInt(container.dataset.slidesPerScrollTablet, 10) || 1;
            var scrollMobile = parseInt(container.dataset.slidesPerScrollMobile, 10) || 1;
            var pauseOnHover = container.dataset.pauseOnHover !== 'false';
            var pauseOnInteraction = container.dataset.pauseOnInteraction !== 'false';
            var infiniteScroll = container.dataset.infiniteScroll !== 'false';
            var transitionDuration = parseInt(container.dataset.transitionDuration, 10) || 300;
            var direction = container.dataset.direction || 'ltr';

            var hasPagination = !!container.querySelector('.swiper-pagination');
            var hasNavigation = !!container.querySelector('.swiper-button-next');

            var config = {
                slidesPerView: slidesMobile,
                slidesPerGroup: scrollMobile,
                spaceBetween: gap,
                speed: transitionDuration,
                loop: infiniteScroll,
                direction: 'horizontal',
                rtl: direction === 'rtl',
                breakpoints: {
                    640: {
                        slidesPerView: slidesTablet,
                        slidesPerGroup: scrollTablet,
                    },
                    1024: {
                        slidesPerView: slidesDesktop,
                        slidesPerGroup: scrollDesktop,
                    },
                },
            };

            if (hasPagination) {
                config.pagination = {
                    el: container.querySelector('.swiper-pagination'),
                    clickable: true,
                };
            }

            if (hasNavigation) {
                config.navigation = {
                    nextEl: container.querySelector('.swiper-button-next'),
                    prevEl: container.querySelector('.swiper-button-prev'),
                };
            }

            if (autoplay) {
                config.autoplay = {
                    delay: speed,
                    disableOnInteraction: !pauseOnInteraction,
                    pauseOnMouseEnter: pauseOnHover,
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
