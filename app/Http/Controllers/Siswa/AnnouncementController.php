<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementResponse;

class AnnouncementController extends Controller
{
    public function index()
    {
        $student = auth()->user()->student;

        $announcement = Announcement::where('is_published', true)
            ->latest('published_at')
            ->first();

        $classStudent = $student->classStudent()
            ->with('classGroup.package')
            ->first();

        $response = $announcement
            ? AnnouncementResponse::where('announcement_id', $announcement->id)
            ->where('student_id', $student->id)
            ->first()
            : null;

        return view('siswa.announcements.index', compact(
            'student',
            'announcement',
            'classStudent',
            'response'
        ));
    }

    public function accept(Announcement $announcement)
    {
        $student = auth()->user()->student;

        AnnouncementResponse::updateOrCreate(
            [
                'announcement_id' => $announcement->id,
                'student_id' => $student->id,
            ],
            [
                'response' => 'accepted',
                'responded_at' => now(),
            ]
        );

        return back()->with('success', 'Pengumuman berhasil diterima.');
    }
}
