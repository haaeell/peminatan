<?php

namespace App\Imports;

use App\Models\Package;
use App\Models\PsychologyQuestion;
use App\Services\QuestionImageService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PsychologyQuestionsImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    public function __construct(private readonly QuestionImageService $imageService)
    {
    }

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            throw ValidationException::withMessages([
                'file' => ['File import soal psikotes kosong.'],
            ]);
        }

        $packages = Package::where('is_active', true)->get()->keyBy(fn ($package) => 'weight_' . strtolower($package->code));
        $groupedRows = $rows->groupBy(function ($row, $index) {
            $group = trim((string) ($row['question_group'] ?? ''));

            return $group !== '' ? $group : '__row_' . $index;
        });

        $nextOrder = ((int) PsychologyQuestion::max('order')) + 1;

        DB::transaction(function () use ($groupedRows, $packages, &$nextOrder) {
            foreach ($groupedRows as $groupKey => $groupRows) {
                $firstRow = $groupRows->first();
                $questionText = trim((string) ($firstRow['question'] ?? ''));

                if ($questionText === '') {
                    $this->groupError($groupKey, 'Kolom question wajib diisi.');
                }

                $labels = $groupRows
                    ->map(fn ($row) => strtoupper(trim((string) ($row['option_label'] ?? ''))))
                    ->filter()
                    ->values()
                    ->all();

                sort($labels);

                if ($labels !== ['A', 'B', 'C', 'D']) {
                    $this->groupError($groupKey, 'Setiap soal harus memiliki option_label A, B, C, dan D masing-masing satu kali.');
                }

                $question = PsychologyQuestion::create([
                    'question' => $questionText,
                    'image_path' => $this->imageService->storeFromUrl($firstRow['image_url'] ?? null),
                    'order' => $nextOrder++,
                    'is_active' => $this->toBoolean($firstRow['is_active'] ?? 1),
                ]);

                foreach ($groupRows as $rowIndex => $row) {
                    $label = strtoupper(trim((string) ($row['option_label'] ?? '')));
                    $optionText = trim((string) ($row['option_text'] ?? ''));

                    if ($optionText === '') {
                        $this->rowError($rowIndex, 'Kolom option_text wajib diisi.');
                    }

                    $option = $question->options()->create([
                        'label' => $label,
                        'option_text' => $optionText,
                    ]);

                    foreach ($packages as $heading => $package) {
                        $option->weights()->create([
                            'package_id' => $package->id,
                            'weight' => (int) ($row[$heading] ?? 0),
                        ]);
                    }
                }
            }
        });
    }

    private function groupError(string $groupKey, string $message): never
    {
        throw ValidationException::withMessages([
            'file' => ['Grup soal ' . $groupKey . ': ' . $message],
        ]);
    }

    private function rowError(int|string $index, string $message): never
    {
        throw ValidationException::withMessages([
            'file' => ['Baris ' . ((int) $index + 2) . ': ' . $message],
        ]);
    }

    private function toBoolean(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? ((string) $value === '1');
    }
}
