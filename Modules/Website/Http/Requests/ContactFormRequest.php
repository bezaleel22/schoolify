<?php

namespace Modules\Website\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true; // Public contact form, no authorization needed
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20|regex:/^[+]?[0-9\s\-\(\)]+$/',
            'subject' => 'nullable|string|max:200',
            'message' => 'required|string|min:10|max:2000',
            'inquiry_type' => 'required|string|in:general,admissions,academic,support,complaint,suggestion',
            'preferred_contact_method' => 'nullable|string|in:email,phone,both',
            'urgency' => 'nullable|string|in:low,medium,high',
            'student_id' => 'nullable|string|max:20',
            'grade_level' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:100'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages()
    {
        return [
            'name.required' => 'Name is required.',
            'name.min' => 'Name must be at least 2 characters.',
            'name.max' => 'Name cannot exceed 100 characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'phone.regex' => 'Please provide a valid phone number.',
            'message.required' => 'Message is required.',
            'message.min' => 'Message must be at least 10 characters.',
            'message.max' => 'Message cannot exceed 2000 characters.',
            'inquiry_type.required' => 'Please select an inquiry type.',
            'inquiry_type.in' => 'Invalid inquiry type selected.',
            'preferred_contact_method.in' => 'Invalid contact method selected.',
            'urgency.in' => 'Invalid urgency level selected.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes()
    {
        return [
            'inquiry_type' => 'inquiry type',
            'preferred_contact_method' => 'preferred contact method',
            'student_id' => 'student ID',
            'grade_level' => 'grade level'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Clean and sanitize input data
        $this->merge([
            'name' => $this->sanitizeText($this->input('name')),
            'email' => trim(strtolower($this->input('email'))),
            'phone' => $this->sanitizePhone($this->input('phone')),
            'subject' => $this->sanitizeText($this->input('subject')),
            'message' => $this->sanitizeMessage($this->input('message')),
            'inquiry_type' => $this->input('inquiry_type', 'general'),
            'preferred_contact_method' => $this->input('preferred_contact_method', 'email'),
            'urgency' => $this->input('urgency', 'medium')
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check for spam patterns
            if ($this->isSpam()) {
                $validator->errors()->add('message', 'Message appears to be spam.');
            }

            // Check for excessive links
            if ($this->hasExcessiveLinks()) {
                $validator->errors()->add('message', 'Message cannot contain more than 2 links.');
            }

            // Validate phone number if provided
            if ($this->input('phone') && !$this->isValidPhoneNumber($this->input('phone'))) {
                $validator->errors()->add('phone', 'Invalid phone number format.');
            }

            // Check rate limiting
            if ($this->isRateLimited()) {
                $validator->errors()->add('email', 'Too many submissions from this email. Please try again later.');
            }

            // If urgency is high, require additional information
            if ($this->input('urgency') === 'high' && strlen($this->input('message')) < 50) {
                $validator->errors()->add('message', 'High urgency requests require a detailed message (minimum 50 characters).');
            }
        });
    }

    /**
     * Check if the message contains spam patterns
     */
    private function isSpam()
    {
        $message = strtolower($this->input('message', ''));
        $spamPatterns = [
            '/\b(buy|purchase|order|cheap|discount|sale|offer|deal)\b.*\b(now|today|click|here)\b/i',
            '/\b(earn|make)\b.*\$\d+.*\b(day|week|month)\b/i',
            '/\b(weight loss|lose weight|diet pills)\b/i',
            '/\b(casino|poker|gambling|lottery|winner)\b/i',
            '/\b(viagra|cialis|pharmacy)\b/i',
            '/http[s]?:\/\/[^\s]+\.(tk|ml|ga|cf)/i' // Suspicious domains
        ];

        foreach ($spamPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        // Check for excessive repetition
        $words = str_word_count($message, 1);
        if (count($words) > 10) {
            $uniqueWords = array_unique($words);
            if ((count($uniqueWords) / count($words)) < 0.4) {
                return true; // Too much repetition
            }
        }

        return false;
    }

    /**
     * Check if message has excessive links
     */
    private function hasExcessiveLinks()
    {
        $message = $this->input('message', '');
        $linkCount = preg_match_all('/https?:\/\/[^\s]+/', $message);
        return $linkCount > 2;
    }

    /**
     * Validate phone number format
     */
    private function isValidPhoneNumber($phone)
    {
        if (empty($phone)) {
            return true;
        }

        // Remove all non-digit characters for length check
        $digitsOnly = preg_replace('/\D/', '', $phone);
        
        // Check length (7-15 digits is generally acceptable)
        if (strlen($digitsOnly) < 7 || strlen($digitsOnly) > 15) {
            return false;
        }

        return true;
    }

    /**
     * Check rate limiting based on email/IP
     */
    private function isRateLimited()
    {
        $email = $this->input('email');
        $ip = $this->ip();
        
        if (!$email) {
            return false;
        }

        // Check email-based rate limiting (3 submissions per hour)
        $emailKey = 'contact_form_email:' . md5($email);
        $emailCount = cache()->get($emailKey, 0);
        
        if ($emailCount >= 3) {
            return true;
        }

        // Check IP-based rate limiting (5 submissions per hour)
        $ipKey = 'contact_form_ip:' . md5($ip);
        $ipCount = cache()->get($ipKey, 0);
        
        if ($ipCount >= 5) {
            return true;
        }

        // Increment counters
        cache()->put($emailKey, $emailCount + 1, 3600);
        cache()->put($ipKey, $ipCount + 1, 3600);

        return false;
    }

    /**
     * Sanitize text input
     */
    private function sanitizeText($text)
    {
        if (!$text) {
            return $text;
        }

        // Remove HTML tags and trim whitespace
        $text = strip_tags($text);
        $text = trim($text);
        
        // Remove excessive whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        
        return $text;
    }

    /**
     * Sanitize phone number
     */
    private function sanitizePhone($phone)
    {
        if (!$phone) {
            return $phone;
        }

        // Keep only digits, spaces, hyphens, parentheses, and plus sign
        return preg_replace('/[^+0-9\s\-\(\)]/', '', $phone);
    }

    /**
     * Sanitize message content
     */
    private function sanitizeMessage($message)
    {
        if (!$message) {
            return $message;
        }

        // Allow basic formatting but remove dangerous HTML
        $allowedTags = '<p><br><strong><em><u>';
        $message = strip_tags($message, $allowedTags);
        
        // Clean up whitespace
        $message = trim($message);
        $message = preg_replace('/\s+/', ' ', $message);
        
        return $message;
    }

    /**
     * Get validated and sanitized data
     */
    public function getContactData()
    {
        $validated = $this->validated();
        
        return array_merge($validated, [
            'ip_address' => $this->ip(),
            'user_agent' => $this->header('User-Agent'),
            'referrer' => $this->header('Referer'),
            'submitted_at' => now()
        ]);
    }

    /**
     * Get inquiry type label
     */
    public function getInquiryTypeLabel()
    {
        $types = [
            'general' => 'General Inquiry',
            'admissions' => 'Admissions',
            'academic' => 'Academic Affairs',
            'support' => 'Technical Support',
            'complaint' => 'Complaint',
            'suggestion' => 'Suggestion'
        ];

        return $types[$this->input('inquiry_type')] ?? 'General Inquiry';
    }

    /**
     * Get urgency level label
     */
    public function getUrgencyLabel()
    {
        $levels = [
            'low' => 'Low Priority',
            'medium' => 'Medium Priority',
            'high' => 'High Priority'
        ];

        return $levels[$this->input('urgency')] ?? 'Medium Priority';
    }

    /**
     * Check if this is a high priority request
     */
    public function isHighPriority()
    {
        return $this->input('urgency') === 'high';
    }

    /**
     * Check if phone contact is preferred
     */
    public function prefersPhoneContact()
    {
        return in_array($this->input('preferred_contact_method'), ['phone', 'both']);
    }

    /**
     * Get contact preferences
     */
    public function getContactPreferences()
    {
        return [
            'method' => $this->input('preferred_contact_method', 'email'),
            'urgency' => $this->input('urgency', 'medium'),
            'phone_provided' => !empty($this->input('phone'))
        ];
    }
}