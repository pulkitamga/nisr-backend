document.addEventListener('DOMContentLoaded', function () {
    const slides = document.querySelectorAll('.banner-slide');
    const indicators = document.querySelectorAll('.carousel-indicators li');
    let currentIndex = 0;
    let slideInterval;

    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.style.opacity = i === index ? '1' : '0';
            slide.style.pointerEvents = i === index ? 'auto' : 'none';
        });

        indicators.forEach((ind, i) => {
            ind.classList.toggle('active', i === index);
        });

        currentIndex = index;
    }

    function startAutoSlide() {
        slideInterval = setInterval(() => {
            let nextIndex = (currentIndex + 1) % slides.length;
            showSlide(nextIndex);
        }, 5000);
    }

    function stopAutoSlide() {
        clearInterval(slideInterval);
    }

    // Indicator click to change slide
    indicators.forEach((indicator, idx) => {
        indicator.addEventListener('click', () => {
            stopAutoSlide();
            showSlide(idx);
            startAutoSlide();
        });
    });

    // Initialize first slide and start auto slide
    showSlide(0);
    startAutoSlide();


    var swiperOne = new Swiper(".mySwiperOne", {
        slidesPerView: 4,
        spaceBetween: 15,
        loop: true,
        autoplay: {
            delay: 2500,
            disableOnInteraction: false,
        },
        breakpoints: {
            0: {
                slidesPerView: 1
            },
            640: {
                slidesPerView: 2
            },
            1024: {
                slidesPerView: 5
            }
        }
    });

    const swiperTwo = new Swiper(".mySwiperTwo", {
        slidesPerView: 1,
        spaceBetween: 30,
        breakpoints: {
            768: {
                slidesPerView: 1
            },
            992: {
                slidesPerView: 2
            }
        },
        loop: true,
        autoplay: {
            delay: 3000,
        },
    });

    const swiperThree = new Swiper(".mySwiperThree", {
        slidesPerView: 4,
        spaceBetween: 15,
        breakpoints: {
            0: {
                slidesPerView: 1
            },
            640: {
                slidesPerView: 2
            },
            1024: {
                slidesPerView: 4
            }
        },
        loop: true,
        autoplay: {
            delay: 3000,
        },
    });


});



document.addEventListener('DOMContentLoaded', function () {
    const slides = document.querySelectorAll('.slide');
    let currentSlide = 0;

    setInterval(() => {
        slides[currentSlide].classList.remove('opacity-100');
        slides[currentSlide].classList.add('opacity-0');

        currentSlide = (currentSlide + 1) % slides.length;

        slides[currentSlide].classList.remove('opacity-0');
        slides[currentSlide].classList.add('opacity-100');
    }, 4000); // Change slide every 4 seconds
});

