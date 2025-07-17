<?php

namespace Modules\Website\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class NewsletterSubscriber extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'status',
        'interests',
        'preferences',
        'subscription_source',
        'ip_address',
        'user_agent',
        'referrer',
        'verification_token',
        'verified_at',
        'subscribed_at',
        'unsubscribed_at',
        'unsubscribe_reason',
        'email_opens',
        'email_clicks',
        'last_email_sent_at',
        'last_activity_at',
        'double_opt_in'
    ];

    protected $casts = [
        'interests' => 'array',
        'preferences' => 'array',
        'verified_at' => 'datetime',
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'last_email_sent_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'double_opt_in' => 'boolean',
        'email_opens' => 'integer',
        'email_clicks' => 'integer'
    ];

    protected $dates = [
        'verified_at',
        'subscribed_at',
        'unsubscribed_at',
        'last_email_sent_at',
        'last_activity_at'
    ];

    // Scopes
    public function scopeSubscribed($query)
    {
        return $query->where('status', 'subscribed');
    }

    public function scopeUnsubscribed($query)
    {
        return $query->where('status', 'unsubscribed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeBounced($query)
    {
        return $query->where('status', 'bounced');
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    public function scopeUnverified($query)
    {
        return $query->whereNull('verified_at');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'subscribed')
                    ->whereNotNull('verified_at');
    }

    public function scopeBySource($query, $source)
    {
        return $query->where('subscription_source', $source);
    }

    public function scopeWithInterest($query, $interest)
    {
        return $query->whereJsonContains('interests', $interest);
    }

    public function scopeRecentActivity($query, $days = 30)
    {
        return $query->where('last_activity_at', '>=', now()->subDays($days));
    }

    public function scopeInactive($query, $days = 90)
    {
        return $query->where('last_activity_at', '<', now()->subDays($days))
                    ->orWhereNull('last_activity_at');
    }

    public function scopeEngaged($query)
    {
        return $query->where('email_opens', '>', 0)
                    ->orWhere('email_clicks', '>', 0);
    }

    public function scopeHighEngagement($query, $openThreshold = 10, $clickThreshold = 5)
    {
        return $query->where('email_opens', '>=', $openThreshold)
                    ->orWhere('email_clicks', '>=', $clickThreshold);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        if ($this->first_name && $this->last_name) {
            return trim($this->first_name . ' ' . $this->last_name);
        }
        
        return $this->first_name ?: $this->email;
    }

    public function getIsSubscribedAttribute()
    {
        return $this->status === 'subscribed';
    }

    public function getIsUnsubscribedAttribute()
    {
        return $this->status === 'unsubscribed';
    }

    public function getIsPendingAttribute()
    {
        return $this->status === 'pending';
    }

    public function getIsBouncedAttribute()
    {
        return $this->status === 'bounced';
    }

    public function getIsVerifiedAttribute()
    {
        return $this->verified_at !== null;
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'subscribed' && $this->verified_at !== null;
    }

    public function getEngagementRateAttribute()
    {
        if ($this->email_opens == 0) {
            return 0;
        }
        
        return round(($this->email_clicks / $this->email_opens) * 100, 2);
    }

    public function getSubscriptionDurationAttribute()
    {
        if (!$this->subscribed_at) {
            return null;
        }
        
        $endDate = $this->unsubscribed_at ?: now();
        return $this->subscribed_at->diffForHumans($endDate, true);
    }

    public function getLastActivityHumanAttribute()
    {
        return $this->last_activity_at ? $this->last_activity_at->diffForHumans() : 'Never';
    }

    // Methods
    public function generateVerificationToken()
    {
        $this->verification_token = Str::random(64);
        $this->save();
        
        return $this->verification_token;
    }

    public function verify()
    {
        $this->update([
            'status' => 'subscribed',
            'verified_at' => now(),
            'subscribed_at' => now(),
            'verification_token' => null,
            'last_activity_at' => now()
        ]);
    }

    public function subscribe($source = null)
    {
        $updateData = [
            'status' => 'subscribed',
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
            'unsubscribe_reason' => null,
            'last_activity_at' => now()
        ];
        
        if ($source) {
            $updateData['subscription_source'] = $source;
        }
        
        // If double opt-in is disabled, mark as verified
        if (!$this->double_opt_in) {
            $updateData['verified_at'] = now();
        }
        
        $this->update($updateData);
    }

    public function unsubscribe($reason = null)
    {
        $this->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
            'unsubscribe_reason' => $reason,
            'last_activity_at' => now()
        ]);
    }

    public function resubscribe()
    {
        $this->update([
            'status' => 'subscribed',
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
            'unsubscribe_reason' => null,
            'last_activity_at' => now()
        ]);
    }

    public function markAsBounced()
    {
        $this->update([
            'status' => 'bounced',
            'last_activity_at' => now()
        ]);
    }

    public function recordEmailOpen()
    {
        $this->increment('email_opens');
        $this->update(['last_activity_at' => now()]);
    }

    public function recordEmailClick()
    {
        $this->increment('email_clicks');
        $this->update(['last_activity_at' => now()]);
    }

    public function updateLastEmailSent()
    {
        $this->update(['last_email_sent_at' => now()]);
    }

    public function addInterest($interest)
    {
        $interests = $this->interests ?: [];
        
        if (!in_array($interest, $interests)) {
            $interests[] = $interest;
            $this->update(['interests' => $interests]);
        }
    }

    public function removeInterest($interest)
    {
        $interests = $this->interests ?: [];
        $interests = array_filter($interests, fn($i) => $i !== $interest);
        
        $this->update(['interests' => array_values($interests)]);
    }

    public function hasInterest($interest)
    {
        return in_array($interest, $this->interests ?: []);
    }

    public function updatePreference($key, $value)
    {
        $preferences = $this->preferences ?: [];
        $preferences[$key] = $value;
        
        $this->update(['preferences' => $preferences]);
    }

    public function getPreference($key, $default = null)
    {
        return $this->preferences[$key] ?? $default;
    }

    public function isSubscribed()
    {
        return $this->status === 'subscribed';
    }

    public function isUnsubscribed()
    {
        return $this->status === 'unsubscribed';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isBounced()
    {
        return $this->status === 'bounced';
    }

    public function isVerified()
    {
        return $this->verified_at !== null;
    }

    public function isActive()
    {
        return $this->status === 'subscribed' && $this->verified_at !== null;
    }

    // Factory will be created separately when needed
    // protected static function newFactory()
    // {
    //     return \Modules\Website\Database\factories\NewsletterSubscriberFactory::new();
    // }
}