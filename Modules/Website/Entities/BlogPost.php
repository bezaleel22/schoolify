<?php

namespace Modules\Website\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Models\User;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'gallery',
        'category_id',
        'author_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'schema_markup',
        'tags',
        'status',
        'published_at',
        'featured',
        'allow_comments',
        'view_count',
        'comment_count',
        'reading_time'
    ];

    protected $casts = [
        'gallery' => 'array',
        'schema_markup' => 'array',
        'tags' => 'array',
        'published_at' => 'datetime',
        'featured' => 'boolean',
        'allow_comments' => 'boolean',
        'view_count' => 'integer',
        'comment_count' => 'integer',
        'reading_time' => 'integer'
    ];

    protected $dates = [
        'published_at'
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function comments()
    {
        return $this->hasMany(BlogComment::class, 'post_id');
    }

    public function approvedComments()
    {
        return $this->hasMany(BlogComment::class, 'post_id')
                    ->where('status', 'approved');
    }

    public function analytics()
    {
        return $this->morphMany(PageAnalytic::class, 'page', 'page_type', 'page_id');
    }

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

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeByCategory($query, $categorySlug)
    {
        return $query->whereHas('category', function ($q) use ($categorySlug) {
            $q->where('slug', $categorySlug);
        });
    }

    public function scopeByTag($query, $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    public function scopeByAuthor($query, $authorId)
    {
        return $query->where('author_id', $authorId);
    }

    public function scopePopular($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days))
                    ->orderBy('view_count', 'desc');
    }

    // Mutators
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = Str::slug($value);
        }

        // Auto-calculate reading time
        if (!empty($this->attributes['content'])) {
            $wordCount = str_word_count(strip_tags($this->attributes['content']));
            $this->attributes['reading_time'] = ceil($wordCount / 200); // 200 words per minute
        }
    }

    public function setContentAttribute($value)
    {
        $this->attributes['content'] = $value;
        
        // Auto-calculate reading time
        $wordCount = str_word_count(strip_tags($value));
        $this->attributes['reading_time'] = ceil($wordCount / 200);
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

    public function getEstimatedReadingTimeAttribute()
    {
        if ($this->reading_time) {
            return $this->reading_time . ' min read';
        }

        $wordCount = str_word_count(strip_tags($this->content));
        $minutes = ceil($wordCount / 200);
        return $minutes . ' min read';
    }

    // Methods
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    public function updateCommentCount()
    {
        $this->update(['comment_count' => $this->approvedComments()->count()]);
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

    public function isFeatured()
    {
        return $this->featured;
    }

    public function allowsComments()
    {
        return $this->allow_comments;
    }

    // Factory will be created separately when needed
    // protected static function newFactory()
    // {
    //     return \Modules\Website\Database\factories\BlogPostFactory::new();
    // }
}