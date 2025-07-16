<?php

namespace Modules\Website\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class GalleryAlbum extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'cover_image',
        'album_type',
        'category',
        'event_date',
        'photographer',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'schema_markup',
        'status',
        'featured',
        'sort_order',
        'view_count',
        'image_count'
    ];

    protected $casts = [
        'schema_markup' => 'array',
        'event_date' => 'date',
        'featured' => 'boolean',
        'sort_order' => 'integer',
        'view_count' => 'integer',
        'image_count' => 'integer'
    ];

    protected $dates = [
        'event_date'
    ];

    // Relationships
    public function images()
    {
        return $this->hasMany(GalleryImage::class, 'album_id');
    }

    public function activeImages()
    {
        return $this->hasMany(GalleryImage::class, 'album_id')
                    ->where('status', 'active')
                    ->orderBy('sort_order', 'asc');
    }

    public function analytics()
    {
        return $this->morphMany(PageAnalytic::class, 'page', 'page_type', 'page_id');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePrivate($query)
    {
        return $query->where('status', 'private');
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('album_type', $type);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrderBySort($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    public function scopeRecent($query, $limit = 6)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function scopeByYear($query, $year)
    {
        return $query->whereYear('event_date', $year);
    }

    public function scopeByMonth($query, $year, $month)
    {
        return $query->whereYear('event_date', $year)
                    ->whereMonth('event_date', $month);
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

    public function getCoverImageUrlAttribute()
    {
        if ($this->cover_image) {
            return $this->cover_image;
        }

        // Use first active image as cover if no cover image is set
        $firstImage = $this->activeImages()->first();
        return $firstImage ? $firstImage->file_path : '/images/default-album-cover.jpg';
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->cover_image) {
            return $this->cover_image;
        }

        // Use first active image thumbnail as cover if no cover image is set
        $firstImage = $this->activeImages()->first();
        return $firstImage ? $firstImage->thumbnail_path : '/images/default-album-cover.jpg';
    }

    public function getFormattedEventDateAttribute()
    {
        return $this->event_date ? $this->event_date->format('M j, Y') : null;
    }

    public function getIsPhotoAlbumAttribute()
    {
        return $this->album_type === 'photos';
    }

    public function getIsVideoAlbumAttribute()
    {
        return $this->album_type === 'videos';
    }

    public function getIsMixedAlbumAttribute()
    {
        return $this->album_type === 'mixed';
    }

    // Methods
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    public function updateImageCount()
    {
        $this->update(['image_count' => $this->activeImages()->count()]);
    }

    public function isPublished()
    {
        return $this->status === 'published';
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isPrivate()
    {
        return $this->status === 'private';
    }

    public function isFeatured()
    {
        return $this->featured;
    }

    public function hasImages()
    {
        return $this->image_count > 0;
    }

    public function getPhotos()
    {
        return $this->activeImages()->whereIn('file_type', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    public function getVideos()
    {
        return $this->activeImages()->whereIn('file_type', ['mp4', 'mov', 'avi', 'wmv', 'flv']);
    }

    // Factory will be created separately when needed
    // protected static function newFactory()
    // {
    //     return \Modules\Website\Database\factories\GalleryAlbumFactory::new();
    // }
}