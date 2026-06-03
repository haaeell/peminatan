<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class TestSession extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'test_date',
        'start_time',
        'end_time',
        'test_type',
        'is_active',
    ];

    protected $casts = [
        'test_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function classes()
    {
        return $this->hasMany(TestSessionClass::class);
    }

    public static function activeForOriginClass(string $originClass): ?self
    {
        $bucket = intdiv(now()->timestamp, 15);

        return Cache::store('file')->remember(
            "test_sessions.active.{$originClass}.{$bucket}",
            now()->addSeconds(15),
            function () use ($originClass) {
                return static::query()
                    ->select(['id', 'name', 'test_date', 'start_time', 'end_time', 'test_type', 'is_active'])
                    ->where('is_active', true)
                    ->where('test_date', today())
                    ->where('start_time', '<=', now()->format('H:i:s'))
                    ->where('end_time', '>=', now()->format('H:i:s'))
                    ->whereHas('classes', function ($query) use ($originClass) {
                        $query->where('origin_class', $originClass);
                    })
                    ->orderBy('test_date')
                    ->orderBy('start_time')
                    ->first();
            }
        );
    }

    public static function upcomingForOriginClass(string $originClass): ?self
    {
        $active = static::activeForOriginClass($originClass);

        if ($active) {
            return $active;
        }

        $bucket = intdiv(now()->timestamp, 15);

        return Cache::store('file')->remember(
            "test_sessions.upcoming.{$originClass}.{$bucket}",
            now()->addSeconds(15),
            function () use ($originClass) {
                return static::query()
                    ->select(['id', 'name', 'test_date', 'start_time', 'end_time', 'test_type', 'is_active'])
                    ->where('is_active', true)
                    ->where(function ($q) {
                        $q->where('test_date', '>', today())
                            ->orWhere(function ($qq) {
                                $qq->where('test_date', today())
                                    ->where('start_time', '>', now()->format('H:i:s'));
                            });
                    })
                    ->whereHas('classes', function ($query) use ($originClass) {
                        $query->where('origin_class', $originClass);
                    })
                    ->orderBy('test_date')
                    ->orderBy('start_time')
                    ->first();
            }
        );
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_test_sessions')
            ->withPivot([
                'started_at',
                'finished_at',
                'status',
                'academic_started_at',
                'psychology_started_at',
                'academic_submitted_at',
                'psychology_submitted_at',
                'academic_duration_seconds',
                'psychology_duration_seconds',
                'academic_submit_type',
                'psychology_submit_type',
                'academic_violation_count',
                'psychology_violation_count',
            ])
            ->withTimestamps();
    }
}
