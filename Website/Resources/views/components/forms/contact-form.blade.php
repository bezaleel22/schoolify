<div class="contact-form-wrapper">
    <div class="contact-form-header">
        <h3>Get in Touch</h3>
        <p>Send us a message and we'll get back to you as soon as possible.</p>
    </div>
    
    <form id="contact-form" action="{{ route('website.contact.submit') }}" method="POST" class="contact-form">
        @csrf
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="first_name">First Name <span class="required">*</span></label>
                    <input type="text" id="first_name" name="first_name" class="form-control" required value="{{ old('first_name') }}">
                    @error('first_name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="last_name">Last Name <span class="required">*</span></label>
                    <input type="text" id="last_name" name="last_name" class="form-control" required value="{{ old('last_name') }}">
                    @error('last_name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" required value="{{ old('email') }}">
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="{{ old('phone') }}">
                    @error('phone')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="subject">Subject <span class="required">*</span></label>
            <select id="subject" name="subject" class="form-control" required>
                <option value="">Select a subject</option>
                <option value="admission" {{ old('subject') == 'admission' ? 'selected' : '' }}>Admission Inquiry</option>
                <option value="general" {{ old('subject') == 'general' ? 'selected' : '' }}>General Information</option>
                <option value="visit" {{ old('subject') == 'visit' ? 'selected' : '' }}>Schedule a Visit</option>
                <option value="curriculum" {{ old('subject') == 'curriculum' ? 'selected' : '' }}>Curriculum Questions</option>
                <option value="fees" {{ old('subject') == 'fees' ? 'selected' : '' }}>Fees and Payment</option>
                <option value="other" {{ old('subject') == 'other' ? 'selected' : '' }}>Other</option>
            </select>
            @error('subject')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="message">Message <span class="required">*</span></label>
            <textarea id="message" name="message" class="form-control" rows="5" required placeholder="Tell us more about your inquiry...">{{ old('message') }}</textarea>
            @error('message')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>
        
        <!-- Child Information (Optional) -->
        <div class="child-info-section">
            <h4>Child Information (Optional)</h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="child_name">Child's Name</label>
                        <input type="text" id="child_name" name="child_name" class="form-control" value="{{ old('child_name') }}">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="child_age">Child's Age</label>
                        <select id="child_age" name="child_age" class="form-control">
                            <option value="">Select age</option>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ old('child_age') == $i ? 'selected' : '' }}>{{ $i }} year{{ $i > 1 ? 's' : '' }} old</option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="program_interest">Program of Interest</label>
                <select id="program_interest" name="program_interest" class="form-control">
                    <option value="">Select a program</option>
                    <option value="creche" {{ old('program_interest') == 'creche' ? 'selected' : '' }}>Crèche & Daycare</option>
                    <option value="preparatory" {{ old('program_interest') == 'preparatory' ? 'selected' : '' }}>Preparatory School</option>
                    <option value="primary" {{ old('program_interest') == 'primary' ? 'selected' : '' }}>Primary Education</option>
                    <option value="after_school" {{ old('program_interest') == 'after_school' ? 'selected' : '' }}>After School Programs</option>
                </select>
            </div>
        </div>
        
        <!-- Google reCAPTCHA -->
        @if(config('services.recaptcha.site_key'))
        <div class="form-group">
            <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
            @error('g-recaptcha-response')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>
        @endif
        
        <!-- Privacy Policy Agreement -->
        <div class="form-group">
            <div class="form-check">
                <input type="checkbox" id="privacy_agreement" name="privacy_agreement" class="form-check-input" required {{ old('privacy_agreement') ? 'checked' : '' }}>
                <label for="privacy_agreement" class="form-check-label">
                    I agree to the <a href="{{ route('website.privacy') }}" target="_blank">Privacy Policy</a> and consent to the processing of my personal data. <span class="required">*</span>
                </label>
                @error('privacy_agreement')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
        </div>
        
        <!-- Newsletter Subscription -->
        <div class="form-group">
            <div class="form-check">
                <input type="checkbox" id="newsletter_subscription" name="newsletter_subscription" class="form-check-input" {{ old('newsletter_subscription') ? 'checked' : '' }}>
                <label for="newsletter_subscription" class="form-check-label">
                    Subscribe to our newsletter for updates and news
                </label>
            </div>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn-submit qu_btn">
                <span class="btn-text">Send Message</span>
                <span class="btn-loading" style="display: none;">
                    <i class="fa fa-spinner fa-spin"></i> Sending...
                </span>
            </button>
        </div>
        
        <!-- Form Messages -->
        <div class="form-message"></div>
    </form>
</div>

<style>
.contact-form-wrapper {
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.contact-form-header {
    text-align: center;
    margin-bottom: 30px;
}

.contact-form-header h3 {
    color: #333;
    margin-bottom: 10px;
    font-size: 24px;
}

.contact-form-header p {
    color: #666;
    margin: 0;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: 500;
}

.required {
    color: #e74c3c;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e1e8ed;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s, box-shadow 0.3s;
    background: #fff;
}

.form-control:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.form-control.error {
    border-color: #e74c3c;
}

.error-message {
    color: #e74c3c;
    font-size: 12px;
    margin-top: 5px;
    display: block;
}

.child-info-section {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 10px;
    margin: 25px 0;
}

.child-info-section h4 {
    color: #333;
    margin-bottom: 20px;
    font-size: 18px;
}

.form-check {
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.form-check-input {
    margin-top: 4px;
    width: 18px;
    height: 18px;
}

.form-check-label {
    margin: 0;
    line-height: 1.5;
    cursor: pointer;
}

.form-check-label a {
    color: #007bff;
    text-decoration: none;
}

.form-check-label a:hover {
    text-decoration: underline;
}

.btn-submit {
    width: 100%;
    padding: 15px;
    background: linear-gradient(45deg, #007bff, #0056b3);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
}

.btn-submit:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.btn-loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.form-message {
    margin-top: 20px;
    padding: 15px;
    border-radius: 8px;
    display: none;
}

.form-message.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    display: block;
}

.form-message.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f1b0b7;
    display: block;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 8px;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f1b0b7;
}

@media (max-width: 768px) {
    .contact-form-wrapper {
        padding: 25px 20px;
    }
    
    .child-info-section {
        padding: 20px 15px;
    }
    
    .form-control {
        padding: 10px 12px;
    }
    
    .btn-submit {
        padding: 12px;
        font-size: 14px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contact-form');
    const submitBtn = form.querySelector('.btn-submit');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoading = submitBtn.querySelector('.btn-loading');
    const messageDiv = form.querySelector('.form-message');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        submitBtn.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline-block';
        messageDiv.style.display = 'none';
        
        // Get form data
        const formData = new FormData(form);
        
        // Submit form
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageDiv.className = 'form-message success';
                messageDiv.innerHTML = '<i class="fa fa-check-circle"></i> ' + data.message;
                messageDiv.style.display = 'block';
                form.reset();
                
                // Reset reCAPTCHA if present
                if (typeof grecaptcha !== 'undefined') {
                    grecaptcha.reset();
                }
            } else {
                messageDiv.className = 'form-message error';
                messageDiv.innerHTML = '<i class="fa fa-exclamation-circle"></i> ' + (data.message || 'An error occurred. Please try again.');
                messageDiv.style.display = 'block';
            }
        })
        .catch(error => {
            messageDiv.className = 'form-message error';
            messageDiv.innerHTML = '<i class="fa fa-exclamation-circle"></i> An error occurred. Please try again.';
            messageDiv.style.display = 'block';
        })
        .finally(() => {
            // Reset button state
            submitBtn.disabled = false;
            btnText.style.display = 'inline-block';
            btnLoading.style.display = 'none';
        });
    });
});
</script>