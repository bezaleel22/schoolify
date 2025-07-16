<?php

namespace Modules\Website\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class BlogCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'image',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'schema_markup',
        'status',
        'sort_order'
    ];

    protected $casts = [
        'schema_markup' => 'array',
        'sort_order' => 'integer'
    ];

    // Relationships
    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class, 'category_id');
    }

    public function publishedPosts()
    {
        return $this->hasMany(BlogPost::class, 'category_id')
                    ->published();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOrderBySort($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    // Mutators
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    // Accessors
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getPostsCountAttribute()
    {
        return $this->publishedPosts()->count();
    }

    // Methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    // Factory will be created separately when needed
    // protected static function newFactory()
    // {
    //     return \Modules\Website\Database\factories\BlogCategoryFactory::new();
    // }
}