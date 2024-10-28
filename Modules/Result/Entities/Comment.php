<?php

namespace Modules\Result\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;
    protected $fillable = ['text', 'is_flagged', 'type', 'school_id'];

    // Define the many-to-many relationship with tags
    public function tags()
    {
        return $this->belongsToMany(CommentTag::class, 'comment_pivots', 'comment_id', 'comment_tag_id');
    }
}
