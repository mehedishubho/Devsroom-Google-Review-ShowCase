(function () {
    'use strict';

    /**
     * Calculate row spans for masonry layout using CSS Grid.
     */
    function initMasonry() {
        var containers = document.querySelectorAll('.devsroom-greviews-masonry[data-masonry="true"]');

        containers.forEach(function (container) {
            if (container.dataset.masonryInitialized === 'true') return;
            layoutMasonry(container);
            container.dataset.masonryInitialized = 'true';
        });
    }

    /**
     * Apply row spans to cards in a masonry container.
     */
    function layoutMasonry(container) {
        var cards = container.querySelectorAll('.devsroom-greviews-card--masonry');
        var rowHeight = 8; // Must match grid-auto-rows in CSS.

        cards.forEach(function (card) {
            // Reset span so we can measure natural height.
            card.style.gridRowEnd = '';
            var contentHeight = card.getBoundingClientRect().height;
            var rowSpan = Math.ceil((contentHeight + rowHeight) / (rowHeight * 2));
            card.style.gridRowEnd = 'span ' + rowSpan;
        });
    }

    /**
     * Debounce helper.
     */
    function debounce(fn, delay) {
        var timer;
        return function () {
            clearTimeout(timer);
            timer = setTimeout(fn, delay);
        };
    }

    // Initialize on DOM ready and on load (for images).
    function setup() {
        initMasonry();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setup);
    } else {
        setup();
    }

    // Recalculate on load (images affect height).
    window.addEventListener('load', function () {
        var containers = document.querySelectorAll('.devsroom-greviews-masonry[data-masonry="true"]');
        containers.forEach(function (container) {
            layoutMasonry(container);
        });
    });

    // Recalculate on resize (debounced).
    window.addEventListener('resize', debounce(function () {
        var containers = document.querySelectorAll('.devsroom-greviews-masonry[data-masonry="true"]');
        containers.forEach(function (container) {
            layoutMasonry(container);
        });
    }, 250));
})();
