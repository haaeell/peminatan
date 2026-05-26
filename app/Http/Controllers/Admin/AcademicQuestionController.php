<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicQuestion;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicQuestionController extends Controller
{
    public function index()
    {
        $questions = AcademicQuestion::with('options')
            ->latest()
            ->paginate(10);

        return view('admin.academic-questions.index', compact('questions'));
    }

    public function store(Request $request, ActivityLogService $logger)
    {
        $validated = $request->validate([
            'question' => ['required', 'string'],
            'options' => ['required', 'array'],
            'options.A' => ['required', 'string'],
            'options.B' => ['required', 'string'],
            'options.C' => ['required', 'string'],
            'options.D' => ['required', 'string'],
            'correct_answer' => ['required', 'in:A,B,C,D'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated, $request, $logger) {
            $question = AcademicQuestion::create([
                'question' => $validated['question'],
                'order' => AcademicQuestion::max('order') + 1,
                'is_active' => $request->boolean('is_active'),
            ]);

            foreach ($validated['options'] as $label => $text) {
                $question->options()->create([
                    'label' => $label,
                    'option_text' => $text,
                    'is_correct' => $label === $validated['correct_answer'],
                ]);
            }

            $logger->log('academic_question', 'create', $question);
        });

        return back()->with('success', 'Soal akademik berhasil dibuat.');
    }

    public function destroy(AcademicQuestion $academicQuestion, ActivityLogService $logger)
    {
        $logger->log('academic_question', 'delete', $academicQuestion);

        $academicQuestion->delete();

        return back()->with('success', 'Soal akademik berhasil dihapus.');
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:academic_questions,id'],
        ]);

        AcademicQuestion::whereIn('id', $validated['ids'])->delete();

        return back()->with('success', 'Soal terpilih berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_soal_akademik.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'question',
                'option_a',
                'option_b',
                'option_c',
                'option_d',
                'correct_answer',
            ]);

            fputcsv($file, [
                'Contoh soal akademik?',
                'Jawaban A',
                'Jawaban B',
                'Jawaban C',
                'Jawaban D',
                'A',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function export()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="soal_akademik.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'question',
                'option_a',
                'option_b',
                'option_c',
                'option_d',
                'correct_answer',
            ]);

            AcademicQuestion::with('options')->chunk(100, function ($questions) use ($file) {
                foreach ($questions as $question) {
                    $options = $question->options->keyBy('label');

                    fputcsv($file, [
                        $question->question,
                        $options['A']->option_text ?? '',
                        $options['B']->option_text ?? '',
                        $options['C']->option_text ?? '',
                        $options['D']->option_text ?? '',
                        $question->options->firstWhere('is_correct', true)?->label,
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $path = $request->file('file')->getRealPath();
        $file = fopen($path, 'r');

        $header = fgetcsv($file);

        DB::transaction(function () use ($file) {
            while (($row = fgetcsv($file)) !== false) {
                [$questionText, $a, $b, $c, $d, $correct] = $row;

                $question = AcademicQuestion::create([
                    'question' => $questionText,
                    'order' => AcademicQuestion::max('order') + 1,
                    'is_active' => true,
                ]);

                foreach (
                    [
                        'A' => $a,
                        'B' => $b,
                        'C' => $c,
                        'D' => $d,
                    ] as $label => $text
                ) {
                    $question->options()->create([
                        'label' => $label,
                        'option_text' => $text,
                        'is_correct' => strtoupper($correct) === $label,
                    ]);
                }
            }
        });

        fclose($file);

        return back()->with('success', 'Import soal akademik berhasil.');
    }
}
