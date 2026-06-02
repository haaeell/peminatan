<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'nisn',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function publishedAnnouncements()
    {
        return $this->hasMany(Announcement::class, 'published_by');
    }

    public function reviewedObjections()
    {
        return $this->hasMany(Objection::class, 'reviewed_by');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
