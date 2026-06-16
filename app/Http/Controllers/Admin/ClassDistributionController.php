<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\ClassStudent;
use App\Models\Package;
use App\Models\Student;
use App\Services\ActivityLogService;
use App\Services\ClassDistributionService;
use Illuminate\Http\Request;

class ClassDistributionController extends Controller
{
    public function index()
    {
        $classGroups = ClassGroup::with(['package'])
            ->orderBy('name')
            ->get();

        // Group students by their effective class: pending takes precedence over live.
        // This lets the admin see where each student will end up after the final announcement.
        $allClassStudents = ClassStudent::with([
            'student.result',
            'student.packageChoice.firstPackage',
            'student.packageChoice.secondPackage',
            'classGroup',
        ])->get()->groupBy(fn($cs) => $cs->pending_class_group_id ?? $cs->class_group_id);

        $classGroups->each(function ($group) use ($allClassStudents) {
            $group->setRelation('students', $allClassStudents->get($group->id, collect()));
        });

        $packages = Package::where('is_active', true)
            ->orderBy('code')
            ->get();

        $unassignedStudents = Student::whereDoesntHave('classStudent')
            ->where('status', 'completed')
            ->with('result')
            ->get();

        return view('admin.class-distribution.index', compact(
            'classGroups',
            'packages',
            'unassignedStudents'
        ));
    }

    public function store(Request $request, ActivityLogService $logger)
    {
        $validated = $request->validate([
            'package_id' => ['required', 'exists:packages,id'],
            'name' => ['required', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'is_locked' => ['nullable', 'boolean'],
        ]);

        $classGroup = ClassGroup::create([
            'package_id' => $validated['package_id'],
            'name' => strtoupper(trim($validated['name'])),
            'capacity' => $validated['capacity'],
            'is_locked' => $request->boolean('is_locked'),
        ]);

        $logger->log('class_distribution', 'create_class_group', $classGroup, $validated);

        return back()->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function update(Request $request, ClassGroup $classGroup, ActivityLogService $logger)
    {
        $validated = $request->validate([
            'package_id' => ['required', 'exists:packages,id'],
            'name' => ['required', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'is_locked' => ['nullable', 'boolean'],
        ]);

        $filled = $classGroup->students()->count();
        if ((int) $validated['capacity'] < $filled) {
            return back()->with('error', 'Kapasitas tidak boleh lebih kecil dari jumlah siswa yang sudah masuk kelas.');
        }

        $classGroup->update([
            'package_id' => $validated['package_id'],
            'name' => strtoupper(trim($validated['name'])),
            'capacity' => $validated['capacity'],
            'is_locked' => $request->boolean('is_locked'),
        ]);

        $logger->log('class_distribution', 'update_class_group', $classGroup, $validated);

        return back()->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(ClassGroup $classGroup, ActivityLogService $logger)
    {
        if ($classGroup->students()->exists()) {
            return back()->with('error', 'Kelas yang masih berisi siswa tidak bisa dihapus. Pindahkan atau kosongkan dulu kelasnya.');
        }

        $logger->log('class_distribution', 'delete_class_group', $classGroup);

        $classGroup->delete();

        return back()->with('success', 'Kelas berhasil dihapus.');
    }

    public function run(
        ClassDistributionService $service,
        ActivityLogService $logger
    ) {
        $service->distribute();

        $logger->log('class_distribution', 'auto_distribute');

        return back()->with('success', 'Distribusi kelas berhasil dijalankan.');
    }

    public function manualMove(
        Request $request,
        ClassDistributionService $service,
        ActivityLogService $logger
    ) {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'class_group_id' => ['required', 'exists:class_groups,id'],
        ]);

        $service->manualMove(
            $validated['student_id'],
            $validated['class_group_id']
        );

        $logger->log('class_distribution', 'manual_move', null, $validated);

        return response()->json([
            'message' => 'Siswa berhasil dipindahkan.',
        ]);
    }

    public function lock(
        ClassDistributionService $service,
        ActivityLogService $logger
    ) {
        $service->lockAll();

        $logger->log('class_distribution', 'lock_final');

        return back()->with('success', 'Data final berhasil dikunci.');
    }
}
