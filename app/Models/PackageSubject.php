<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageSubject extends Model
{
    protected $fillable = [
        'package_id',
        'subject_name',
        'order',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
