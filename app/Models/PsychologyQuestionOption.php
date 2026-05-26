<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PsychologyQuestionOption extends Model
{
    protected $fillable = [
        'psychology_question_id',
        'label',
        'option_text',
    ];

    public function question()
    {
        return $this->belongsTo(PsychologyQuestion::class, 'psychology_question_id');
    }

    public function weights()
    {
        return $this->hasMany(PsychologyOptionWeight::class);
    }
}
