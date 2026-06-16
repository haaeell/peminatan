<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassStudent extends Model
{
    protected $fillable = [
        'class_group_id',
        'student_id',
        'package_id',
        'is_manual_override',
        'pending_class_group_id',
        'pending_package_id',
    ];

    protected $casts = [
        'is_manual_override' => 'boolean',
    ];

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function pendingClassGroup()
    {
        return $this->belongsTo(ClassGroup::class, 'pending_class_group_id');
    }

    public function pendingPackage()
    {
        return $this->belongsTo(Package::class, 'pending_package_id');
    }

    public function scopeHasPendingChange($query)
    {
        return $query->whereNotNull('pending_class_group_id');
    }

    public function hasPendingChange(): bool
    {
        return $this->pending_class_group_id !== null;
    }
}
