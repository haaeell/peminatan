<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PsychologyQuestion;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PsychologyQuestionController extends Controller
{
    public function index()
    {
        $questions = PsychologyQuestion::with('options.weights.package')
            ->latest()
            ->paginate(10);

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

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_soal_psikotes.csv"',
        ];

        $callback = function () use ($packages) {
            $file = fopen('php://output', 'w');

            $header = ['question', 'option_label', 'option_text'];

            foreach ($packages as $package) {
                $header[] = 'weight_' . $package->code;
            }

            fputcsv($file, $header);

            fputcsv($file, [
                'Saya lebih suka aktivitas...',
                'A',
                'Eksperimen dan sains',
                10,
                3,
                0,
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function export()
    {
        $packages = Package::where('is_active', true)->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="soal_psikotes.csv"',
        ];

        $callback = function () use ($packages) {
            $file = fopen('php://output', 'w');

            $header = ['question', 'option_label', 'option_text'];

            foreach ($packages as $package) {
                $header[] = 'weight_' . $package->code;
            }

            fputcsv($file, $header);

            PsychologyQuestion::with('options.weights.package')->chunk(100, function ($questions) use ($file, $packages) {
                foreach ($questions as $question) {
                    foreach ($question->options as $option) {
                        $row = [
                            $question->question,
                            $option->label,
                            $option->option_text,
                        ];

                        foreach ($packages as $package) {
                            $weight = $option->weights
                                ->firstWhere('package_id', $package->id)
                                ?->weight ?? 0;

                            $row[] = $weight;
                        }

                        fputcsv($file, $row);
                    }
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

        $packages = Package::where('is_active', true)->get();

        $path = $request->file('file')->getRealPath();
        $file = fopen($path, 'r');

        $header = fgetcsv($file);

        DB::transaction(function () use ($file, $packages) {
            $questionMap = [];

            while (($row = fgetcsv($file)) !== false) {
                $questionText = $row[0];
                $label = strtoupper($row[1]);
                $optionText = $row[2];

                if (!isset($questionMap[$questionText])) {
                    $questionMap[$questionText] = PsychologyQuestion::create([
                        'question' => $questionText,
                        'order' => PsychologyQuestion::max('order') + 1,
                        'is_active' => true,
                    ]);
                }

                $question = $questionMap[$questionText];

                $option = $question->options()->create([
                    'label' => $label,
                    'option_text' => $optionText,
                ]);

                foreach ($packages as $index => $package) {
                    $weight = $row[3 + $index] ?? 0;

                    $option->weights()->create([
                        'package_id' => $package->id,
                        'weight' => (int) $weight,
                    ]);
                }
            }
        });

        fclose($file);

        return back()->with('success', 'Import soal psikotes berhasil.');
    }
}
