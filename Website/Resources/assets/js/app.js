// Website Main JavaScript File
(function($) {
    'use strict';

    // Document ready
    $(document).ready(function() {
        
        // Initialize all components
        initializeComponents();
        
        // Handle preloader
        handlePreloader();
        
        // Initialize navigation
        initializeNavigation();
        
        // Initialize forms
        initializeForms();
        
        // Initialize animations
        initializeAnimations();
        
        // Initialize carousel/sliders
        initializeCarousels();
        
        // Initialize lightbox
        initializeLightbox();
        
        // Initialize counters
        initializeCounters();
        
        // Initialize smooth scroll
        initializeSmoothScroll();
    });

    // Window load
    $(window).on('load', function() {
        // Hide preloader
        $('.preloader').fadeOut('slow');
        
        // Initialize Revolution Slider if exists
        if (typeof jQuery.fn.revolution !== 'undefined') {
            initializeRevolutionSlider();
        }
    });

    // Preloader
    function handlePreloader() {
        if ($('.preloader').length > 0) {
            $(window).on('load', function() {
                $('.preloader').fadeOut(800);
            });
        }
    }

    // Initialize all components
    function initializeComponents() {
        // Sticky header
        if ($('.isSticky').length > 0) {
            $(window).on('scroll', function() {
                var scroll = $(window).scrollTop();
                if (scroll >= 150) {
                    $('.isSticky').addClass('sticky');
                } else {
                    $('.isSticky').removeClass('sticky');
                }
            });
        }

        // Back to top button
        if ($('.back-to-top').length > 0) {
            $(window).on('scroll', function() {
                if ($(this).scrollTop() > 200) {
                    $('.back-to-top').fadeIn();
                } else {
                    $('.back-to-top').fadeOut();
                }
            });

            $('.back-to-top').on('click', function(e) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: 0
                }, 800);
            });
        }
    }

    // Navigation
    function initializeNavigation() {
        // Mobile menu toggle
        $('.menuToggler').on('click', function(e) {
            e.preventDefault();
            $('.sidebarMenu').addClass('active');
            $('body').addClass('menu-open');
        });

        $('.SMACloser, .sidebarMenuOverlay').on('click', function(e) {
            e.preventDefault();
            $('.sidebarMenu').removeClass('active');
            $('body').removeClass('menu-open');
        });

        // Search toggle
        $('.searchBtn a').on('click', function(e) {
            e.preventDefault();
            $('.header01SearchBar').toggleClass('active');
        });

        // Sub menu toggle for mobile
        $('.menu-item-has-children > a').on('click', function(e) {
            if ($(window).width() < 992) {
                e.preventDefault();
                $(this).next('.sub-menu').slideToggle();
                $(this).toggleClass('active');
            }
        });
    }

    // Forms
    function initializeForms() {
        // Contact form
        $('#contact-form').on('submit', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var formData = new FormData(this);
            
            $.ajax({
                url: form.attr('action') || '/contact',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    form.find('.btn-submit').prop('disabled', true).text('Sending...');
                },
                success: function(response) {
                    form.find('.form-message').html('<div class="alert alert-success">Message sent successfully!</div>');
                    form[0].reset();
                },
                error: function() {
                    form.find('.form-message').html('<div class="alert alert-danger">Error sending message. Please try again.</div>');
                },
                complete: function() {
                    form.find('.btn-submit').prop('disabled', false).text('Send Message');
                }
            });
        });

        // Newsletter form
        $('.newsletter-form').on('submit', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var email = form.find('input[type="email"]').val();
            
            $.ajax({
                url: '/newsletter/subscribe',
                type: 'POST',
                data: {
                    email: email,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    form.find('button').prop('disabled', true).text('Subscribing...');
                },
                success: function(response) {
                    form.find('.newsletter-message').html('<div class="alert alert-success">Successfully subscribed!</div>');
                    form[0].reset();
                },
                error: function() {
                    form.find('.newsletter-message').html('<div class="alert alert-danger">Error subscribing. Please try again.</div>');
                },
                complete: function() {
                    form.find('button').prop('disabled', false).text('Subscribe');
                }
            });
        });
    }

    // Animations
    function initializeAnimations() {
        // Animate on scroll
        if (typeof $.fn.appear !== 'undefined') {
            $('.counter').appear(function() {
                var $this = $(this);
                var countTo = $this.data('count');
                
                $({ countNum: $this.text() }).animate({
                    countNum: countTo
                }, {
                    duration: 2000,
                    easing: 'linear',
                    step: function() {
                        $this.text(Math.floor(this.countNum));
                    },
                    complete: function() {
                        $this.text(this.countNum);
                    }
                });
            });
        }
    }

    // Carousels
    function initializeCarousels() {
        // Owl Carousel
        if (typeof $.fn.owlCarousel !== 'undefined') {
            // Team carousel
            $('.team-carousel').owlCarousel({
                loop: true,
                margin: 30,
                nav: true,
                dots: false,
                responsive: {
                    0: { items: 1 },
                    600: { items: 2 },
                    1000: { items: 3 }
                }
            });

            // Blog carousel
            $('.blog-carousel').owlCarousel({
                loop: true,
                margin: 30,
                nav: true,
                dots: false,
                responsive: {
                    0: { items: 1 },
                    600: { items: 2 },
                    1000: { items: 3 }
                }
            });

            // Portfolio carousel
            $('.portfolio-carousel').owlCarousel({
                loop: true,
                margin: 30,
                nav: true,
                dots: false,
                responsive: {
                    0: { items: 1 },
                    600: { items: 2 },
                    1000: { items: 4 }
                }
            });
        }
    }

    // Lightbox
    function initializeLightbox() {
        if (typeof $.fn.lightcase !== 'undefined') {
            $('[data-rel^="lightcase"]').lightcase();
        }
    }

    // Counters
    function initializeCounters() {
        if (typeof $.fn.appear !== 'undefined') {
            $('.fact_01 .counter').appear(function() {
                var $this = $(this);
                var countTo = $this.data('count');
                
                $({ countNum: $this.text() }).animate({
                    countNum: countTo
                }, {
                    duration: 2000,
                    easing: 'linear',
                    step: function() {
                        $this.text(Math.floor(this.countNum));
                    },
                    complete: function() {
                        $this.text(this.countNum);
                    }
                });
            });
        }
    }

    // Smooth scroll
    function initializeSmoothScroll() {
        $('a[href*="#"]').not('[href="#"]').not('[href="#0"]').on('click', function(e) {
            if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
                var target = $(this.hash);
                target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                if (target.length) {
                    e.preventDefault();
                    $('html, body').animate({
                        scrollTop: target.offset().top - 100
                    }, 800);
                }
            }
        });
    }

    // Revolution Slider
    function initializeRevolutionSlider() {
        if ($('#rev_slider_3').length > 0) {
            var revapi = $('#rev_slider_3').show().revolution({
                delay: 6000,
                responsiveLevels: [1200, 1140, 778, 480],
                gridwidth: [1220, 920, 700, 380],
                jsFileLocation: "/js/",
                sliderLayout: "fullscreen",
                navigation: {
                    keyboardNavigation: "off",
                    keyboard_direction: "horizontal",
                    mouseScrollNavigation: "off",
                    onHoverStop: "off",
                    bullets: {
                        enable: true,
                        style: "metis",
                        hide_onmobile: true,
                        hide_under: 700,
                        h_align: "right",
                        v_align: "bottom",
                        h_offset: 180,
                        hide_onleave: false,
                        v_offset: 60,
                        space: 15,
                        tmp: '<span class="tp-bullet-img-wrap"><span class="tp-bullet-image"></span></span>'
                    },
                    arrows: { enable: false }
                }
            });
        }
    }

    // Portfolio filter
    function initializePortfolioFilter() {
        if (typeof Shuffle !== 'undefined' && $('.portfolio-grid').length > 0) {
            var portfolioGrid = new Shuffle(document.querySelector('.portfolio-grid'), {
                itemSelector: '.portfolio-item',
                sizer: '.portfolio-item'
            });

            $('.portfolio-filter button').on('click', function() {
                var filterValue = $(this).data('filter');
                $('.portfolio-filter button').removeClass('active');
                $(this).addClass('active');
                portfolioGrid.filter(filterValue);
            });
        }
    }

    // Initialize portfolio filter
    initializePortfolioFilter();

    // Google Analytics 4
    function initializeGA4() {
        if (typeof gtag !== 'undefined') {
            gtag('config', 'GA_MEASUREMENT_ID', {
                page_title: document.title,
                page_location: window.location.href
            });
        }
    }

    // Call GA4 initialization
    initializeGA4();

})(jQuery);

// PWA Service Worker Registration
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js')
            .then(function(registration) {
                console.log('ServiceWorker registration successful');
            })
            .catch(function(error) {
                console.log('ServiceWorker registration failed: ', error);
            });
    });
}
