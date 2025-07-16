<div class="container largeContainer">
    <div class="row">
        <div class="col-lg-6">
            <div class="subTitle">
                <span class="bleft"></span>Discover The Leading Academy
            </div>
            <h2 class="secTitle">The Director</h2>
            <p class="secDesc">
                Welcome to Lighthouse Leading Academy, the epitome of Christian Education in Africa. I'm thrilled to share the news that you've made a wise decision by choosing to be a part of this institution. It truly reflects an informed and intelligent choice. The school boasts a rich blend of spiritual and academic heritage, offering abundant opportunities for the maximization of your potential.
            </p>
            <p>
                <a class="qu_btn" href="{{ route('website.about') }}">About Us</a>
            </p>
            <div class="row">
                <div class="col-md-5">
                    <div class="fact_01">
                        <h2>
                            <span class="counter completed" data-count="2500">2.5</span><i>k</i>
                        </h2>
                        <p>Successful Kids</p>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="fact_01">
                        <h2>
                            <span class="counter completed" data-count="95">95</span><i>%</i>
                        </h2>
                        <p>Happy Parents</p>
                    </div>
                </div>
            </div>
            
            <!-- Additional Statistics -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="fact_01">
                        <h2>
                            <span class="counter completed" data-count="15">15</span><i>+</i>
                        </h2>
                        <p>Years of Excellence</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="fact_01">
                        <h2>
                            <span class="counter completed" data-count="50">50</span><i>+</i>
                        </h2>
                        <p>Expert Teachers</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="absThumb">
                <img src="{{ asset('images/home3/1.png') }}" alt="Director" class="img-fluid" />
                
                <!-- Director's Signature -->
                <div class="director-signature">
                    <img src="{{ asset('images/home3/mama_sign.webp') }}" alt="Director's Signature" class="signature-img">
                    <div class="director-info">
                        <h5>Mrs. Excellence Akpehe</h5>
                        <p>Director, Lighthouse Leading Academy</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.absThumb {
    position: relative;
}

.director-signature {
    position: absolute;
    bottom: 20px;
    right: 20px;
    background: rgba(255, 255, 255, 0.95);
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    text-align: center;
    min-width: 200px;
}

.signature-img {
    max-width: 120px;
    height: auto;
    margin-bottom: 10px;
}

.director-info h5 {
    margin-bottom: 5px;
    color: #333;
    font-weight: 600;
}

.director-info p {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.fact_01 {
    text-align: center;
    margin-bottom: 20px;
    padding: 20px 10px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    transition: transform 0.3s;
}

.fact_01:hover {
    transform: translateY(-5px);
}

.fact_01 h2 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #007bff;
    margin-bottom: 10px;
}

.fact_01 p {
    color: #666;
    font-weight: 500;
    margin: 0;
}

@media (max-width: 768px) {
    .director-signature {
        position: static;
        margin-top: 20px;
        background: #f8f9fa;
    }
    
    .fact_01 h2 {
        font-size: 2rem;
    }
}
</style>