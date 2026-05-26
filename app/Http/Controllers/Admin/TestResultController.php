<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\ClassStudent;
use App\Models\Package;
use App\Models\TestResult;
use App\Services\ClassDistributionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestResultController extends Controller
{
    public function index()
    {
        $results = TestResult::with([
            'student.user',
            'student.biodata',
            'student.selfie',
            'student.packageChoice.firstPackage',
            'student.packageChoice.secondPackage',
            'student.classStudent.classGroup',
            'recommendedPackage',
            'finalPackage',
        ])
            ->latest()
            ->paginate(25);

        $packages = Package::where('is_active', true)->get();
        $classGroups = ClassGroup::with('package')->orderBy('name')->get();

        return view('admin.test-results.index', compact(
            'results',
            'packages',
            'classGroups'
        ));
    }

    public function distribute(ClassDistributionService $service)
    {
        $service->distribute();

        return back()->with('success', 'Siswa berhasil dibagi otomatis ke kelas berdasarkan jurusan.');
    }

    public function manualUpdate(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'final_package_id' => ['required', 'exists:packages,id'],
            'class_group_id' => ['required', 'exists:class_groups,id'],
        ]);

        DB::transaction(function () use ($validated) {
            $classGroup = ClassGroup::findOrFail($validated['class_group_id']);

            if ($classGroup->students()->count() >= $classGroup->capacity) {
                abort(422, 'Kapasitas kelas sudah penuh.');
            }

            TestResult::where('student_id', $validated['student_id'])->update([
                'final_package_id' => $validated['final_package_id'],
            ]);

            ClassStudent::updateOrCreate(
                ['student_id' => $validated['student_id']],
                [
                    'class_group_id' => $validated['class_group_id'],
                    'package_id' => $validated['final_package_id'],
                    'is_manual_override' => true,
                ]
            );
        });

        return back()->with('success', 'Penempatan siswa berhasil diubah manual.');
    }

    public function export()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="hasil_tes.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Nama',
                'NISN',
                'Kelas Asal',
                'Nilai Akademik',
                'Rekomendasi Psikotes',
                'Jurusan Final',
                'Pilihan 1',
                'Pilihan 2',
            ]);

            TestResult::with([
                'student.packageChoice.firstPackage',
                'student.packageChoice.secondPackage',
                'recommendedPackage',
                'finalPackage',
            ])->chunk(100, function ($results) use ($file) {
                foreach ($results as $result) {
                    fputcsv($file, [
                        $result->student?->name,
                        $result->student?->nisn,
                        $result->student?->origin_class,
                        $result->academic_score,
                        $result->recommendedPackage?->name,
                        $result->finalPackage?->name,
                        $result->student?->packageChoice?->firstPackage?->name,
                        $result->student?->packageChoice?->secondPackage?->name,
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
