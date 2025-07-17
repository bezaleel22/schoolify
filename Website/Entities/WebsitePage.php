<?php

namespace Modules\Website\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class WebsitePage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'template',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'schema_markup',
        'status',
        'published_at',
        'sort_order',
        'view_count'
    ];

    protected $casts = [
        'schema_markup' => 'array',
        'published_at' => 'datetime',
        'view_count' => 'integer',
        'sort_order' => 'integer'
    ];

    protected $dates = [
        'published_at'
    ];

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where(function ($q) {
                        $q->whereNull('published_at')
                          ->orWhere('published_at', '<=', now());
                    });
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
                    ->where('published_at', '>', now());
    }

    public function scopeByTemplate($query, $template)
    {
        return $query->where('template', $template);
    }

    // Mutators
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    // Accessors
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getExcerptAttribute($value)
    {
        if ($value) {
            return $value;
        }

        // Auto-generate excerpt from content if not provided
        return Str::limit(strip_tags($this->content), 160);
    }

    // Methods
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    public function isPublished()
    {
        return $this->status === 'published' && 
               ($this->published_at === null || $this->published_at <= now());
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isScheduled()
    {
        return $this->status === 'scheduled' && $this->published_at > now();
    }

    // Factory will be created separately when needed
    // protected static function newFactory()
    // {
    //     return \Modules\Website\Database\factories\WebsitePageFactory::new();
    // }
}