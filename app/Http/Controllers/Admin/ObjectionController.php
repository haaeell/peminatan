<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\ClassStudent;
use App\Models\Objection;
use App\Models\Package;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ObjectionController extends Controller
{
    public function index()
    {
        $objections = Objection::with([
            'student.packageChoice.firstPackage',
            'student.packageChoice.secondPackage',
            'student.result.recommendedPackage',
            'student.result.finalPackage',
            'student.classStudent.classGroup',
            'announcement',
            'reviewer',
        ])
            ->latest()
            ->get();

        $packages = Package::where('is_active', true)
            ->orderBy('name')
            ->get();

        $classGroups = ClassGroup::withCount('students')
            ->with('package')
            ->orderBy('package_id')
            ->orderBy('name')
            ->get();

        return view('admin.objections.index', compact('objections', 'packages', 'classGroups'));
    }

    public function approve(Request $request, Objection $objection, ActivityLogService $logger)
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string'],
            'final_package_id' => ['required', 'exists:packages,id'],
            'class_group_id' => ['required', 'exists:class_groups,id'],
        ]);

        DB::transaction(function () use ($validated, $objection) {
            $classGroup = ClassGroup::findOrFail($validated['class_group_id']);
            $student = $objection->student;

            abort_if(!$student, 422, 'Data siswa tidak ditemukan.');
            abort_if(
                (int) $classGroup->package_id !== (int) $validated['final_package_id'],
                422,
                'Kelas tujuan harus sesuai dengan jurusan final yang dipilih.'
            );

            // Count both live occupants and other students already staged into this class.
            $liveCount = $classGroup->students()
                ->where('student_id', '!=', $student->id)
                ->count();

            $pendingCount = ClassStudent::where('pending_class_group_id', $classGroup->id)
                ->where('student_id', '!=', $student->id)
                ->count();

            abort_if(
                $liveCount + $pendingCount >= $classGroup->capacity,
                422,
                'Kapasitas kelas tujuan sudah penuh.'
            );

            // Stage the change — do NOT write to live columns until Final Announcement is published.
            ClassStudent::updateOrCreate(
                ['student_id' => $student->id],
                [
                    'pending_class_group_id' => $classGroup->id,
                    'pending_package_id' => $validated['final_package_id'],
                    'is_manual_override' => true,
                ]
            );

            $objection->update([
                'status' => 'approved',
                'admin_note' => $validated['admin_note'] ?? null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        });

        $logger->log('objection', 'approve', $objection);

        return back()->with('success', 'Keberatan disetujui dan penempatan siswa berhasil diperbarui.');
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
