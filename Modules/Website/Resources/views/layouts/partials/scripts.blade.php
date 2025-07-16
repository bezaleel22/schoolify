<!-- Core JavaScript Libraries -->
<script src="{{ asset('js/jquery.js') }}"></script>
<script src="{{ asset('js/bootstrap.min.js') }}"></script>
<script src="{{ asset('js/jquery.appear.js') }}"></script>
<script src="{{ asset('js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('js/shuffle.min.js') }}"></script>
<script src="{{ asset('js/nice-select.js') }}"></script>
<script src="{{ asset('js/lightcase.js') }}"></script>
<script src="{{ asset('js/jquery.datetimepicker.full.min.js') }}"></script>
<script src="{{ asset('js/circle-progress.js') }}"></script>
<script src="{{ asset('js/gmaps.js') }}"></script>

<!-- Revolution Slider -->
<script src="{{ asset('js/jquery.themepunch.tools.min.js') }}"></script>
<script src="{{ asset('js/jquery.themepunch.revolution.min.js') }}"></script>

<!-- Revolution Slider Extensions -->
<script src="{{ asset('js/extensions/revolution.extension.actions.min.js') }}"></script>
<script src="{{ asset('js/extensions/revolution.extension.carousel.min.js') }}"></script>
<script src="{{ asset('js/extensions/revolution.extension.kenburn.min.js') }}"></script>
<script src="{{ asset('js/extensions/revolution.extension.migration.min.js') }}"></script>
<script src="{{ asset('js/extensions/revolution.extension.navigation.min.js') }}"></script>
<script src="{{ asset('js/extensions/revolution.extension.parallax.min.js') }}"></script>
<script src="{{ asset('js/extensions/revolution.extension.slideanims.min.js') }}"></script>
<script src="{{ asset('js/extensions/revolution.extension.video.min.js') }}"></script>
<script src="{{ asset('js/extensions/revolution.extension.layeranimation.min.js') }}"></script>

<!-- Theme JavaScript -->
<script src="{{ asset('js/theme.js') }}"></script>

<!-- Laravel Mix JavaScript -->
<script src="{{ mix('js/website.js') }}"></script>

<!-- Google Maps API (Optional - uncomment if needed) -->
{{-- <script src="https://maps.google.com/maps/api/js?key=YOUR_API_KEY"></script> --}}

<!-- Google reCAPTCHA (Optional - uncomment if needed) -->
@if(config('services.recaptcha.site_key'))
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endif

<!-- WhatsApp Widget -->
<script>
    // WhatsApp floating button
    document.addEventListener('DOMContentLoaded', function() {
        var whatsappBtn = document.createElement('div');
        whatsappBtn.className = 'whatsapp-float';
        whatsappBtn.innerHTML = '<a href="https://wa.me/2348127823406" target="_blank"><img src="{{ asset("images/whatsapp.svg") }}" alt="WhatsApp"></a>';
        document.body.appendChild(whatsappBtn);
    });
</script>

<style>
    .whatsapp-float {
        position: fixed;
        width: 60px;
        height: 60px;
        bottom: 20px;
        right: 20px;
        background-color: #25d366;
        color: #FFF;
        border-radius: 50px;
        text-align: center;
        font-size: 30px;
        box-shadow: 2px 2px 3px #999;
        z-index: 1000;
        transition: all 0.3s;
    }
    
    .whatsapp-float:hover {
        transform: scale(1.1);
    }
    
    .whatsapp-float a {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        text-decoration: none;
    }
    
    .whatsapp-float img {
        width: 30px;
        height: 30px;
    }
</style>

<!-- Custom Scripts -->
<script>
    // Custom website functionality
    document.addEventListener('DOMContentLoaded', function() {
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Initialize popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
        
        // Lazy loading for images
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
        
        // Back to top button
        const backToTop = document.createElement('button');
        backToTop.className = 'back-to-top';
        backToTop.innerHTML = '<i class="twi-angle-up"></i>';
        backToTop.style.cssText = `
            position: fixed;
            bottom: 80px;
            right: 20px;
            width: 50px;
            height: 50px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 50%;
            display: none;
            cursor: pointer;
            z-index: 999;
            transition: all 0.3s;
        `;
        document.body.appendChild(backToTop);
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTop.style.display = 'block';
            } else {
                backToTop.style.display = 'none';
            }
        });
        
        backToTop.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        
        // Cookie consent (optional)
        if (!localStorage.getItem('cookieConsent')) {
            const cookieBanner = document.createElement('div');
            cookieBanner.className = 'cookie-banner';
            cookieBanner.innerHTML = `
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-md-9">
                            <p class="mb-0">We use cookies to enhance your browsing experience. By continuing to use our site, you agree to our use of cookies.</p>
                        </div>
                        <div class="col-md-3 text-end">
                            <button class="btn btn-primary btn-sm" onclick="acceptCookies()">Accept</button>
                        </div>
                    </div>
                </div>
            `;
            cookieBanner.style.cssText = `
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: rgba(0, 0, 0, 0.9);
                color: white;
                padding: 15px 0;
                z-index: 1001;
            `;
            document.body.appendChild(cookieBanner);
        }
    });
    
    function acceptCookies() {
        localStorage.setItem('cookieConsent', 'true');
        document.querySelector('.cookie-banner').remove();
    }
    
    // Performance optimization
    window.addEventListener('load', function() {
        // Preload critical images
        const criticalImages = [
            '{{ asset("images/logo2.png") }}',
            '{{ asset("images/slider/9.webp") }}'
        ];
        
        criticalImages.forEach(src => {
            const img = new Image();
            img.src = src;
        });
    });
</script>

@stack('scripts')