<?php

namespace Modules\Website\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BlogCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        // User must be authenticated via Google OAuth
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'content' => 'required|string|min:10|max:2000',
            'parent_id' => 'nullable|integer|exists:blog_comments,id',
            'notify_replies' => 'nullable|boolean'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages()
    {
        return [
            'content.required' => 'Comment content is required.',
            'content.min' => 'Comment must be at least 10 characters long.',
            'content.max' => 'Comment cannot exceed 2000 characters.',
            'parent_id.exists' => 'Invalid parent comment.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes()
    {
        return [
            'content' => 'comment',
            'parent_id' => 'parent comment'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Clean and sanitize the comment content
        if ($this->has('content')) {
            $content = $this->input('content');
            
            // Remove potentially harmful HTML tags but keep basic formatting
            $allowedTags = '<p><br><strong><em><u><a><ul><ol><li><blockquote>';
            $content = strip_tags($content, $allowedTags);
            
            // Clean up whitespace
            $content = trim($content);
            $content = preg_replace('/\s+/', ' ', $content);
            
            $this->merge([
                'content' => $content
            ]);
        }

        // Set default values
        $this->merge([
            'notify_replies' => $this->input('notify_replies', false)
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $content = $this->input('content');
            
            // Check for spam patterns
            if ($this->isSpam($content)) {
                $validator->errors()->add('content', 'Comment appears to be spam.');
            }

            // Check for excessive links
            if ($this->hasExcessiveLinks($content)) {
                $validator->errors()->add('content', 'Comments cannot contain more than 2 links.');
            }

            // Check for inappropriate content
            if ($this->containsInappropriateContent($content)) {
                $validator->errors()->add('content', 'Comment contains inappropriate content.');
            }

            // Check if parent comment exists and belongs to the same post
            if ($this->input('parent_id')) {
                $parentComment = \Modules\Website\Entities\BlogComment::find($this->input('parent_id'));
                if ($parentComment && $parentComment->post_id != $this->route('postId')) {
                    $validator->errors()->add('parent_id', 'Invalid parent comment for this post.');
                }
            }

            // Rate limiting check (prevent rapid-fire commenting)
            if ($this->isRateLimited()) {
                $validator->errors()->add('content', 'Please wait before posting another comment.');
            }
        });
    }

    /**
     * Check if content is spam
     */
    private function isSpam($content)
    {
        $spamPatterns = [
            '/\b(viagra|cialis|casino|poker|gambling)\b/i',
            '/\b(cheap|discount|sale|offer|deal)\b.*\b(watch|bag|shoe|clothes)\b/i',
            '/\b(buy|order|purchase)\b.*\b(online|now|today)\b/i',
            '/http[s]?:\/\/[^\s]+\.(tk|ml|ga|cf)/i', // Suspicious domains
            '/\b(earn|make)\b.*\$\d+.*\b(day|week|month)\b/i'
        ];

        foreach ($spamPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        // Check for excessive repetition
        $words = str_word_count($content, 1);
        $uniqueWords = array_unique($words);
        
        if (count($words) > 10 && (count($uniqueWords) / count($words)) < 0.3) {
            return true; // Too much repetition
        }

        return false;
    }

    /**
     * Check if content has excessive links
     */
    private function hasExcessiveLinks($content)
    {
        $linkCount = preg_match_all('/https?:\/\/[^\s]+/', $content);
        return $linkCount > 2;
    }

    /**
     * Check for inappropriate content
     */
    private function containsInappropriateContent($content)
    {
        $inappropriateWords = [
            // Add inappropriate words as needed
            'spam', 'scam', 'fraud'
        ];

        $content = strtolower($content);
        
        foreach ($inappropriateWords as $word) {
            if (strpos($content, strtolower($word)) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user is rate limited
     */
    private function isRateLimited()
    {
        if (!Auth::check()) {
            return false;
        }

        $userId = Auth::id();
        $cacheKey = "comment_rate_limit_{$userId}";
        
        // Check if user has commented in the last minute
        $lastComment = cache()->get($cacheKey);
        
        if ($lastComment && now()->diffInSeconds($lastComment) < 60) {
            return true;
        }

        // Set rate limit cache
        cache()->put($cacheKey, now(), 60);
        
        return false;
    }

    /**
     * Get the validated comment data
     */
    public function getCommentData()
    {
        $validated = $this->validated();
        
        return [
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
            'notify_replies' => $validated['notify_replies'] ?? false,
            'ip_address' => $this->ip(),
            'user_agent' => $this->header('User-Agent')
        ];
    }

    /**
     * Check if this is a reply to another comment
     */
    public function isReply()
    {
        return !empty($this->validated()['parent_id']);
    }

    /**
     * Get the parent comment ID
     */
    public function getParentId()
    {
        return $this->validated()['parent_id'] ?? null;
    }

    /**
     * Get sanitized content
     */
    public function getCommentContent()
    {
        return $this->validated()['content'];
    }

    /**
     * Check if user wants to be notified of replies
     */
    public function wantsNotification()
    {
        return $this->validated()['notify_replies'] ?? false;
    }

    /**
     * Get comment metadata
     */
    public function getMetadata()
    {
        return [
            'ip_address' => $this->ip(),
            'user_agent' => $this->header('User-Agent'),
            'submitted_at' => now(),
            'referrer' => $this->header('Referer')
        ];
    }
}