<?php

namespace Modules\Website\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true; // Public search, no authorization needed
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'q' => 'required|string|min:2|max:255',
            'type' => 'nullable|string|in:all,pages,blogs,events,staff,gallery',
            'per_page' => 'nullable|integer|min:1|max:50',
            'category' => 'nullable|string|max:100',
            'sort' => 'nullable|string|in:relevance,date,title,views',
            'order' => 'nullable|string|in:asc,desc'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages()
    {
        return [
            'q.required' => 'Search query is required.',
            'q.min' => 'Search query must be at least 2 characters.',
            'q.max' => 'Search query cannot exceed 255 characters.',
            'type.in' => 'Invalid search type. Allowed types: all, pages, blogs, events, staff, gallery.',
            'per_page.integer' => 'Items per page must be a number.',
            'per_page.min' => 'Items per page must be at least 1.',
            'per_page.max' => 'Items per page cannot exceed 50.',
            'sort.in' => 'Invalid sort option. Allowed: relevance, date, title, views.',
            'order.in' => 'Invalid order option. Allowed: asc, desc.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes()
    {
        return [
            'q' => 'search query',
            'per_page' => 'items per page'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Clean and sanitize the search query
        if ($this->has('q')) {
            $this->merge([
                'q' => trim(strip_tags($this->q))
            ]);
        }

        // Set default values
        $this->merge([
            'type' => $this->input('type', 'all'),
            'per_page' => $this->input('per_page', 10),
            'sort' => $this->input('sort', 'relevance'),
            'order' => $this->input('order', 'desc')
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Additional validation logic if needed
            $query = $this->input('q');
            
            // Check for potentially harmful content
            if ($this->containsSqlInjection($query)) {
                $validator->errors()->add('q', 'Invalid search query.');
            }

            // Check for minimum meaningful length
            if (strlen(preg_replace('/[^a-zA-Z0-9]/', '', $query)) < 2) {
                $validator->errors()->add('q', 'Search query must contain at least 2 alphanumeric characters.');
            }
        });
    }

    /**
     * Check for potential SQL injection patterns
     */
    private function containsSqlInjection($query)
    {
        $patterns = [
            '/(\bunion\b.*\bselect\b)/i',
            '/(\bselect\b.*\bfrom\b)/i',
            '/(\binsert\b.*\binto\b)/i',
            '/(\bupdate\b.*\bset\b)/i',
            '/(\bdelete\b.*\bfrom\b)/i',
            '/(\bdrop\b.*\btable\b)/i',
            '/(\bexec\b|\bexecute\b)/i',
            '/(\bscript\b.*\>/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $query)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the search parameters as an array
     */
    public function getSearchParams()
    {
        return [
            'query' => $this->validated()['q'],
            'type' => $this->validated()['type'],
            'per_page' => $this->validated()['per_page'],
            'category' => $this->validated()['category'] ?? null,
            'sort' => $this->validated()['sort'],
            'order' => $this->validated()['order']
        ];
    }

    /**
     * Get sanitized search query
     */
    public function getQuery()
    {
        return $this->validated()['q'];
    }

    /**
     * Get search type
     */
    public function getType()
    {
        return $this->validated()['type'];
    }

    /**
     * Get pagination size
     */
    public function getPerPage()
    {
        return $this->validated()['per_page'];
    }

    /**
     * Check if this is a specific type search
     */
    public function isTypeSearch($type)
    {
        return $this->getType() === $type;
    }

    /**
     * Check if this is a general search
     */
    public function isGeneralSearch()
    {
        return $this->getType() === 'all';
    }
}