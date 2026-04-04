(function () {
    'use strict';

    /**
     * "Read More" toggle for truncated review text.
     */
    function initReadMore() {
        var links = document.querySelectorAll('.devsroom-greviews-read-more');

        links.forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                var card = this.closest('.devsroom-greviews-card__text--truncated');
                if (!card) return;

                var shortText = card.querySelector('.devsroom-greviews-text-short');
                var fullText = card.querySelector('.devsroom-greviews-text-full');

                if (fullText && shortText) {
                    var isExpanded = fullText.style.display !== 'none';
                    if (isExpanded) {
                        shortText.style.display = '';
                        fullText.style.display = 'none';
                        this.textContent = this.dataset.moreText || 'Read more';
                    } else {
                        shortText.style.display = 'none';
                        fullText.style.display = '';
                        this.textContent = this.dataset.lessText || 'Read less';
                    }
                }
            });
        });
    }

    /**
     * Apply CSS line limit to review text.
     */
    function initLineLimit() {
        var texts = document.querySelectorAll('.devsroom-greviews-card__text');
        texts.forEach(function (el) {
            if (el.classList.contains('devsroom-greviews-card__text--truncated')) return;
            var limit = parseInt(el.style.getPropertyValue('--greviews-line-limit'), 10);
            if (limit > 0) {
                el.style.webkitLineClamp = limit;
            }
        });
    }

    // Initialize on DOM ready.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initReadMore();
            initLineLimit();
        });
    } else {
        initReadMore();
        initLineLimit();
    }
})();
