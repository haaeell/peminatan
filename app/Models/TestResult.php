<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestResult extends Model
{
    protected $fillable = [
        'student_id',
        'academic_score',
        'psychology_scores',
        'recommended_package_id',
        'final_package_id',
        'is_locked',
    ];

    protected $casts = [
        'academic_score' => 'decimal:2',
        'psychology_scores' => 'array',
        'is_locked' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function recommendedPackage()
    {
        return $this->belongsTo(Package::class, 'recommended_package_id');
    }

    public function finalPackage()
    {
        return $this->belongsTo(Package::class, 'final_package_id');
    }
}
