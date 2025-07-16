<?php

namespace Modules\Website\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'excerpt',
        'featured_image',
        'gallery',
        'location',
        'location_address',
        'latitude',
        'longitude',
        'start_date',
        'end_date',
        'all_day',
        'timezone',
        'event_type',
        'audience',
        'organizer_name',
        'organizer_email',
        'organizer_phone',
        'registration_required',
        'registration_link',
        'price',
        'max_attendees',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'schema_markup',
        'status',
        'featured',
        'view_count'
    ];

    protected $casts = [
        'gallery' => 'array',
        'schema_markup' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'all_day' => 'boolean',
        'registration_required' => 'boolean',
        'price' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'max_attendees' => 'integer',
        'featured' => 'boolean',
        'view_count' => 'integer'
    ];

    protected $dates = [
        'start_date',
        'end_date'
    ];

    // Relationships
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

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopePostponed($query)
    {
        return $query->where('status', 'postponed');
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('event_type', $type);
    }

    public function scopeByAudience($query, $audience)
    {
        return $query->where('audience', $audience);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now())
                    ->orderBy('start_date', 'asc');
    }

    public function scopePast($query)
    {
        return $query->where('end_date', '<', now())
                    ->orderBy('start_date', 'desc');
    }

    public function scopeOngoing($query)
    {
        return $query->where('start_date', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                    });
    }

    public function scopeToday($query)
    {
        $today = now()->startOfDay();
        $tomorrow = now()->endOfDay();
        
        return $query->where(function ($q) use ($today, $tomorrow) {
            $q->whereBetween('start_date', [$today, $tomorrow])
              ->orWhere(function ($subQ) use ($today, $tomorrow) {
                  $subQ->where('start_date', '<=', $today)
                       ->where('end_date', '>=', $tomorrow);
              });
        });
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('start_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('start_date', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhere(function ($subQ) use ($startDate, $endDate) {
                  $subQ->where('start_date', '<=', $startDate)
                       ->where('end_date', '>=', $endDate);
              })
              ->orWhere(function ($subQ) use ($startDate, $endDate) {
                  $subQ->whereBetween('end_date', [$startDate, $endDate]);
              });
        });
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

        // Auto-generate excerpt from description if not provided
        return Str::limit(strip_tags($this->description), 160);
    }

    public function getFormattedDateAttribute()
    {
        if ($this->all_day) {
            if ($this->end_date && $this->start_date->format('Y-m-d') !== $this->end_date->format('Y-m-d')) {
                return $this->start_date->format('M j') . ' - ' . $this->end_date->format('M j, Y');
            }
            return $this->start_date->format('M j, Y');
        }

        if ($this->end_date) {
            if ($this->start_date->format('Y-m-d') === $this->end_date->format('Y-m-d')) {
                return $this->start_date->format('M j, Y g:i A') . ' - ' . $this->end_date->format('g:i A');
            }
            return $this->start_date->format('M j, Y g:i A') . ' - ' . $this->end_date->format('M j, Y g:i A');
        }

        return $this->start_date->format('M j, Y g:i A');
    }

    public function getDurationAttribute()
    {
        if (!$this->end_date) {
            return null;
        }

        return $this->start_date->diffForHumans($this->end_date, true);
    }

    public function getIsMultiDayAttribute()
    {
        return $this->end_date && $this->start_date->format('Y-m-d') !== $this->end_date->format('Y-m-d');
    }

    public function getHasLocationAttribute()
    {
        return !empty($this->location) || (!empty($this->latitude) && !empty($this->longitude));
    }

    // Methods
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    public function isUpcoming()
    {
        return $this->start_date > now();
    }

    public function isPast()
    {
        return ($this->end_date ?: $this->start_date) < now();
    }

    public function isOngoing()
    {
        return $this->start_date <= now() && 
               ($this->end_date === null || $this->end_date >= now());
    }

    public function isToday()
    {
        $today = now()->format('Y-m-d');
        
        if ($this->end_date) {
            return $this->start_date->format('Y-m-d') <= $today && 
                   $this->end_date->format('Y-m-d') >= $today;
        }
        
        return $this->start_date->format('Y-m-d') === $today;
    }

    public function isFeatured()
    {
        return $this->featured;
    }

    public function requiresRegistration()
    {
        return $this->registration_required;
    }

    public function isFree()
    {
        return $this->price === null || $this->price == 0;
    }

    public function hasMaxAttendees()
    {
        return $this->max_attendees !== null;
    }

    // Factory will be created separately when needed
    // protected static function newFactory()
    // {
    //     return \Modules\Website\Database\factories\EventFactory::new();
    // }
}