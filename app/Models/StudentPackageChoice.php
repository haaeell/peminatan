<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPackageChoice extends Model
{
    protected $fillable = [
        'student_id',
        'first_package_id',
        'second_package_id',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function firstPackage()
    {
        return $this->belongsTo(Package::class, 'first_package_id');
    }

    public function secondPackage()
    {
        return $this->belongsTo(Package::class, 'second_package_id');
    }
}
