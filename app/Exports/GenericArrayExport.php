<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GenericArrayExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    public function __construct(
        private readonly array $headings,
        private readonly array $rows
    ) {
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
