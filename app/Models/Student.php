<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'nisn',
        'nis',
        'name',
        'origin_class',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function biodata()
    {
        return $this->hasOne(StudentBiodata::class);
    }

    public function selfie()
    {
        return $this->hasOne(StudentSelfie::class);
    }

    public function packageChoice()
    {
        return $this->hasOne(StudentPackageChoice::class);
    }

    public function academicAnswers()
    {
        return $this->hasMany(StudentAcademicAnswer::class);
    }

    public function psychologyAnswers()
    {
        return $this->hasMany(StudentPsychologyAnswer::class);
    }

    public function result()
    {
        return $this->hasOne(TestResult::class);
    }

    public function classStudent()
    {
        return $this->hasOne(ClassStudent::class);
    }

    public function testSessions()
    {
        return $this->belongsToMany(TestSession::class, 'student_test_sessions')
            ->withPivot([
                'started_at',
                'finished_at',
                'status',
            ])
            ->withTimestamps();
    }

    public function violations()
    {
        return $this->hasMany(Violation::class);
    }

    public function announcementResponses()
    {
        return $this->hasMany(AnnouncementResponse::class);
    }

    public function objections()
    {
        return $this->hasMany(Objection::class);
    }
}
