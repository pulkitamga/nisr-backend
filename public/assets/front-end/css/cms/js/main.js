(function($) {
    "use strict";

    $(window).on("load", function() {
        $(".preloader").fadeOut();
        isotopeintial(); // Make sure this function is defined elsewhere
    });

    // Use event delegation to bind click on .preloaderCls
    $(document).on("click", ".preloaderCls", function(e) {
        e.preventDefault();
        $(".preloader").hide(); // .hide() is a bit cleaner than .css("display", "none")
    });


    function onePageNav(element) {
        if ($(element).length > 0) {
            $(document).on("click", element + " a", function(e) {
                var target = $($(this).attr("href"));
                if (target.length) {
                    e.preventDefault();
                    $("html, body").stop().animate({
                            scrollTop: target.offset().top - 10,
                        },
                        1000
                    );
                }
            });
        }
    }

    onePageNav(".onepage-nav");


    $(".mobile-menu-active").vsmobilemenu({
        menuContainer: ".th-mobile-menu",
        expandScreenWidth: 992,
        menuToggleBtn: ".th-menu-toggle",
    });

    /*---------- 04. Sticky fix ----------*/
    $(window).scroll(function() {
        var topPos = $(this).scrollTop();
        if (topPos > 500) {
            $('.th-header').addClass('sticky');
        } else {
            $('.th-header').removeClass('sticky')
        }
    })

    /*---------- 05. Scroll To Top ----------*/
    // progressAvtivation
    if ($('.scroll-top')) {

        var scrollTopbtn = document.querySelector('.scroll-top');
        var progressPath = document.querySelector('.scroll-top path');
        var pathLength = progressPath.getTotalLength();
        progressPath.style.transition = progressPath.style.WebkitTransition = 'none';
        progressPath.style.strokeDasharray = pathLength + ' ' + pathLength;
        progressPath.style.strokeDashoffset = pathLength;
        progressPath.getBoundingClientRect();
        progressPath.style.transition = progressPath.style.WebkitTransition = 'stroke-dashoffset 10ms linear';
        var updateProgress = function() {
            var scroll = $(window).scrollTop();
            var height = $(document).height() - $(window).height();
            var progress = pathLength - (scroll * pathLength / height);
            progressPath.style.strokeDashoffset = progress;
        }
        updateProgress();
        $(window).scroll(updateProgress);
        var offset = 50;
        var duration = 750;
        jQuery(window).on('scroll', function() {
            if (jQuery(this).scrollTop() > offset) {
                jQuery(scrollTopbtn).addClass('show');
            } else {
                jQuery(scrollTopbtn).removeClass('show');
            }
        });
        jQuery(scrollTopbtn).on('click', function(event) {
            event.preventDefault();
            jQuery('html, body').animate({
                scrollTop: 0
            }, duration);
            return false;
        })
    }

    /*---------- Background ----------*/

    if ($("[data-bg-src]").length > 0) {
        $("[data-bg-src]").each(function() {
            var src = $(this).attr("data-bg-src");
            $(this).css("background-image", "url(" + src + ")");
            $(this).removeAttr("data-bg-src").addClass("background-image");
        });
    }

    if ($('[data-mask-src]').length > 0) {
        $('[data-mask-src]').each(function() {
            var mask = $(this).attr('data-mask-src');
            $(this).css({
                'mask-image': 'url(' + mask + ')',
                '-webkit-mask-image': 'url(' + mask + ')'
            });
            $(this).addClass('bg-mask');
            $(this).removeAttr('data-mask-src');
        });
    };

    function isotopeintial() {
        // Isotope initialization
        var $isotope = $(".filter-active").isotope({
            filter: "*",
            animationOptions: {
                duration: 750,
                easing: "linear",
                queue: false,
            },
        });

        // Isotope filter
        $(".filter-menu").on("click", "button", function() {
            var $this = $(this);
            $(".filter-menu").find("button").removeClass("active");
            $this.addClass("active");
            var selector = $this.attr("data-filter");
            $isotope.isotope({
                filter: selector,
                animationOptions: {
                    duration: 750,
                    easing: "linear",
                    queue: false,
                },
            });
            return false;
        });
    }

    /*----------- 07. Global Slider ----------*/

    $('.th-slider').each(function() {
        var thSlider = $(this);
        var settings = $(this).data('slider-options');

        // Store references to the navigation Slider
        var prevArrow = thSlider.find('.slider-prev');
        var nextArrow = thSlider.find('.slider-next');
        var paginationElN = thSlider.find('.slider-pagination.pagi-number');
        var paginationExternel = thSlider.siblings('.slider-controller').find('.slider-pagination');

        var paginationEl = paginationExternel.length ? paginationExternel.get(0) : thSlider.find('.slider-pagination').get(0);

        var paginationType = settings['paginationType'] ? settings['paginationType'] : 'bullets';
        var autoplayconditon = settings['autoplay'];

        var sliderDefault = {
            slidesPerView: 1,
            spaceBetween: settings['spaceBetween'] || 24,
            loop: settings['loop'] !== false,
            speed: settings['speed'] || 1000,
            autoplay: autoplayconditon || {
                delay: 6000,
                disableOnInteraction: false
            },
            navigation: {
                nextEl: nextArrow.get(0),
                prevEl: prevArrow.get(0),
            },
            pagination: {
                el: paginationEl,
                type: paginationType,
                clickable: true,
                renderBullet: function(index, className) {
                    var number = index + 1;
                    var formattedNumber = number < 10 ? '0' + number : number;
                    if (paginationElN.length) {
                        return '<span class="' + className + ' number">' + formattedNumber + '</span>';
                    } else {
                        return '<span class="' + className + '" aria-label="Go to Slide ' + formattedNumber + '"></span>';
                    }
                },
                formatFractionCurrent: function(number) {
                    return number < 10 ? '0' + number : number;
                },
                formatFractionTotal: function(number) {
                    return number < 10 ? '0' + number : number;
                }
            },
            on: {
                slideChange: function() {
                    setTimeout(function() {
                        swiper.params.mousewheel.releaseOnEdges = false;
                    }, 500);
                },
                reachEnd: function() {
                    setTimeout(function() {
                        swiper.params.mousewheel.releaseOnEdges = true;
                    }, 750);
                }
            }
        };

        var options = JSON.parse(thSlider.attr('data-slider-options'));
        options = $.extend({}, sliderDefault, options);
        var swiper = new Swiper(thSlider.get(0), options); // Assign the swiper variable

        if ($('.slider-area').length > 0) {
            $('.slider-area').closest(".container").parent().addClass("arrow-wrap");
        }
    });

    $(document).on('click', '[data-slider-prev], [data-slider-next]', function() {
        var sliderSelector = $(this).data('slider-prev') || $(this).data('slider-next');
        var targetSlider = $(sliderSelector);

        if (targetSlider.length) {
            var swiper = targetSlider[0].swiper;

            if (swiper) {
                if ($(this).data('slider-prev') !== undefined) {
                    swiper.slidePrev();
                } else {
                    swiper.slideNext();
                }
            }
        }
    });


    // Call On Load
    if ($(".responsive-tab").length) {
        $(".responsive-tab").asTab({
            sliderTab: true,
            tabButton: ".tab-btn",
        });
    }

    window.addEventListener('contextmenu', function(e) {
        // do something here...
        e.preventDefault();
    }, false);

    document.onkeydown = function(e) {
        if (event.keyCode == 123) {
            return false;
        }
        if (e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) {
            return false;
        }
        if (e.ctrlKey && e.shiftKey && e.keyCode == 'C'.charCodeAt(0)) {
            return false;
        }
        if (e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) {
            return false;
        }
        if (e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) {
            return false;
        }
    }

})(jQuery);