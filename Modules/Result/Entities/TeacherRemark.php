<?php

namespace Modules\Result\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeacherRemark extends Model
{
    use HasFactory;

    protected $fillable = ['remark', 'student_id', 'exam_type_id'];
}
