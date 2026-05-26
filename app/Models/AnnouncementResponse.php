<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementResponse extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'announcement_id',
        'student_id',
        'response',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
