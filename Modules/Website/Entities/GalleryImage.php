<?php

namespace Modules\Website\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class GalleryImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'album_id',
        'title',
        'description',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'mime_type',
        'width',
        'height',
        'duration',
        'thumbnail_path',
        'alt_text',
        'caption',
        'photographer',
        'taken_at',
        'exif_data',
        'status',
        'sort_order',
        'view_count',
        'download_count'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'duration' => 'integer',
        'taken_at' => 'date',
        'exif_data' => 'array',
        'sort_order' => 'integer',
        'view_count' => 'integer',
        'download_count' => 'integer'
    ];

    protected $dates = [
        'taken_at'
    ];

    // Relationships
    public function album()
    {
        return $this->belongsTo(GalleryAlbum::class, 'album_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeOrderBySort($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    public function scopeImages($query)
    {
        return $query->whereIn('file_type', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    public function scopeVideos($query)
    {
        return $query->whereIn('file_type', ['mp4', 'mov', 'avi', 'wmv', 'flv']);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('file_type', $type);
    }

    public function scopeByPhotographer($query, $photographer)
    {
        return $query->where('photographer', $photographer);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function scopePopular($query, $limit = 10)
    {
        return $query->orderBy('view_count', 'desc')->limit($limit);
    }

    // Accessors
    public function getFileUrlAttribute()
    {
        if (filter_var($this->file_path, FILTER_VALIDATE_URL)) {
            return $this->file_path;
        }
        
        return Storage::url($this->file_path);
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_path) {
            if (filter_var($this->thumbnail_path, FILTER_VALIDATE_URL)) {
                return $this->thumbnail_path;
            }
            
            return Storage::url($this->thumbnail_path);
        }
        
        // For images, return the original file as thumbnail
        if ($this->isImage()) {
            return $this->file_url;
        }
        
        // For videos, return a default video thumbnail
        return '/images/video-thumbnail.png';
    }

    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getDimensionsAttribute()
    {
        if ($this->width && $this->height) {
            return $this->width . ' × ' . $this->height;
        }
        
        return null;
    }

    public function getFormattedDurationAttribute()
    {
        if (!$this->duration) {
            return null;
        }
        
        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;
        
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function getAltTextAttribute($value)
    {
        if ($value) {
            return $value;
        }
        
        // Auto-generate alt text from title or filename
        return $this->title ?: pathinfo($this->file_name, PATHINFO_FILENAME);
    }

    public function getIsImageAttribute()
    {
        return in_array(strtolower($this->file_type), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    public function getIsVideoAttribute()
    {
        return in_array(strtolower($this->file_type), ['mp4', 'mov', 'avi', 'wmv', 'flv']);
    }

    public function getCanPreviewAttribute()
    {
        return $this->isImage() || $this->isVideo();
    }

    // Methods
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    public function incrementDownloadCount()
    {
        $this->increment('download_count');
    }

    public function isImage()
    {
        return in_array(strtolower($this->file_type), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    public function isVideo()
    {
        return in_array(strtolower($this->file_type), ['mp4', 'mov', 'avi', 'wmv', 'flv']);
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isInactive()
    {
        return $this->status === 'inactive';
    }

    public function isProcessing()
    {
        return $this->status === 'processing';
    }

    public function activate()
    {
        $this->update(['status' => 'active']);
        $this->album->updateImageCount();
    }

    public function deactivate()
    {
        $this->update(['status' => 'inactive']);
        $this->album->updateImageCount();
    }

    public function getExifValue($key)
    {
        return $this->exif_data[$key] ?? null;
    }

    public function hasExifData()
    {
        return !empty($this->exif_data);
    }

    // Factory will be created separately when needed
    // protected static function newFactory()
    // {
    //     return \Modules\Website\Database\factories\GalleryImageFactory::new();
    // }
}