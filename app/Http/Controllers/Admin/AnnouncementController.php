<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

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
        $announcement->update([
            'is_published' => true,
            'published_at' => now(),
            'published_by' => auth()->id(),
        ]);

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
