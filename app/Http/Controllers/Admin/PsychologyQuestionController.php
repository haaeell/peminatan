<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PsychologyQuestionsExport;
use App\Exports\PsychologyQuestionsTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\PsychologyQuestionsImport;
use App\Models\Package;
use App\Models\PsychologyQuestion;
use App\Services\ActivityLogService;
use App\Services\QuestionImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class PsychologyQuestionController extends Controller
{
    public function index()
    {
        $perPage = request('per_page', 10);

        $query = PsychologyQuestion::with('options.weights.package')
            ->orderBy('order')
            ->orderByDesc('id');

        $questions = $perPage === 'all'
            ? $query->get()
            : $query->paginate((int) $perPage)->withQueryString();

        $packages = Package::where('is_active', true)->get();

        return view('admin.psychology-questions.index', compact(
            'questions',
            'packages'
        ));
    }

    public function store(Request $request, ActivityLogService $logger)
    {
        $validated = $request->validate([
            'question' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'options' => ['required', 'array'],
            'options.A.text' => ['required', 'string'],
            'options.B.text' => ['required', 'string'],
            'options.C.text' => ['required', 'string'],
            'options.D.text' => ['required', 'string'],
            'options.*.weights' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated, $request, $logger) {
            $question = PsychologyQuestion::create([
                'question' => $validated['question'],
                'image_path' => app(QuestionImageService::class)->storeUploaded($request->file('image')),
                'order' => PsychologyQuestion::max('order') + 1,
                'is_active' => $request->boolean('is_active'),
            ]);

            foreach ($validated['options'] as $label => $optionData) {
                $option = $question->options()->create([
                    'label' => $label,
                    'option_text' => $optionData['text'],
                ]);

                foreach (($optionData['weights'] ?? []) as $packageId => $weight) {
                    if ($weight === null || $weight === '') {
                        continue;
                    }

                    $option->weights()->create([
                        'package_id' => $packageId,
                        'weight' => (int) $weight,
                    ]);
                }
            }

            $logger->log('psychology_question', 'create', $question);
        });

        return back()->with('success', 'Soal psikotes berhasil dibuat.');
    }

    public function update(Request $request, PsychologyQuestion $psychologyQuestion, ActivityLogService $logger)
    {
        $validated = $request->validate([
            'question' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'options' => ['required', 'array'],
            'options.A.text' => ['required', 'string'],
            'options.B.text' => ['required', 'string'],
            'options.C.text' => ['required', 'string'],
            'options.D.text' => ['required', 'string'],
            'options.*.weights' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated, $request, $psychologyQuestion, $logger) {
            $psychologyQuestion->update([
                'question' => $validated['question'],
                'image_path' => app(QuestionImageService::class)->storeUploaded($request->file('image'), $psychologyQuestion->image_path),
                'is_active' => $request->boolean('is_active'),
            ]);

            foreach ($validated['options'] as $label => $optionData) {
                $option = $psychologyQuestion->options()->updateOrCreate(
                    ['label' => $label],
                    ['option_text' => $optionData['text']]
                );

                foreach (Package::where('is_active', true)->pluck('id') as $packageId) {
                    $weight = $optionData['weights'][$packageId] ?? 0;

                    $option->weights()->updateOrCreate(
                        ['package_id' => $packageId],
                        ['weight' => (int) $weight]
                    );
                }
            }

            $logger->log('psychology_question', 'update', $psychologyQuestion);
        });

        return back()->with('success', 'Soal psikotes berhasil diperbarui.');
    }

    public function destroy(PsychologyQuestion $psychologyQuestion, ActivityLogService $logger)
    {
        $logger->log('psychology_question', 'delete', $psychologyQuestion);

        $psychologyQuestion->delete();

        return back()->with('success', 'Soal psikotes berhasil dihapus.');
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:psychology_questions,id'],
        ]);

        PsychologyQuestion::whereIn('id', $validated['ids'])->delete();

        return back()->with('success', 'Soal psikotes terpilih berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        $packages = Package::where('is_active', true)->get();

        return Excel::download(
            new PsychologyQuestionsTemplateExport($packages),
            'template_soal_psikotes.xlsx'
        );
    }

    public function export()
    {
        $packages = Package::where('is_active', true)->get();

        return Excel::download(
            new PsychologyQuestionsExport($packages),
            'soal_psikotes.xlsx'
        );
    }

    public function import(Request $request, ActivityLogService $logger, QuestionImageService $imageService)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        Excel::import(new PsychologyQuestionsImport($imageService), $request->file('file'));
        $logger->log('psychology_question', 'import', null, [
            'filename' => $request->file('file')->getClientOriginalName(),
        ]);

        return back()->with('success', 'Import soal psikotes berhasil.');
    }
}
