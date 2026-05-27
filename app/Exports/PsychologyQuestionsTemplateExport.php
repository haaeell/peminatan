<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PsychologyQuestionsTemplateExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    public function __construct(private readonly Collection $packages)
    {
    }

    public function headings(): array
    {
        return array_merge(
            ['question_group', 'question', 'image_url', 'option_label', 'option_text', 'is_active'],
            $this->packages->map(fn ($package) => 'weight_' . strtolower($package->code))->all()
        );
    }

    public function array(): array
    {
        $baseWeights = $this->packages->map(fn ($package, $index) => $index === 0 ? 10 : 0)->all();

        return [
            array_merge(
                ['PSI-001', 'Saya lebih suka aktivitas yang melibatkan eksperimen.', '', 'A', 'Sangat sesuai dengan diri saya', '1'],
                $baseWeights
            ),
            array_merge(
                ['PSI-001', 'Saya lebih suka aktivitas yang melibatkan eksperimen.', '', 'B', 'Cukup sesuai dengan diri saya', '1'],
                $baseWeights
            ),
            array_merge(
                ['PSI-001', 'Saya lebih suka aktivitas yang melibatkan eksperimen.', '', 'C', 'Kurang sesuai dengan diri saya', '1'],
                $baseWeights
            ),
            array_merge(
                ['PSI-001', 'Saya lebih suka aktivitas yang melibatkan eksperimen.', '', 'D', 'Tidak sesuai dengan diri saya', '1'],
                $baseWeights
            ),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
