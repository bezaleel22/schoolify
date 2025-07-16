<?php

namespace Modules\Website\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class ContactSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'subject',
        'message',
        'inquiry_type',
        'priority',
        'ip_address',
        'user_agent',
        'referrer',
        'form_data',
        'status',
        'assigned_to',
        'internal_notes',
        'read_at',
        'replied_at',
        'resolved_at',
        'is_spam',
        'spam_score'
    ];

    protected $casts = [
        'form_data' => 'array',
        'read_at' => 'datetime',
        'replied_at' => 'datetime',
        'resolved_at' => 'datetime',
        'is_spam' => 'boolean',
        'spam_score' => 'float'
    ];

    protected $dates = [
        'read_at',
        'replied_at',
        'resolved_at'
    ];

    // Relationships
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Scopes
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeRead($query)
    {
        return $query->where('status', 'read');
    }

    public function scopeReplied($query)
    {
        return $query->where('status', 'replied');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeSpam($query)
    {
        return $query->where('status', 'spam');
    }

    public function scopeNotSpam($query)
    {
        return $query->where('is_spam', false);
    }

    public function scopeByInquiryType($query, $type)
    {
        return $query->where('inquiry_type', $type);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeHigh($query)
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['new', 'read']);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getIsNewAttribute()
    {
        return $this->status === 'new';
    }

    public function getIsReadAttribute()
    {
        return in_array($this->status, ['read', 'replied', 'resolved']);
    }

    public function getIsRepliedAttribute()
    {
        return in_array($this->status, ['replied', 'resolved']);
    }

    public function getIsResolvedAttribute()
    {
        return $this->status === 'resolved';
    }

    public function getIsSpamAttribute()
    {
        return $this->status === 'spam' || $this->is_spam;
    }

    public function getIsAssignedAttribute()
    {
        return $this->assigned_to !== null;
    }

    public function getIsHighPriorityAttribute()
    {
        return in_array($this->priority, ['high', 'urgent']);
    }

    public function getIsUrgentAttribute()
    {
        return $this->priority === 'urgent';
    }

    public function getResponseTimeAttribute()
    {
        if (!$this->replied_at) {
            return null;
        }

        return $this->created_at->diffForHumans($this->replied_at, true);
    }

    public function getResolutionTimeAttribute()
    {
        if (!$this->resolved_at) {
            return null;
        }

        return $this->created_at->diffForHumans($this->resolved_at, true);
    }

    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'urgent' => 'red',
            'high' => 'orange',
            'normal' => 'blue',
            'low' => 'gray',
            default => 'blue'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'new' => 'blue',
            'read' => 'yellow',
            'replied' => 'green',
            'resolved' => 'green',
            'spam' => 'red',
            default => 'gray'
        };
    }

    // Methods
    public function markAsRead($userId = null)
    {
        $this->update([
            'status' => 'read',
            'read_at' => now(),
            'assigned_to' => $userId ?: $this->assigned_to
        ]);
    }

    public function markAsReplied($userId = null)
    {
        $this->update([
            'status' => 'replied',
            'replied_at' => now(),
            'assigned_to' => $userId ?: $this->assigned_to
        ]);
    }

    public function markAsResolved($userId = null)
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'assigned_to' => $userId ?: $this->assigned_to
        ]);
    }

    public function markAsSpam()
    {
        $this->update([
            'status' => 'spam',
            'is_spam' => true
        ]);
    }

    public function assignTo($userId, $notes = null)
    {
        $updateData = ['assigned_to' => $userId];
        
        if ($notes) {
            $updateData['internal_notes'] = $this->internal_notes . "\n\n" . now()->format('Y-m-d H:i:s') . " - Assigned: " . $notes;
        }
        
        $this->update($updateData);
    }

    public function addNote($note, $userId = null)
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        $userInfo = $userId ? " (User ID: {$userId})" : "";
        $newNote = "{$timestamp}{$userInfo} - {$note}";
        
        $this->update([
            'internal_notes' => $this->internal_notes . "\n\n" . $newNote
        ]);
    }

    public function setPriority($priority)
    {
        $this->update(['priority' => $priority]);
    }

    public function isNew()
    {
        return $this->status === 'new';
    }

    public function isRead()
    {
        return in_array($this->status, ['read', 'replied', 'resolved']);
    }

    public function isReplied()
    {
        return in_array($this->status, ['replied', 'resolved']);
    }

    public function isResolved()
    {
        return $this->status === 'resolved';
    }

    public function isSpam()
    {
        return $this->status === 'spam' || $this->is_spam;
    }

    public function isAssigned()
    {
        return $this->assigned_to !== null;
    }

    public function isHighPriority()
    {
        return in_array($this->priority, ['high', 'urgent']);
    }

    // Factory will be created separately when needed
    // protected static function newFactory()
    // {
    //     return \Modules\Website\Database\factories\ContactSubmissionFactory::new();
    // }
}