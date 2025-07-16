<?php

namespace Modules\Website\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class BlogComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'author_name',
        'author_email',
        'author_avatar',
        'content',
        'user_agent',
        'ip_address',
        'status',
        'auth_provider',
        'google_id',
        'is_verified',
        'approved_at',
        'approved_by'
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'approved_at' => 'datetime'
    ];

    protected $dates = [
        'approved_at'
    ];

    // Relationships
    public function post()
    {
        return $this->belongsTo(BlogPost::class, 'post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(BlogComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(BlogComment::class, 'parent_id');
    }

    public function approvedReplies()
    {
        return $this->hasMany(BlogComment::class, 'parent_id')
                    ->where('status', 'approved');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSpam($query)
    {
        return $query->where('status', 'spam');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeReplies($query)
    {
        return $query->whereNotNull('parent_id');
    }

    public function scopeByProvider($query, $provider)
    {
        return $query->where('auth_provider', $provider);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getAuthorDisplayNameAttribute()
    {
        if ($this->user) {
            return $this->user->name;
        }
        
        return $this->author_name ?: 'Anonymous';
    }

    public function getAuthorAvatarUrlAttribute()
    {
        if ($this->user && $this->user->avatar) {
            return $this->user->avatar;
        }
        
        if ($this->author_avatar) {
            return $this->author_avatar;
        }
        
        // Generate Gravatar URL
        $email = $this->user ? $this->user->email : $this->author_email;
        if ($email) {
            return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?d=mp&s=80';
        }
        
        return '/images/default-avatar.png';
    }

    public function getIsGuestAttribute()
    {
        return $this->auth_provider === 'guest';
    }

    public function getIsGoogleUserAttribute()
    {
        return $this->auth_provider === 'google';
    }

    public function getIsRegisteredUserAttribute()
    {
        return $this->auth_provider === 'local' && $this->user_id;
    }

    // Methods
    public function approve($approvedBy = null)
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approvedBy
        ]);

        // Update post comment count
        $this->post->updateCommentCount();
    }

    public function reject()
    {
        $this->update(['status' => 'rejected']);
        
        // Update post comment count
        $this->post->updateCommentCount();
    }

    public function markAsSpam()
    {
        $this->update(['status' => 'spam']);
        
        // Update post comment count
        $this->post->updateCommentCount();
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isSpam()
    {
        return $this->status === 'spam';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isTopLevel()
    {
        return $this->parent_id === null;
    }

    public function isReply()
    {
        return $this->parent_id !== null;
    }

    public function hasReplies()
    {
        return $this->replies()->count() > 0;
    }

    // Factory will be created separately when needed
    // protected static function newFactory()
    // {
    //     return \Modules\Website\Database\factories\BlogCommentFactory::new();
    // }
}