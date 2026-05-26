<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AcademicQuestion;
use App\Models\AcademicQuestionOption;
use App\Models\StudentAcademicAnswer;
use App\Models\TestResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicTestController extends Controller
{
    public function index()
    {
        $student = auth()->user()->student;

        abort_if(!$student->selfie, 403, 'Selfie wajib dilakukan sebelum tes.');

        $questions = AcademicQuestion::where('is_active', true)
            ->with('options')
            ->orderBy('order')
            ->get();

        $answers = $student->academicAnswers()
            ->pluck('academic_question_option_id', 'academic_question_id')
            ->toArray();

        return view('siswa.academic.index', compact('student', 'questions', 'answers'));
    }

    public function autosave(Request $request)
    {
        $validated = $request->validate([
            'academic_question_id' => ['required', 'exists:academic_questions,id'],
            'academic_question_option_id' => ['required', 'exists:academic_question_options,id'],
        ]);

        $student = auth()->user()->student;

        $option = AcademicQuestionOption::where('id', $validated['academic_question_option_id'])
            ->where('academic_question_id', $validated['academic_question_id'])
            ->firstOrFail();

        StudentAcademicAnswer::updateOrCreate(
            [
                'student_id' => $student->id,
                'academic_question_id' => $validated['academic_question_id'],
            ],
            [
                'academic_question_option_id' => $option->id,
                'is_correct' => $option->is_correct,
                'answered_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Jawaban tersimpan.',
        ]);
    }

    public function submit()
    {
        $student = auth()->user()->student;

        DB::transaction(function () use ($student) {
            $total = AcademicQuestion::where('is_active', true)->count();

            $correct = $student->academicAnswers()
                ->where('is_correct', true)
                ->count();

            $score = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

            TestResult::updateOrCreate(
                ['student_id' => $student->id],
                ['academic_score' => $score]
            );

            $student->update(['status' => 'psychology_test']);
        });

        return response()->json([
            'message' => 'Tes akademik selesai.',
            'redirect_url' => route('siswa.psychology.index'),
        ]);
    }
}
