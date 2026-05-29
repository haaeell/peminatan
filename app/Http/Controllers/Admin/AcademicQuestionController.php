<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AcademicQuestionsExport;
use App\Exports\AcademicQuestionsTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\AcademicQuestionsImport;
use App\Models\AcademicQuestion;
use App\Services\ActivityLogService;
use App\Services\QuestionImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AcademicQuestionController extends Controller
{
    public function index()
    {
        $perPage = request('per_page', 10);

        $query = AcademicQuestion::with('options')
            ->orderBy('order')
            ->orderByDesc('id');

        $questions = $perPage === 'all'
            ? $query->get()
            : $query->paginate((int) $perPage)->withQueryString();

        return view('admin.academic-questions.index', compact('questions'));
    }

    public function store(Request $request, ActivityLogService $logger)
    {
        $validated = $request->validate([
            'question' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'options' => ['required', 'array'],
            'options.A' => ['required', 'string'],
            'options.B' => ['required', 'string'],
            'options.C' => ['required', 'string'],
            'options.D' => ['required', 'string'],
            'options.E' => ['required', 'string'],
            'correct_answer' => ['required', 'in:A,B,C,D,E'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated, $request, $logger) {
            $question = AcademicQuestion::create([
                'question' => $validated['question'],
                'image_path' => app(QuestionImageService::class)->storeUploaded($request->file('image')),
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

    public function update(Request $request, AcademicQuestion $academicQuestion, ActivityLogService $logger)
    {
        $validated = $request->validate([
            'question' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'options' => ['required', 'array'],
            'options.A' => ['required', 'string'],
            'options.B' => ['required', 'string'],
            'options.C' => ['required', 'string'],
            'options.D' => ['required', 'string'],
            'options.E' => ['required', 'string'],
            'correct_answer' => ['required', 'in:A,B,C,D,E'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated, $request, $academicQuestion, $logger) {
            $academicQuestion->update([
                'question' => $validated['question'],
                'image_path' => app(QuestionImageService::class)->storeUploaded($request->file('image'), $academicQuestion->image_path),
                'is_active' => $request->boolean('is_active'),
            ]);

            foreach ($validated['options'] as $label => $text) {
                $academicQuestion->options()->updateOrCreate(
                    ['label' => $label],
                    [
                        'option_text' => $text,
                        'is_correct' => $label === $validated['correct_answer'],
                    ]
                );
            }

            $logger->log('academic_question', 'update', $academicQuestion);
        });

        return back()->with('success', 'Soal akademik berhasil diperbarui.');
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
        return Excel::download(
            new AcademicQuestionsTemplateExport(),
            'template_soal_akademik.xlsx'
        );
    }

    public function export()
    {
        return Excel::download(
            new AcademicQuestionsExport(),
            'soal_akademik.xlsx'
        );
    }

    public function import(Request $request, ActivityLogService $logger, QuestionImageService $imageService)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        Excel::import(new AcademicQuestionsImport($imageService), $request->file('file'));
        $logger->log('academic_question', 'import', null, [
            'filename' => $request->file('file')->getClientOriginalName(),
        ]);

        return back()->with('success', 'Import soal akademik berhasil.');
    }
}
