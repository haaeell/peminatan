<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Objection;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class ObjectionController extends Controller
{
    public function index()
    {
        $objections = Objection::with([
            'student',
            'announcement',
            'reviewer',
        ])
            ->latest()
            ->paginate(20);

        return view('admin.objections.index', compact('objections'));
    }

    public function approve(Request $request, Objection $objection, ActivityLogService $logger)
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string'],
        ]);

        $objection->update([
            'status' => 'approved',
            'admin_note' => $validated['admin_note'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $logger->log('objection', 'approve', $objection);

        return back()->with('success', 'Keberatan disetujui.');
    }

    public function reject(Request $request, Objection $objection, ActivityLogService $logger)
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string'],
        ]);

        $objection->update([
            'status' => 'rejected',
            'admin_note' => $validated['admin_note'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $logger->log('objection', 'reject', $objection);

        return back()->with('success', 'Keberatan ditolak.');
    }
}
