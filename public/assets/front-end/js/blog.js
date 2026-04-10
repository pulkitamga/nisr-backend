document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    document.querySelectorAll('.blog-single-card-item').forEach(function (card) {
        card.addEventListener('click', function (event) {
            if (event.target.closest('a, button, input, textarea, select, label')) {
                return;
            }

            const route = card.getAttribute('data-route');
            if (route) {
                window.location.href = route;
            }
        });
    });

    const bindClearSearch = function (triggerSelector, formSelector, inputSelector) {
        document.querySelectorAll(triggerSelector).forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                const form = document.querySelector(formSelector);
                const input = document.querySelector(inputSelector);

                if (!form || !input) {
                    return;
                }

                input.value = '';
                form.submit();
            });
        });
    };

    bindClearSearch('.clear-all-search', '#search-form', '#search');
    bindClearSearch('.clear-all-search-popular', '#popular-search-form', '#popular-search');

    document.querySelectorAll('.nisr-blog-shell .blog-top-nav').forEach(function (nav) {
        const wrapper = nav.parentElement;
        const previousButton = wrapper ? wrapper.querySelector('.previous-button button, .blog-top-nav_prev-btn button') : null;
        const nextButton = wrapper ? wrapper.querySelector('.next-button button, .blog-top-nav_next-btn button') : null;

        const scrollNav = function (direction) {
            nav.scrollBy({
                left: direction * 260,
                behavior: 'smooth',
            });
        };

        if (previousButton) {
            previousButton.addEventListener('click', function () {
                scrollNav(-1);
            });
        }

        if (nextButton) {
            nextButton.addEventListener('click', function () {
                scrollNav(1);
            });
        }
    });

    document.querySelectorAll('.article-nav-wrapper_collapse').forEach(function (toggle) {
        const navWrapper = document.querySelector('.article-nav-wrapper');
        const openIcon = toggle.querySelector('.open-icon');
        const closeIcon = toggle.querySelector('.close-icon');

        toggle.addEventListener('click', function () {
            if (!navWrapper) {
                return;
            }

            const isHidden = navWrapper.classList.contains('d-none');
            navWrapper.classList.toggle('d-none', !isHidden);
            navWrapper.classList.toggle('d-block', isHidden);

            if (openIcon) {
                openIcon.classList.toggle('d-none', isHidden);
            }

            if (closeIcon) {
                closeIcon.classList.toggle('d-none', !isHidden);
            }
        });

        navWrapper?.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth >= 992) {
                    return;
                }

                navWrapper.classList.add('d-none');
                navWrapper.classList.remove('d-block');

                if (openIcon) {
                    openIcon.classList.remove('d-none');
                }

                if (closeIcon) {
                    closeIcon.classList.add('d-none');
                }
            });
        });
    });
});
