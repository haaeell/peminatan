<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestSession extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'test_date',
        'start_time',
        'end_time',
        'test_type',
        'is_active',
    ];

    protected $casts = [
        'test_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function classes()
    {
        return $this->hasMany(TestSessionClass::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_test_sessions')
            ->withPivot([
                'started_at',
                'finished_at',
                'status',
            ])
            ->withTimestamps();
    }
}
