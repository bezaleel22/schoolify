<section class="sidebarMenu">
    <div class="sidebarMenuOverlay"></div>
    <div class="SMArea">
        <div class="SMAHeader">
            <h3>
                <i class="twi-bars1"></i> Menu
            </h3>
            <a href="javascript:void(0);" class="SMACloser">
                <i class="twi-times2"></i>
            </a>
        </div>
        <div class="SMABody">
            <ul>
                <li class="{{ request()->routeIs('website.home') ? 'current-menu-item' : '' }}">
                    <a href="{{ route('website.home') }}">Home</a>
                </li>
                <li class="{{ request()->routeIs('website.admission*') ? 'current-menu-item' : '' }}">
                    <a href="{{ route('website.admission') }}">Admission</a>
                </li>
                <li class="{{ request()->routeIs('website.portfolio*') ? 'current-menu-item' : '' }}">
                    <a href="{{ route('website.portfolio') }}">Portfolio</a>
                </li>
                <li class="{{ request()->routeIs('website.blog*') ? 'current-menu-item' : '' }}">
                    <a href="{{ route('website.blog.index') }}">Blog</a>
                </li>
                <li class="{{ request()->routeIs('website.about*') ? 'current-menu-item' : '' }}">
                    <a href="{{ route('website.about') }}">About Us</a>
                </li>
                <li class="{{ request()->routeIs('website.contact*') ? 'current-menu-item' : '' }}">
                    <a href="{{ route('website.contact') }}">Contact</a>
                </li>
            </ul>
            
            <div class="mobile-portal-btn">
                <a href="https://llacademy.ng/parent-dashboard" target="_blank" class="qu_btn mx-auto">
                    Student Portal
                </a>
            </div>
            
            <div class="mobile-contact-info">
                <div class="contact-item">
                    <i class="twi-phone"></i>
                    <div>
                        <p>Call Us</p>
                        <a href="tel:+2348127823406">+2348127823406</a><br>
                        <a href="tel:+2349169801738">+2349169801738</a>
                    </div>
                </div>
                
                <div class="contact-item">
                    <i class="twi-envelope"></i>
                    <div>
                        <p>Email Us</p>
                        <a href="mailto:support@llacademy.ng">support@llacademy.ng</a>
                    </div>
                </div>
                
                <div class="contact-item">
                    <i class="twi-map-marker"></i>
                    <div>
                        <p>Visit Us</p>
                        <span>No. 20 Genabe Zone, beside Shammah Plaza, Welfare Quarters Makurdi, Benue State.</span>
                    </div>
                </div>
            </div>
            
            <div class="mobile-social">
                <h5>Follow Us</h5>
                <div class="social-links">
                    <a href="https://facebook.com/lighthouseacademy" target="_blank" rel="noopener">
                        <i class="twi-facebook-f"></i>
                    </a>
                    <a href="https://twitter.com/lighthouseacademy" target="_blank" rel="noopener">
                        <i class="twi-twitter"></i>
                    </a>
                    <a href="https://instagram.com/lighthouseacademy" target="_blank" rel="noopener">
                        <i class="twi-instagram"></i>
                    </a>
                    <a href="https://linkedin.com/company/lighthouseacademy" target="_blank" rel="noopener">
                        <i class="twi-linkedin"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .mobile-portal-btn {
        padding: 20px;
        text-align: center;
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
        margin: 20px 0;
    }
    
    .mobile-contact-info {
        padding: 20px;
    }
    
    .contact-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 15px;
        gap: 10px;
    }
    
    .contact-item i {
        font-size: 18px;
        color: #007bff;
        margin-top: 2px;
    }
    
    .contact-item p {
        margin: 0 0 5px;
        font-weight: 600;
        color: #333;
    }
    
    .contact-item a {
        color: #666;
        text-decoration: none;
    }
    
    .contact-item a:hover {
        color: #007bff;
    }
    
    .contact-item span {
        color: #666;
        font-size: 14px;
        line-height: 1.4;
    }
    
    .mobile-social {
        padding: 20px;
        border-top: 1px solid #eee;
    }
    
    .mobile-social h5 {
        margin-bottom: 15px;
        color: #333;
    }
    
    .social-links {
        display: flex;
        gap: 15px;
    }
    
    .social-links a {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        border-radius: 50%;
        color: #333;
        text-decoration: none;
        transition: all 0.3s;
    }
    
    .social-links a:hover {
        background: #007bff;
        color: white;
        transform: translateY(-2px);
    }
</style>