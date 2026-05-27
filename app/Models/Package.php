<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subjects()
    {
        return $this->hasMany(PackageSubject::class);
    }

    public function firstChoices()
    {
        return $this->hasMany(StudentPackageChoice::class, 'first_package_id');
    }

    public function secondChoices()
    {
        return $this->hasMany(StudentPackageChoice::class, 'second_package_id');
    }

    public function psychologyWeights()
    {
        return $this->hasMany(PsychologyOptionWeight::class);
    }

    public function classGroups()
    {
        return $this->hasMany(ClassGroup::class);
    }

    public function testResultsRecommended()
    {
        return $this->hasMany(TestResult::class, 'recommended_package_id');
    }

    public function testResultsFinal()
    {
        return $this->hasMany(TestResult::class, 'final_package_id');
    }
}
