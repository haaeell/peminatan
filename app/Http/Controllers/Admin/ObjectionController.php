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
            'student.classStudent.pendingClassGroup',
            'student.classStudent.pendingPackage',
            'announcement',
            'reviewer',
        ])
            ->latest()
            ->get();

        $packages = Package::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Effective occupancy per class: pending_class_group_id takes precedence over class_group_id.
        $effectiveOccupancy = ClassStudent::selectRaw(
            'COALESCE(pending_class_group_id, class_group_id) as eff_id, COUNT(*) as cnt'
        )->groupBy('eff_id')->pluck('cnt', 'eff_id');

        $classGroups = ClassGroup::with('package')
            ->orderBy('package_id')
            ->orderBy('name')
            ->get()
            ->each(function ($group) use ($effectiveOccupancy) {
                $group->students_count = $effectiveOccupancy->get($group->id, 0);
            });

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

            // Effective occupancy: use pending_class_group_id if set, else class_group_id.
            // This correctly excludes students staged OUT of this class and includes those staged IN.
            $effectiveCount = ClassStudent::whereRaw(
                'COALESCE(pending_class_group_id, class_group_id) = ?',
                [$classGroup->id]
            )->where('student_id', '!=', $student->id)->count();

            abort_if(
                $effectiveCount >= $classGroup->capacity,
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
