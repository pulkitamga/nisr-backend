"use strict";

updateFlashDealProgressBar();
setInterval(updateFlashDealProgressBar, 10000);

$(document).ready(function () {
    var directionFromSession = $("#direction-from-session").data("value");
    var isRtl = directionFromSession === "rtl";

    $(".flash-deal-slider").owlCarousel({
        loop: false,
        autoplay: true,
        autoplayTimeout: 3000,
        center: false,
        margin: 10,
        nav: true,
        navText:
            isRtl
                ? [
                      "<i class='czi-arrow-right'></i>",
                      "<i class='czi-arrow-left'></i>",
                  ]
                : [
                      "<i class='czi-arrow-left'></i>",
                      "<i class='czi-arrow-right'></i>",
                  ],
        dots: false,
        autoplayHoverPause: true,
        rtl: isRtl,
        ltr: !isRtl,
        responsive: {
            0: {
                items: 1.1,
            },
            360: {
                items: 1.2,
            },
            375: {
                items: 1.4,
            },
            480: {
                items: 1.8,
            },
            576: {
                items: 2,
            },
            768: {
                items: 3,
            },
            992: {
                items: 4,
            },
            1200: {
                items: 4,
            },
        },
    });

    $(".flash-deal-slider-mobile").owlCarousel({
        loop: true,
        autoplay: true,
        autoplayTimeout: 3000,
        center: true,
        margin: 10,
        nav: true,
        navText:
            isRtl
                ? [
                      "<i class='czi-arrow-right'></i>",
                      "<i class='czi-arrow-left'></i>",
                  ]
                : [
                      "<i class='czi-arrow-left'></i>",
                      "<i class='czi-arrow-right'></i>",
                  ],
        dots: false,
        autoplayHoverPause: true,
        rtl: isRtl,
        ltr: !isRtl,
        responsive: {
            0: {
                items: 1.1,
            },
            360: {
                items: 1.2,
            },
            375: {
                items: 1.4,
            },
            480: {
                items: 1.8,
            },
            576: {
                items: 2,
            },
            768: {
                items: 3,
            },
            992: {
                items: 4,
            },
            1200: {
                items: 4,
            },
        },
    });

    $("#featured_products_list").owlCarousel({
        loop: true,
        autoplay: true,
        autoplayTimeout: 3000,
        margin: 20,
        nav: true,
        navText:
            isRtl
                ? [
                      "<i class='czi-arrow-right'></i>",
                      "<i class='czi-arrow-left'></i>",
                  ]
                : [
                      "<i class='czi-arrow-left'></i>",
                      "<i class='czi-arrow-right'></i>",
                  ],
        dots: false,
        autoplayHoverPause: true,
        rtl: isRtl,
        ltr: !isRtl,
        responsive: {
            0: {
                items: 1,
            },
            360: {
                items: 1,
            },
            375: {
                items: 1,
            },
            540: {
                items: 2,
            },
            576: {
                items: 2,
            },
            768: {
                items: 3,
            },
            992: {
                items: 4,
            },
            1200: {
                items: 6,
            },
        },
    });

    $(".new-arrivals-product").owlCarousel({
        loop: true,
        autoplay: true,
        autoplayTimeout: 3000,
        margin: 20,
        nav: true,
        navText:
            isRtl
                ? [
                      "<i class='czi-arrow-right'></i>",
                      "<i class='czi-arrow-left'></i>",
                  ]
                : [
                      "<i class='czi-arrow-left'></i>",
                      "<i class='czi-arrow-right'></i>",
                  ],
        dots: false,
        autoplayHoverPause: true,
        rtl: isRtl,
        ltr: !isRtl,
        responsive: {
            0: {
                items: 1,
            },
            360: {
                items: 1.02,
            },
            375: {
                items: 1.02,
            },
            540: {
                items: 2,
            },
            576: {
                items: 2,
            },
            768: {
                items: 2,
            },
            992: {
                items: 2,
            },
            1200: {
                items: 4,
            },
            1400: {
                items: 4,
            },
        },
    });

    $(".category-wise-product-slider").each(function () {
        let loopEnable = $(this).data('loop')?.toString() === 'true';

        $(this).owlCarousel({
            loop: loopEnable,
            autoplay: true,
            autoplayTimeout: 3000,
            margin: 20,
            nav: true,
            navText:
                isRtl
                    ? [
                          "<i class='czi-arrow-right'></i>",
                          "<i class='czi-arrow-left'></i>",
                      ]
                    : [
                          "<i class='czi-arrow-left'></i>",
                          "<i class='czi-arrow-right'></i>",
                      ],
            dots: false,
            autoplayHoverPause: true,
            rtl: isRtl,
            ltr: !isRtl,
            responsive: {
                0: {
                    items: 1.2,
                },
                375: {
                    items: 1.4,
                },
                425: {
                    items: 2,
                },
                576: {
                    items: 3,
                },
                768: {
                    items: 4,
                },
                992: {
                    items: 5,
                },
                1200: {
                    items: 6,
                },
            },
            onInitialized: checkNavigationButtons,
        });
    });

    function checkNavigationButtons(event) {
        var itemCount = event.item.count;
        let owlNav = $(".owl-nav");
        itemCount > 1 ? owlNav.show() : owlNav.hide();
    }

    $(".hero-slider").owlCarousel({
        loop: true,
        autoplay: true,
        autoplayTimeout: 3000,
        margin: 20,
        nav: true,
        navText:
            isRtl
                ? [
                      "<i class='czi-arrow-right'></i>",
                      "<i class='czi-arrow-left'></i>",
                  ]
                : [
                      "<i class='czi-arrow-left'></i>",
                      "<i class='czi-arrow-right'></i>",
                  ],
        dots: true,
        autoplayHoverPause: true,
        autoplaySpeed: 1500,
        slideTransition: "linear",
        items: 1,
        rtl: isRtl,
        ltr: !isRtl,
    });

    function initMainBannerSlider() {
        var $slider = $(".main-banner-slider");

        if (!$slider.length) {
            return;
        }

        var $slides = $slider.find(".banner-slide");
        var $indicators = $slider
            .closest(".home-hero-panel")
            .find(".carousel-indicators li");

        if ($slides.length <= 1) {
            $slides.css({
                opacity: 1,
                zIndex: 1,
                pointerEvents: "auto",
            });
            $indicators.eq(0).addClass("active");
            return;
        }

        var activeIndex = 0;
        var autoplayDelay = 3000;
        var autoplayTimer = null;

        function renderSlide(nextIndex) {
            activeIndex = nextIndex;

            $slides.each(function (index) {
                var isActive = index === activeIndex;

                $(this).css({
                    opacity: isActive ? 1 : 0,
                    zIndex: isActive ? 1 : 0,
                    pointerEvents: isActive ? "auto" : "none",
                });

                $(this).attr("aria-hidden", isActive ? "false" : "true");
            });

            $indicators.removeClass("active");
            $indicators.eq(activeIndex).addClass("active");
        }

        function stopAutoplay() {
            if (autoplayTimer) {
                window.clearInterval(autoplayTimer);
                autoplayTimer = null;
            }
        }

        function startAutoplay() {
            stopAutoplay();
            autoplayTimer = window.setInterval(function () {
                renderSlide((activeIndex + 1) % $slides.length);
            }, autoplayDelay);
        }

        $indicators.on("click", function () {
            var nextIndex = Number($(this).data("slide-to"));

            if (!Number.isNaN(nextIndex)) {
                renderSlide(nextIndex);
                startAutoplay();
            }
        });

        $slider.on("mouseenter focusin", stopAutoplay);
        $slider.on("mouseleave focusout", startAutoplay);

        renderSlide(activeIndex);
        startAutoplay();
    }

    initMainBannerSlider();

    $(".brands-slider").owlCarousel({
        loop: false,
        autoplay: true,
        autoplayTimeout: 3000,
        margin: 10,
        nav: true,
        navText:
            isRtl
                ? [
                      "<i class='czi-arrow-right'></i>",
                      "<i class='czi-arrow-left'></i>",
                  ]
                : [
                      "<i class='czi-arrow-left'></i>",
                      "<i class='czi-arrow-right'></i>",
                  ],
        dots: false,
        rtl: isRtl,
        ltr: !isRtl,
        autoplayHoverPause: true,
        responsive: {
            0: {
                items: 4,
            },
            360: {
                items: 5,
            },
            576: {
                items: 6,
            },
            768: {
                items: 7,
            },
            992: {
                items: 9,
            },
            1200: {
                items: 11,
            },
            1400: {
                items: 12,
            },
        },
    });

    $(".footer-banner-slider").owlCarousel({
        loop: true,
        autoplay: true,
        autoplayTimeout: 3000,
        margin: 10,
        nav: false,
        rtl: isRtl,
        ltr: !isRtl,
        autoplayHoverPause: true,
        items: 1,
    });

    $("#category-slider, #top-seller-slider").owlCarousel({
        loop: false,
        autoplay: true,
        autoplayTimeout: 3000,
        margin: 20,
        nav: false,
        dots: true,
        autoplayHoverPause: true,
        rtl: isRtl,
        ltr: !isRtl,
        responsive: {
            0: {
                items: 2,
            },
            360: {
                items: 3,
            },
            375: {
                items: 3,
            },
            540: {
                items: 4,
            },
            576: {
                items: 5,
            },
            768: {
                items: 6,
            },
            992: {
                items: 8,
            },
            1200: {
                items: 10,
            },
            1400: {
                items: 11,
            },
        },
    });

    $(".categories--slider").owlCarousel({
        loop: false,
        autoplay: true,
        autoplayTimeout: 3000,
        margin: 20,
        nav: false,
        dots: false,
        autoplayHoverPause: true,
        rtl: isRtl,
        ltr: !isRtl,
        responsive: {
            0: {
                items: 3,
            },
            // 360: {
            //     items: 3.2,
            // },
            // 375: {
            //     items: 3.5,
            // },
            540: {
                items: 4,
            },
            576: {
                items: 5,
            },
            768: {
                items: 6,
            },
            992: {
                items: 8,
            },
            1200: {
                items: 10,
            },
            1400: {
                items: 11,
            },
        },
    });

    const othersStore = $(".others-store-slider").owlCarousel({
        responsiveClass: true,
        nav: false,
        dots: false,
        loop: true,
        autoplay: true,
        autoplayTimeout: 3000,
        autoplayHoverPause: true,
        smartSpeed: 600,
        rtl: isRtl,
        ltr: !isRtl,
        responsive: {
            0: {
                items: 1.3,
                margin: 10,
            },
            480: {
                items: 2,
                margin: 26,
            },
            768: {
                items: 2,
                margin: 26,
            },
            992: {
                items: 3,
                margin: 26,
            },
            1200: {
                items: 4,
                margin: 26,
            },
        },
    });

    $(".store-next").on("click", function () {
        othersStore.trigger("next.owl.carousel", [600]);
    });

    $(".store-prev").on("click", function () {
        othersStore.trigger("prev.owl.carousel", [600]);
    });
});
