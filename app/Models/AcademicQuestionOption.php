<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicQuestionOption extends Model
{
    protected $fillable = [
        'academic_question_id',
        'label',
        'option_text',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function question()
    {
        return $this->belongsTo(AcademicQuestion::class, 'academic_question_id');
    }
}
