<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Student;
use App\Models\TestResult;
use App\Models\Violation;

class DashboardController extends Controller
{
    public function index()
    {
        $totalStudents = Student::count();
        $completedStudents = Student::where('status', 'completed')->count();
        $totalViolations = Violation::count();

        $packagePreferences = Package::withCount([
            'firstChoices',
            'secondChoices',
        ])
            ->get();

        $academicDistribution = [
            '0-59' => TestResult::whereBetween('academic_score', [0, 59])->count(),
            '60-69' => TestResult::whereBetween('academic_score', [60, 69])->count(),
            '70-79' => TestResult::whereBetween('academic_score', [70, 79])->count(),
            '80-89' => TestResult::whereBetween('academic_score', [80, 89])->count(),
            '90-100' => TestResult::whereBetween('academic_score', [90, 100])->count(),
        ];

        return view('admin.dashboard.index', compact(
            'totalStudents',
            'completedStudents',
            'totalViolations',
            'packagePreferences',
            'academicDistribution'
        ));
    }
}
