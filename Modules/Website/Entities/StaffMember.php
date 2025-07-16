<?php

namespace Modules\Website\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class StaffMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'slug',
        'title',
        'position',
        'department',
        'bio',
        'short_bio',
        'profile_image',
        'email',
        'phone',
        'office_location',
        'qualifications',
        'specializations',
        'research_interests',
        'publications',
        'awards',
        'social_links',
        'office_hours',
        'join_date',
        'years_experience',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'schema_markup',
        'status',
        'featured',
        'show_on_website',
        'sort_order'
    ];

    protected $casts = [
        'qualifications' => 'array',
        'specializations' => 'array',
        'research_interests' => 'array',
        'publications' => 'array',
        'awards' => 'array',
        'social_links' => 'array',
        'schema_markup' => 'array',
        'join_date' => 'date',
        'years_experience' => 'integer',
        'featured' => 'boolean',
        'show_on_website' => 'boolean',
        'sort_order' => 'integer'
    ];

    protected $dates = [
        'join_date'
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeRetired($query)
    {
        return $query->where('status', 'retired');
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeVisible($query)
    {
        return $query->where('show_on_website', true);
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeOrderBySort($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    public function scopeOrderByName($query)
    {
        return $query->orderBy('last_name', 'asc')
                    ->orderBy('first_name', 'asc');
    }

    public function scopeByPosition($query, $position)
    {
        return $query->where('position', 'like', '%' . $position . '%');
    }

    public function scopeWithExperience($query, $minYears)
    {
        return $query->where('years_experience', '>=', $minYears);
    }

    public function scopeRecentJoins($query, $months = 12)
    {
        return $query->where('join_date', '>=', now()->subMonths($months));
    }

    // Mutators
    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = $value;
        $this->generateSlug();
    }

    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = $value;
        $this->generateSlug();
    }

    protected function generateSlug()
    {
        if (!empty($this->attributes['first_name']) && !empty($this->attributes['last_name'])) {
            $name = $this->attributes['first_name'] . ' ' . $this->attributes['last_name'];
            $this->attributes['slug'] = Str::slug($name);
        }
    }

    // Accessors
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getFullNameAttribute()
    {
        $name = trim($this->first_name . ' ' . $this->last_name);
        return $this->title ? $this->title . ' ' . $name : $name;
    }

    public function getDisplayNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getFormalNameAttribute()
    {
        $name = trim($this->first_name . ' ' . $this->last_name);
        return $this->title ? $this->title . ' ' . $name : $name;
    }

    public function getShortBioAttribute($value)
    {
        if ($value) {
            return $value;
        }

        // Auto-generate short bio from full bio if not provided
        return Str::limit(strip_tags($this->bio), 120);
    }

    public function getProfileImageUrlAttribute()
    {
        if ($this->profile_image) {
            return $this->profile_image;
        }

        // Return default profile image
        return '/images/default-profile.png';
    }

    public function getYearsAtSchoolAttribute()
    {
        if (!$this->join_date) {
            return null;
        }

        return $this->join_date->diffInYears(now());
    }

    public function getFormattedJoinDateAttribute()
    {
        return $this->join_date ? $this->join_date->format('M Y') : null;
    }

    public function getSocialLinksAttribute($value)
    {
        $links = json_decode($value, true) ?: [];
        
        // Ensure common social platforms are available
        $defaultLinks = [
            'linkedin' => null,
            'twitter' => null,
            'facebook' => null,
            'instagram' => null,
            'website' => null,
            'google_scholar' => null,
            'researchgate' => null
        ];

        return array_merge($defaultLinks, $links);
    }

    public function getHasSocialLinksAttribute()
    {
        $links = $this->social_links ?: [];
        return collect($links)->filter()->isNotEmpty();
    }

    public function getHasQualificationsAttribute()
    {
        return !empty($this->qualifications);
    }

    public function getHasSpecializationsAttribute()
    {
        return !empty($this->specializations);
    }

    public function getHasResearchInterestsAttribute()
    {
        return !empty($this->research_interests);
    }

    public function getHasPublicationsAttribute()
    {
        return !empty($this->publications);
    }

    public function getHasAwardsAttribute()
    {
        return !empty($this->awards);
    }

    public function getQualificationsListAttribute()
    {
        return $this->qualifications ? implode(', ', $this->qualifications) : '';
    }

    public function getSpecializationsListAttribute()
    {
        return $this->specializations ? implode(', ', $this->specializations) : '';
    }

    // Methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isInactive()
    {
        return $this->status === 'inactive';
    }

    public function isRetired()
    {
        return $this->status === 'retired';
    }

    public function isFeatured()
    {
        return $this->featured;
    }

    public function isVisibleOnWebsite()
    {
        return $this->show_on_website;
    }

    public function getSocialLink($platform)
    {
        $links = $this->social_links ?: [];
        return $links[$platform] ?? null;
    }

    public function setSocialLink($platform, $url)
    {
        $links = $this->social_links ?: [];
        $links[$platform] = $url;
        $this->update(['social_links' => $links]);
    }

    public function addQualification($qualification)
    {
        $qualifications = $this->qualifications ?: [];
        
        if (!in_array($qualification, $qualifications)) {
            $qualifications[] = $qualification;
            $this->update(['qualifications' => $qualifications]);
        }
    }

    public function removeQualification($qualification)
    {
        $qualifications = $this->qualifications ?: [];
        $qualifications = array_filter($qualifications, fn($q) => $q !== $qualification);
        
        $this->update(['qualifications' => array_values($qualifications)]);
    }

    public function addSpecialization($specialization)
    {
        $specializations = $this->specializations ?: [];
        
        if (!in_array($specialization, $specializations)) {
            $specializations[] = $specialization;
            $this->update(['specializations' => $specializations]);
        }
    }

    public function removeSpecialization($specialization)
    {
        $specializations = $this->specializations ?: [];
        $specializations = array_filter($specializations, fn($s) => $s !== $specialization);
        
        $this->update(['specializations' => array_values($specializations)]);
    }

    public function addPublication($publication)
    {
        $publications = $this->publications ?: [];
        $publications[] = $publication;
        $this->update(['publications' => $publications]);
    }

    public function addAward($award)
    {
        $awards = $this->awards ?: [];
        $awards[] = $award;
        $this->update(['awards' => $awards]);
    }

    // Factory will be created separately when needed
    // protected static function newFactory()
    // {
    //     return \Modules\Website\Database\factories\StaffMemberFactory::new();
    // }
}