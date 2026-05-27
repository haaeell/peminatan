<?php

namespace App\Exports;

use App\Models\PsychologyQuestion;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PsychologyQuestionsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    public function __construct(private readonly Collection $packages)
    {
    }

    public function collection()
    {
        return PsychologyQuestion::with('options.weights')
            ->orderBy('order')
            ->get()
            ->flatMap(function ($question) {
                return $question->options->map(function ($option) use ($question) {
                    $row = [
                        'question_group' => 'PSI-' . str_pad((string) $question->order, 3, '0', STR_PAD_LEFT),
                        'question' => $question->question,
                        'image_url' => $question->image_url,
                        'option_label' => $option->label,
                        'option_text' => $option->option_text,
                        'is_active' => $question->is_active ? 1 : 0,
                    ];

                    foreach ($this->packages as $package) {
                        $row['weight_' . strtolower($package->code)] = $option->weights
                            ->firstWhere('package_id', $package->id)
                            ?->weight ?? 0;
                    }

                    return $row;
                });
            });
    }

    public function headings(): array
    {
        return array_merge(
            ['question_group', 'question', 'image_url', 'option_label', 'option_text', 'is_active'],
            $this->packages->map(fn ($package) => 'weight_' . strtolower($package->code))->all()
        );
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
