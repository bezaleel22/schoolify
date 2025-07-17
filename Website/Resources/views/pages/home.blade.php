@extends('website::layouts.app')

@section('title', 'Home - Lighthouse Leading Academy')
@section('meta_description', 'Welcome to Lighthouse Leading Academy - A premier Christian educational institution providing world-class education in Makurdi, Benue State, Nigeria.')

@section('content')
<!-- Hero Slider Section -->
<section class="slider_03">
    @include('website::components.hero.slider')
</section>

<!-- About Section -->
<section class="aboutSection03">
    @include('website::components.content.about-section')
</section>

<!-- Services Section -->
<section class="serviceSection03">
    @include('website::components.content.services-section')
</section>

<!-- Sponsorship Section -->
<section id="sponsor" class="chooseSection chooseSection02">
    <div class="container largeContainer">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="subTitle">
                    <span class="bleft"></span>
                    Our Partners & Sponsors
                    <span class="bright"></span>
                </div>
                <h2 class="secTitle">Trusted <span>Partners</span></h2>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="partner-logo text-center">
                    <img src="{{ asset('images/client-logo/1.png') }}" alt="Partner 1" class="img-fluid">
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="partner-logo text-center">
                    <img src="{{ asset('images/client-logo/2.png') }}" alt="Partner 2" class="img-fluid">
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="partner-logo text-center">
                    <img src="{{ asset('images/client-logo/3.png') }}" alt="Partner 3" class="img-fluid">
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="partner-logo text-center">
                    <img src="{{ asset('images/client-logo/4.png') }}" alt="Partner 4" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Partnership/Application Section -->
<section class="appoinmentSection03">
    <div class="container largeContainer">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="appointment-content">
                    <div class="subTitle">
                        <span class="bleft"></span>
                        Join Our Academy
                    </div>
                    <h2 class="secTitle">Ready to Give Your Child the Best Education?</h2>
                    <p>
                        At Lighthouse Leading Academy, we provide a nurturing environment where your child can grow academically, spiritually, and socially. Our experienced faculty and state-of-the-art facilities ensure your child receives world-class education.
                    </p>
                    <ul class="benefits-list">
                        <li><i class="twi-check"></i> World-class curriculum</li>
                        <li><i class="twi-check"></i> Experienced and caring faculty</li>
                        <li><i class="twi-check"></i> Modern facilities and equipment</li>
                        <li><i class="twi-check"></i> Christian values integration</li>
                        <li><i class="twi-check"></i> Small class sizes for personalized attention</li>
                    </ul>
                    <div class="appointment-actions">
                        <a href="{{ route('website.admission') }}" class="qu_btn">Apply Now</a>
                        <a href="{{ route('website.contact') }}" class="qu_btn qu_btn_outline">Schedule Visit</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="appointment-image">
                    <img src="{{ asset('images/home3/1.webp') }}" alt="Students" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="teamSection02">
    <div class="container largeContainer">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="subTitle">
                    <span class="bleft"></span>
                    Meet Our Exceptional Instructors
                    <span class="bright"></span>
                </div>
                <h2 class="secTitle">Expert <span>Team</span></h2>
            </div>
        </div>
        
        @if(isset($staff) && $staff->count() > 0)
            <div class="row">
                @foreach($staff->take(4) as $member)
                    <div class="col-lg-3 col-md-6 mb-4">
                        @include('website::components.content.staff-card', ['staff' => $member])
                    </div>
                @endforeach
            </div>
            
            <div class="row">
                <div class="col-lg-12 text-center">
                    <a href="{{ route('website.staff') }}" class="qu_btn">View All Staff</a>
                </div>
            </div>
        @endif
    </div>
</section>

<!-- Call to Action Section -->
<section class="ctaSection">
    <div class="container largeContainer">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="cta-content">
                    <h2>Ready to Start Your Child's Journey?</h2>
                    <p>Join the Lighthouse Leading Academy family and give your child the foundation for success.</p>
                    <div class="cta-actions">
                        <a href="{{ route('website.admission') }}" class="qu_btn">Start Application</a>
                        <a href="tel:+2348127823406" class="qu_btn qu_btn_outline">Call Us Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="chooseSection03">
    <div class="container largeContainer">
        <div class="row">
            <div class="col-lg-6">
                <div class="choose-content">
                    <div class="subTitle">
                        <span class="bleft"></span>
                        Why Choose Lighthouse Academy
                    </div>
                    <h2 class="secTitle">Building Tomorrow's <span>Leaders</span></h2>
                    
                    <div class="choose-item">
                        <div class="choose-icon">
                            <i class="twi-graduation-cap"></i>
                        </div>
                        <div class="choose-text">
                            <h4>Academic Excellence</h4>
                            <p>Our rigorous curriculum prepares students for success in higher education and beyond.</p>
                        </div>
                    </div>
                    
                    <div class="choose-item">
                        <div class="choose-icon">
                            <i class="twi-heart"></i>
                        </div>
                        <div class="choose-text">
                            <h4>Christian Values</h4>
                            <p>We integrate Christian principles into every aspect of education and character development.</p>
                        </div>
                    </div>
                    
                    <div class="choose-item">
                        <div class="choose-icon">
                            <i class="twi-users"></i>
                        </div>
                        <div class="choose-text">
                            <h4>Caring Community</h4>
                            <p>Our supportive environment fosters growth, friendship, and lifelong learning.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="choose-image">
                    <img src="{{ asset('images/about/1.jpg') }}" alt="Why Choose Us" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Portfolio Section -->
<section class="portfolioSection03">
    <div class="container largeContainer">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="subTitle">
                    <span class="bleft"></span>
                    Our Gallery
                    <span class="bright"></span>
                </div>
                <h2 class="secTitle">School <span>Portfolio</span></h2>
            </div>
        </div>
        
        @if(isset($portfolioImages) && $portfolioImages->count() > 0)
            <div class="row">
                @foreach($portfolioImages->take(8) as $image)
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="portfolio-item">
                            <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $image->title }}" class="img-fluid">
                            <div class="portfolio-overlay">
                                <a href="{{ asset('storage/' . $image->image_path) }}" data-rel="lightcase:gallery" class="portfolio-link">
                                    <i class="twi-plus"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="row">
                <div class="col-lg-12 text-center">
                    <a href="{{ route('website.portfolio') }}" class="qu_btn">View All Gallery</a>
                </div>
            </div>
        @endif
    </div>
</section>

<!-- Blog Section -->
<section class="blogSectiont03">
    <div class="container largeContainer">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="subTitle">
                    <span class="bleft"></span>
                    Latest News & Updates
                    <span class="bright"></span>
                </div>
                <h2 class="secTitle">Recent <span>Blog Posts</span></h2>
            </div>
        </div>
        
        @if(isset($blogs) && $blogs->count() > 0)
            <div class="row">
                @foreach($blogs->take(3) as $blog)
                    <div class="col-lg-4 col-md-6 mb-4">
                        @include('website::components.content.blog-card', ['blog' => $blog])
                    </div>
                @endforeach
            </div>
            
            <div class="row">
                <div class="col-lg-12 text-center">
                    <a href="{{ route('website.blog.index') }}" class="qu_btn">Read More Posts</a>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
// Additional home page specific scripts
document.addEventListener('DOMContentLoaded', function() {
    // Initialize counters if visible
    if (document.querySelector('.counter')) {
        // Counter animation will be handled by the main app.js
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});
</script>
@endpush

<style>
.benefits-list {
    list-style: none;
    padding: 0;
    margin: 20px 0;
}

.benefits-list li {
    padding: 8px 0;
    color: #666;
}

.benefits-list i {
    color: #28a745;
    margin-right: 10px;
}

.appointment-actions {
    margin-top: 30px;
}

.appointment-actions .qu_btn {
    margin-right: 15px;
    margin-bottom: 10px;
}

.qu_btn_outline {
    background: transparent;
    border: 2px solid #007bff;
    color: #007bff;
}

.qu_btn_outline:hover {
    background: #007bff;
    color: white;
}

.choose-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 30px;
}

.choose-icon {
    width: 60px;
    height: 60px;
    background: #007bff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
    flex-shrink: 0;
}

.choose-icon i {
    font-size: 24px;
    color: white;
}

.choose-text h4 {
    margin-bottom: 10px;
    color: #333;
}

.choose-text p {
    color: #666;
    margin: 0;
}

.portfolio-item {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
}

.portfolio-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 123, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
}

.portfolio-item:hover .portfolio-overlay {
    opacity: 1;
}

.portfolio-link {
    width: 50px;
    height: 50px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #007bff;
    text-decoration: none;
}

.cta-content {
    padding: 80px 0;
    color: white;
}

.cta-content h2 {
    font-size: 3rem;
    margin-bottom: 20px;
}

.cta-content p {
    font-size: 1.2rem;
    margin-bottom: 30px;
}

.cta-actions .qu_btn {
    margin: 0 10px 10px;
}

.partner-logo {
    padding: 20px;
    transition: transform 0.3s;
}

.partner-logo:hover {
    transform: scale(1.05);
}

.partner-logo img {
    max-height: 100px;
    opacity: 0.7;
    transition: opacity 0.3s;
}

.partner-logo:hover img {
    opacity: 1;
}
</style>