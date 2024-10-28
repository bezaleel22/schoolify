<?php

namespace Modules\Result\Entities;

use App\SmClass;
use App\SmExamType;
use App\SmSubject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LearningObjective extends Model
{
    use HasFactory;

    protected $fillable = ['class_id', 'subject_id', 'exam_type_id', 'objectives'];

    // Relationships
    public function class()
    {
        return $this->belongsTo(SmClass::class, 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(SmSubject::class, 'student_id');
    }

    public function examType()
    {
        return $this->belongsTo(SmExamType::class, 'exam_type_id');
    }
}
