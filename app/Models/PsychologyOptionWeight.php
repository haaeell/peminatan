<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PsychologyOptionWeight extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'psychology_question_option_id',
        'package_id',
        'weight',
    ];

    public function option()
    {
        return $this->belongsTo(PsychologyQuestionOption::class, 'psychology_question_option_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
