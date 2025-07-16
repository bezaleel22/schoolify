<footer class="footer_01">
    <div class="container largeContainer">
        <div class="row">
            <div class="col-lg-7 col-md-6">
                <h2 class="secTitle">
                    Sign up for latest news and<br /> insights from
                    <span>LightHouse Leading Academy.</span>
                </h2>
            </div>
            <div class="col-lg-5 col-md-6">
                <div class="SubsrcribeForm">
                    <form class="newsletter-form" action="{{ route('newsletter.subscribe') }}" method="post">
                        @csrf
                        <input type="email" name="email" placeholder="Email Address" required />
                        <button class="yikes-easy-mc-submit-button" type="submit">
                            Subscribe
                        </button>
                    </form>
                    <div class="newsletter-message"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12"><div class="fdivider"></div></div>
        </div>
        <div class="row">
            <div class="col-md-6 col-lg-3">
                <div class="aboutWidget">
                    <h5>Do you have questions? Call or visit us.</h5>
                    <div class="phone">
                        <i class="twi-phone"></i>
                        <a href="tel:+2348127823406">+2348127823406</a>
                        <a href="tel:+2349169801738">+2349169801738</a>
                    </div>
                    <p>
                        No. 20 Genabe Zone, beside Shammah Plaza, Welfare Quarters Makurdi,
                        Benue State.
                    </p>
                    <a href="mailto:support@llacademy.ng">
                        <span>support@llacademy.ng</span>
                    </a>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="widget PL28">
                    <h3 class="widget_title">Useful Links</h3>
                    <ul class="menu">
                        <li><a href="{{ route('website.home') }}">Home</a></li>
                        <li><a href="{{ route('website.about') }}">About Us</a></li>
                        <li><a href="{{ route('website.admission') }}">Admission</a></li>
                        <li><a href="{{ route('website.contact') }}">Contact Us</a></li>
                        <li><a href="{{ route('website.privacy') }}">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="widget PL28">
                    <h3 class="widget_title">Admission</h3>
                    <ul class="menu">
                        <li><a href="{{ route('website.admission') }}">Admission Procedure</a></li>
                        <li><a href="{{ route('website.about') }}">About Academy</a></li>
                        <li><a href="{{ route('website.contact') }}">Contact Us</a></li>
                        <li><a href="{{ route('website.calendar') }}">Academic Calendar</a></li>
                        <li><a href="{{ route('website.requirements') }}">Requirements</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="widget PL28">
                    <h3 class="widget_title">Quick Access</h3>
                    <ul class="menu">
                        <li><a href="https://llacademy.ng/parent-dashboard" target="_blank">Student Portal</a></li>
                        <li><a href="{{ route('website.staff') }}">Our Staff</a></li>
                        <li><a href="{{ route('website.portfolio') }}">Portfolio</a></li>
                        <li><a href="{{ route('website.blog.index') }}">News & Updates</a></li>
                        <li><a href="{{ route('website.events.index') }}">Events</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Social Media Links -->
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="footer-social text-center">
                    <h4>Follow Us</h4>
                    <div class="social-links">
                        <a href="https://facebook.com/lighthouseacademy" target="_blank" rel="noopener" aria-label="Facebook">
                            <i class="twi-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/lighthouseacademy" target="_blank" rel="noopener" aria-label="Twitter">
                            <i class="twi-twitter"></i>
                        </a>
                        <a href="https://instagram.com/lighthouseacademy" target="_blank" rel="noopener" aria-label="Instagram">
                            <i class="twi-instagram"></i>
                        </a>
                        <a href="https://linkedin.com/company/lighthouseacademy" target="_blank" rel="noopener" aria-label="LinkedIn">
                            <i class="twi-linkedin"></i>
                        </a>
                        <a href="https://youtube.com/lighthouseacademy" target="_blank" rel="noopener" aria-label="YouTube">
                            <i class="twi-youtube"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<section class="fcopyright">
    <div class="container largeContainer">
        <div class="row">
            <div class="col-md-6">
                <p>© {{ date('Y') }} LightHouse Leading Academy. All Rights Reserved.</p>
            </div>
            <div class="col-md-6">
                <div class="copyMenu">
                    <ul>
                        <li><a href="{{ route('website.privacy') }}">Privacy</a></li>
                        <li><a href="{{ route('website.terms') }}">Terms</a></li>
                        <li><a href="{{ route('website.contact') }}">Contact</a></li>
                        <li><a href="{{ route('website.about') }}">About</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .footer-social {
        padding: 30px 0;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .footer-social h4 {
        color: white;
        margin-bottom: 20px;
        font-size: 18px;
    }
    
    .footer-social .social-links {
        display: flex;
        justify-content: center;
        gap: 15px;
    }
    
    .footer-social .social-links a {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        color: white;
        text-decoration: none;
        transition: all 0.3s;
        font-size: 18px;
    }
    
    .footer-social .social-links a:hover {
        background: #007bff;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
    }
    
    .phone a {
        color: inherit;
        text-decoration: none;
        display: block;
    }
    
    .phone a:hover {
        color: #007bff;
    }
    
    @media (max-width: 768px) {
        .footer-social .social-links {
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .footer-social .social-links a {
            width: 40px;
            height: 40px;
            font-size: 16px;
        }
    }
</style>