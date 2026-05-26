<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentSelfie extends Model
{
    protected $fillable = [
        'student_id',
        'path',
        'device_info',
        'captured_at',
    ];

    protected $casts = [
        'device_info' => 'array',
        'captured_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
