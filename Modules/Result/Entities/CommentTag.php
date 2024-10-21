<?php

namespace Modules\Result\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommentTag extends Model
{
    use HasFactory;

    protected $fillable = ['tag'];
}
