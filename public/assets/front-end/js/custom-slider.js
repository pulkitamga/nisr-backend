document.addEventListener("DOMContentLoaded", function () {
    if (typeof Swiper === "undefined") {
        return;
    }

    if (document.querySelector(".mySwiperOne")) {
        new Swiper(".mySwiperOne", {
            slidesPerView: 4,
            spaceBetween: 15,
            loop: true,
            autoplay: {
                delay: 2500,
                disableOnInteraction: false,
            },
            breakpoints: {
                0: {
                    slidesPerView: 1,
                },
                640: {
                    slidesPerView: 2,
                },
                1024: {
                    slidesPerView: 5,
                },
            },
        });
    }

    if (document.querySelector(".mySwiperTwo")) {
        new Swiper(".mySwiperTwo", {
            slidesPerView: 1,
            spaceBetween: 30,
            breakpoints: {
                768: {
                    slidesPerView: 1,
                },
                992: {
                    slidesPerView: 2,
                },
            },
            loop: true,
            autoplay: {
                delay: 3000,
            },
        });
    }

    if (document.querySelector(".mySwiperThree")) {
        new Swiper(".mySwiperThree", {
            slidesPerView: 4,
            spaceBetween: 15,
            breakpoints: {
                0: {
                    slidesPerView: 1,
                },
                640: {
                    slidesPerView: 2,
                },
                1024: {
                    slidesPerView: 4,
                },
            },
            loop: true,
            autoplay: {
                delay: 3000,
            },
        });
    }
});
