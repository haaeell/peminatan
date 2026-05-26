<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Student;
use App\Services\ActivityLogService;
use App\Services\ClassDistributionService;
use Illuminate\Http\Request;

class ClassDistributionController extends Controller
{
    public function index()
    {
        $classGroups = ClassGroup::with([
            'package',
            'students.student.result',
            'students.student.packageChoice.firstPackage',
            'students.student.packageChoice.secondPackage',
        ])
            ->orderBy('name')
            ->get();

        $unassignedStudents = Student::whereDoesntHave('classStudent')
            ->where('status', 'completed')
            ->with('result')
            ->get();

        return view('admin.class-distribution.index', compact(
            'classGroups',
            'unassignedStudents'
        ));
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
