<div class="container largeContainer">
    <div class="row">
        <div class="col-lg-12 text-center">
            <div class="subTitle">
                <span class="bleft"></span>
                Our Educational Services
                <span class="bright"></span>
            </div>
            <h2 class="secTitle">What We <span>Offer</span></h2>
            <p class="secDesc">
                Discover our comprehensive educational programs designed to nurture young minds and build strong foundations for future success.
            </p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="service-item">
                <div class="service-icon">
                    <i class="twi-baby"></i>
                </div>
                <div class="service-content">
                    <h4>Crèche & Daycare</h4>
                    <p>Early childhood education and care for infants, toddlers, and pre-schoolers in a safe, nurturing environment.</p>
                    <ul class="service-features">
                        <li>Ages 6 months - 3 years</li>
                        <li>Qualified caregivers</li>
                        <li>Developmental activities</li>
                        <li>Nutritious meals</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="service-item">
                <div class="service-icon">
                    <i class="twi-graduation-cap"></i>
                </div>
                <div class="service-content">
                    <h4>Preparatory School</h4>
                    <p>Foundation education that prepares children for primary school with emphasis on basic skills and social development.</p>
                    <ul class="service-features">
                        <li>Ages 3 - 5 years</li>
                        <li>Pre-literacy & numeracy</li>
                        <li>Creative activities</li>
                        <li>Social skills development</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="service-item">
                <div class="service-icon">
                    <i class="twi-book"></i>
                </div>
                <div class="service-content">
                    <h4>Primary Education</h4>
                    <p>Comprehensive primary education following the Nigerian curriculum with Christian values integration.</p>
                    <ul class="service-features">
                        <li>Primary 1 - 6</li>
                        <li>Nigerian curriculum</li>
                        <li>Christian education</li>
                        <li>Extracurricular activities</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="service-item">
                <div class="service-icon">
                    <i class="twi-users"></i>
                </div>
                <div class="service-content">
                    <h4>After School Programs</h4>
                    <p>Extended learning opportunities including homework assistance, skill development, and recreational activities.</p>
                    <ul class="service-features">
                        <li>Homework supervision</li>
                        <li>Skills development</li>
                        <li>Sports activities</li>
                        <li>Safe environment</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="service-item">
                <div class="service-icon">
                    <i class="twi-heart"></i>
                </div>
                <div class="service-content">
                    <h4>Character Development</h4>
                    <p>Holistic character formation based on Christian principles, building integrity, respect, and leadership qualities.</p>
                    <ul class="service-features">
                        <li>Christian values</li>
                        <li>Leadership training</li>
                        <li>Community service</li>
                        <li>Moral education</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="service-item">
                <div class="service-icon">
                    <i class="twi-star"></i>
                </div>
                <div class="service-content">
                    <h4>Special Programs</h4>
                    <p>Enrichment programs including music, arts, science clubs, and talent development opportunities.</p>
                    <ul class="service-features">
                        <li>Music & Arts</li>
                        <li>Science club</li>
                        <li>Talent development</li>
                        <li>Competition prep</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-12 text-center">
            <div class="service-cta">
                <h3>Ready to Enroll Your Child?</h3>
                <p>Join our community of learners and give your child the best educational foundation.</p>
                <a href="{{ route('website.admission') }}" class="qu_btn">Start Application</a>
                <a href="{{ route('website.contact') }}" class="qu_btn qu_btn_outline">Schedule Visit</a>
            </div>
        </div>
    </div>
</div>

<style>
.service-item {
    background: white;
    padding: 40px 30px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    text-align: center;
    height: 100%;
    transition: transform 0.3s, box-shadow 0.3s;
    border: 1px solid #f0f0f0;
}

.service-item:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
}

.service-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(45deg, #007bff, #0056b3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    position: relative;
}

.service-icon::before {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, #007bff, #0056b3);
    border-radius: 50%;
    opacity: 0.2;
    transform: scale(1.2);
    z-index: -1;
}

.service-icon i {
    font-size: 35px;
    color: white;
}

.service-content h4 {
    margin-bottom: 15px;
    color: #333;
    font-size: 20px;
    font-weight: 600;
}

.service-content p {
    color: #666;
    line-height: 1.6;
    margin-bottom: 20px;
}

.service-features {
    list-style: none;
    padding: 0;
    margin: 0;
    text-align: left;
}

.service-features li {
    padding: 8px 0;
    color: #555;
    border-bottom: 1px solid #f0f0f0;
    position: relative;
    padding-left: 20px;
}

.service-features li:last-child {
    border-bottom: none;
}

.service-features li::before {
    content: '✓';
    position: absolute;
    left: 0;
    color: #28a745;
    font-weight: bold;
}

.service-cta {
    background: linear-gradient(135deg, #007bff, #0056b3);
    padding: 60px 40px;
    border-radius: 20px;
    color: white;
    margin-top: 40px;
}

.service-cta h3 {
    font-size: 28px;
    margin-bottom: 15px;
    color: white;
}

.service-cta p {
    font-size: 16px;
    margin-bottom: 30px;
    opacity: 0.9;
}

.service-cta .qu_btn {
    margin: 0 10px 10px;
}

.qu_btn_outline {
    background: transparent;
    border: 2px solid white;
    color: white;
}

.qu_btn_outline:hover {
    background: white;
    color: #007bff;
}

@media (max-width: 768px) {
    .service-item {
        padding: 30px 20px;
        margin-bottom: 20px;
    }
    
    .service-icon {
        width: 60px;
        height: 60px;
        margin-bottom: 20px;
    }
    
    .service-icon i {
        font-size: 25px;
    }
    
    .service-content h4 {
        font-size: 18px;
    }
    
    .service-cta {
        padding: 40px 20px;
    }
    
    .service-cta h3 {
        font-size: 24px;
    }
}
</style>