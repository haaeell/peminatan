<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PsychologyQuestion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'question',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function options()
    {
        return $this->hasMany(PsychologyQuestionOption::class);
    }

    public function answers()
    {
        return $this->hasMany(StudentPsychologyAnswer::class);
    }
}
