<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\ClassStudent;
use App\Models\TestResult;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::latest()->paginate(10);

        return view('admin.announcements.index', compact('announcements'));
    }

    public function store(Request $request, ActivityLogService $logger)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:temporary,final'],
            'title' => ['required', 'string', 'max:150'],
            'content' => ['nullable', 'string'],
        ]);

        $announcement = Announcement::create($validated);

        $logger->log('announcement', 'create', $announcement);

        return back()->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function update(Request $request, Announcement $announcement, ActivityLogService $logger)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:temporary,final'],
            'title' => ['required', 'string', 'max:150'],
            'content' => ['nullable', 'string'],
        ]);

        $announcement->update($validated);

        $logger->log('announcement', 'update', $announcement);

        return back()->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function publish(Announcement $announcement, ActivityLogService $logger)
    {
        DB::transaction(function () use ($announcement) {
            // Flush all staged placements to live columns when the final announcement is published.
            if ($announcement->type === 'final') {
                $pending = ClassStudent::hasPendingChange()->get();

                foreach ($pending as $classStudent) {
                    $newClassGroupId = $classStudent->pending_class_group_id;
                    $newPackageId = $classStudent->pending_package_id;

                    $classStudent->update([
                        'class_group_id' => $newClassGroupId,
                        'package_id' => $newPackageId,
                        'pending_class_group_id' => null,
                        'pending_package_id' => null,
                    ]);

                    TestResult::where('student_id', $classStudent->student_id)
                        ->update(['final_package_id' => $newPackageId]);
                }
            }

            $announcement->update([
                'is_published' => true,
                'published_at' => now(),
                'published_by' => auth()->id(),
            ]);
        });

        $logger->log('announcement', 'publish', $announcement);

        return back()->with('success', 'Pengumuman berhasil dipublikasikan.');
    }

    public function destroy(Announcement $announcement, ActivityLogService $logger)
    {
        $logger->log('announcement', 'delete', $announcement);

        $announcement->delete();

        return back()->with('success', 'Pengumuman berhasil dihapus.');
    }
}
