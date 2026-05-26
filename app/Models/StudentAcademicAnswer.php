<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAcademicAnswer extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'academic_question_id',
        'academic_question_option_id',
        'is_correct',
        'answered_at',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'answered_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function question()
    {
        return $this->belongsTo(AcademicQuestion::class, 'academic_question_id');
    }

    public function option()
    {
        return $this->belongsTo(AcademicQuestionOption::class, 'academic_question_option_id');
    }
}
