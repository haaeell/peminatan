<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementResponse;
use App\Models\Objection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ObjectionController extends Controller
{
    public function store(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10'],
        ]);

        $student = auth()->user()->student;

        DB::transaction(function () use ($announcement, $student, $validated) {
            AnnouncementResponse::updateOrCreate(
                [
                    'announcement_id' => $announcement->id,
                    'student_id' => $student->id,
                ],
                [
                    'response' => 'objected',
                    'responded_at' => now(),
                ]
            );

            Objection::updateOrCreate(
                [
                    'announcement_id' => $announcement->id,
                    'student_id' => $student->id,
                ],
                [
                    'reason' => $validated['reason'],
                    'status' => 'pending',
                ]
            );
        });

        return back()->with('success', 'Keberatan berhasil dikirim.');
    }
}
