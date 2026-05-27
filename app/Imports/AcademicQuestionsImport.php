<?php

namespace App\Imports;

use App\Models\AcademicQuestion;
use App\Services\QuestionImageService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AcademicQuestionsImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    public function __construct(private readonly QuestionImageService $imageService)
    {
    }

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            throw ValidationException::withMessages([
                'file' => ['File import soal akademik kosong.'],
            ]);
        }

        $nextOrder = ((int) AcademicQuestion::max('order')) + 1;

        DB::transaction(function () use ($rows, &$nextOrder) {
            foreach ($rows as $index => $row) {
                $questionText = trim((string) ($row['question'] ?? ''));
                $correctAnswer = strtoupper(trim((string) ($row['correct_answer'] ?? '')));
                $options = [
                    'A' => trim((string) ($row['option_a'] ?? '')),
                    'B' => trim((string) ($row['option_b'] ?? '')),
                    'C' => trim((string) ($row['option_c'] ?? '')),
                    'D' => trim((string) ($row['option_d'] ?? '')),
                ];

                if ($questionText === '') {
                    $this->rowError($index, 'Kolom question wajib diisi.');
                }

                foreach ($options as $label => $text) {
                    if ($text === '') {
                        $this->rowError($index, "Kolom option_{$label} wajib diisi.");
                    }
                }

                if (!in_array($correctAnswer, ['A', 'B', 'C', 'D'], true)) {
                    $this->rowError($index, 'Kolom correct_answer harus berisi A, B, C, atau D.');
                }

                $question = AcademicQuestion::create([
                    'question' => $questionText,
                    'image_path' => $this->imageService->storeFromUrl($row['image_url'] ?? null),
                    'order' => $nextOrder++,
                    'is_active' => $this->toBoolean($row['is_active'] ?? 1),
                ]);

                foreach ($options as $label => $text) {
                    $question->options()->create([
                        'label' => $label,
                        'option_text' => $text,
                        'is_correct' => $label === $correctAnswer,
                    ]);
                }
            }
        });
    }

    private function rowError(int $index, string $message): never
    {
        throw ValidationException::withMessages([
            'file' => ['Baris ' . ($index + 2) . ': ' . $message],
        ]);
    }

    private function toBoolean(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? ((string) $value === '1');
    }
}
