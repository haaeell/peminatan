<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentBiodata extends Model
{
    protected $fillable = [
        'student_id',
        'birth_place',
        'birth_date',
        'gender',
        'address',
        'phone',
        'father_name',
        'mother_name',
        'parent_phone',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
