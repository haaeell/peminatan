<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'type',
        'title',
        'content',
        'is_published',
        'published_at',
        'published_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function publisher()
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function responses()
    {
        return $this->hasMany(AnnouncementResponse::class);
    }

    public function objections()
    {
        return $this->hasMany(Objection::class);
    }
}
