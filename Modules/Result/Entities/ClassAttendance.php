<?php

namespace Modules\Result\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClassAttendance extends Model
{
    use HasFactory;
    protected $tableName = "class_attendances";
    protected $fillable = ['days_opened', 'days_absent', 'days_present', 'exam_type_id', 'school_id', 'academic_id'];
}
