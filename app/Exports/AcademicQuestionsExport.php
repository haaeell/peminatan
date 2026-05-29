<?php

namespace App\Exports;

use App\Models\AcademicQuestion;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AcademicQuestionsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return AcademicQuestion::with('options')
            ->orderBy('order')
            ->get();
    }

    public function headings(): array
    {
        return [
            'question',
            'image_url',
            'option_a',
            'option_b',
            'option_c',
            'option_d',
            'option_e',
            'correct_answer',
            'is_active',
        ];
    }

    public function map($question): array
    {
        $options = $question->options->keyBy('label');

        return [
            $question->question,
            $question->image_url,
            $options->get('A')?->option_text,
            $options->get('B')?->option_text,
            $options->get('C')?->option_text,
            $options->get('D')?->option_text,
            $options->get('E')?->option_text,
            $question->options->firstWhere('is_correct', true)?->label,
            $question->is_active ? 1 : 0,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
