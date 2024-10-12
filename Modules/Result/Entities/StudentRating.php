<?php

namespace Modules\Result\Entities;

use App\SmStudent;
use App\SmExamType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentRating extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'attribute', 'rate', 'color', 'remark', 'exam_type_id'];

    // Relationships
    public function student()
    {
        return $this->belongsTo(SmStudent::class, 'student_id');
    }


    public function examType()
    {
        return $this->belongsTo(SmExamType::class, 'exam_type_id');
    }
}
