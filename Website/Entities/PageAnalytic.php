<?php

namespace Modules\Website\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class PageAnalytic extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_type',
        'page_id',
        'url',
        'title',
        'ip_address',
        'user_agent',
        'referrer',
        'country',
        'city',
        'device_type',
        'browser',
        'platform',
        'session_duration',
        'is_bounce',
        'scroll_depth',
        'utm_parameters',
        'custom_events',
        'visit_date',
        'visit_time',
        'visited_at',
        'session_id',
        'user_id'
    ];

    protected $casts = [
        'utm_parameters' => 'array',
        'custom_events' => 'array',
        'visit_date' => 'date',
        'visit_time' => 'datetime:H:i:s',
        'visited_at' => 'datetime',
        'session_duration' => 'integer',
        'is_bounce' => 'boolean',
        'scroll_depth' => 'integer'
    ];

    protected $dates = [
        'visit_date',
        'visited_at'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function page()
    {
        return $this->morphTo('page', 'page_type', 'page_id');
    }

    // Scopes
    public function scopeByPageType($query, $pageType)
    {
        return $query->where('page_type', $pageType);
    }

    public function scopeByPage($query, $pageType, $pageId)
    {
        return $query->where('page_type', $pageType)
                    ->where('page_id', $pageId);
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    public function scopeByDeviceType($query, $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    public function scopeByBrowser($query, $browser)
    {
        return $query->where('browser', 'like', '%' . $browser . '%');
    }

    public function scopeByPlatform($query, $platform)
    {
        return $query->where('platform', 'like', '%' . $platform . '%');
    }

    public function scopeBounced($query)
    {
        return $query->where('is_bounce', true);
    }

    public function scopeNotBounced($query)
    {
        return $query->where('is_bounce', false);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('visit_date', today());
    }

    public function scopeYesterday($query)
    {
        return $query->whereDate('visit_date', today()->subDay());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('visit_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('visit_date', now()->month)
                    ->whereYear('visit_date', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('visit_date', now()->year);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('visit_date', [$startDate, $endDate]);
    }

    public function scopeWithSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeRegisteredUsers($query)
    {
        return $query->whereNotNull('user_id');
    }

    public function scopeGuestUsers($query)
    {
        return $query->whereNull('user_id');
    }

    public function scopeWithUtmSource($query, $source)
    {
        return $query->whereJsonContains('utm_parameters->utm_source', $source);
    }

    public function scopeWithUtmMedium($query, $medium)
    {
        return $query->whereJsonContains('utm_parameters->utm_medium', $medium);
    }

    public function scopeWithUtmCampaign($query, $campaign)
    {
        return $query->whereJsonContains('utm_parameters->utm_campaign', $campaign);
    }

    public function scopeLongSessions($query, $minDuration = 300)
    {
        return $query->where('session_duration', '>=', $minDuration);
    }

    public function scopeHighEngagement($query, $minScrollDepth = 80)
    {
        return $query->where('scroll_depth', '>=', $minScrollDepth);
    }

    // Accessors
    public function getFormattedSessionDurationAttribute()
    {
        if (!$this->session_duration) {
            return '0s';
        }

        $minutes = floor($this->session_duration / 60);
        $seconds = $this->session_duration % 60;

        if ($minutes > 0) {
            return $minutes . 'm ' . $seconds . 's';
        }

        return $seconds . 's';
    }

    public function getScrollDepthPercentageAttribute()
    {
        return $this->scroll_depth ? $this->scroll_depth . '%' : '0%';
    }

    public function getUtmSourceAttribute()
    {
        return $this->utm_parameters['utm_source'] ?? null;
    }

    public function getUtmMediumAttribute()
    {
        return $this->utm_parameters['utm_medium'] ?? null;
    }

    public function getUtmCampaignAttribute()
    {
        return $this->utm_parameters['utm_campaign'] ?? null;
    }

    public function getUtmTermAttribute()
    {
        return $this->utm_parameters['utm_term'] ?? null;
    }

    public function getUtmContentAttribute()
    {
        return $this->utm_parameters['utm_content'] ?? null;
    }

    public function getIsRegisteredUserAttribute()
    {
        return $this->user_id !== null;
    }

    public function getIsGuestUserAttribute()
    {
        return $this->user_id === null;
    }

    public function getIsBounceAttribute()
    {
        return $this->is_bounce;
    }

    public function getIsLongSessionAttribute()
    {
        return $this->session_duration >= 300; // 5 minutes
    }

    public function getIsHighEngagementAttribute()
    {
        return $this->scroll_depth >= 80; // 80% scroll depth
    }

    // Methods
    public function hasUtmParameters()
    {
        return !empty($this->utm_parameters);
    }

    public function hasCustomEvents()
    {
        return !empty($this->custom_events);
    }

    public function getCustomEvent($eventName)
    {
        return $this->custom_events[$eventName] ?? null;
    }

    public function addCustomEvent($eventName, $eventData)
    {
        $events = $this->custom_events ?: [];
        $events[$eventName] = $eventData;
        $this->update(['custom_events' => $events]);
    }

    public function isBounce()
    {
        return $this->is_bounce;
    }

    public function isLongSession($threshold = 300)
    {
        return $this->session_duration >= $threshold;
    }

    public function isHighEngagement($threshold = 80)
    {
        return $this->scroll_depth >= $threshold;
    }

    public function isFromUtmCampaign()
    {
        return !empty($this->utm_parameters['utm_campaign']);
    }

    public function isFromSocialMedia()
    {
        $socialSources = ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube'];
        $source = strtolower($this->utm_parameters['utm_source'] ?? '');
        
        return in_array($source, $socialSources);
    }

    public function isFromSearch()
    {
        $searchSources = ['google', 'bing', 'yahoo', 'duckduckgo'];
        $source = strtolower($this->utm_parameters['utm_source'] ?? '');
        
        return in_array($source, $searchSources);
    }

    public function isFromEmail()
    {
        $medium = strtolower($this->utm_parameters['utm_medium'] ?? '');
        return $medium === 'email';
    }

    // Factory will be created separately when needed
    // protected static function newFactory()
    // {
    //     return \Modules\Website\Database\factories\PageAnalyticFactory::new();
    // }
}