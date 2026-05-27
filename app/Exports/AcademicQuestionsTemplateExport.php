<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AcademicQuestionsTemplateExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'question',
            'image_url',
            'option_a',
            'option_b',
            'option_c',
            'option_d',
            'correct_answer',
            'is_active',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Contoh soal akademik bergambar?',
                '',
                'Pilihan A',
                'Pilihan B',
                'Pilihan C',
                'Pilihan D',
                'A',
                '1',
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
