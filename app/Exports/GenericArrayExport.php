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
        foreach ($this->rows as $index => $row) {
            if (count($row) === 1 && str_starts_with((string) $row[0], 'KELAS: ')) {
                $rowNumber = $index + 2;
                $sheet->mergeCells('A' . $rowNumber . ':' . $this->columnLetter(count($this->headings)) . $rowNumber);
                $sheet->getStyle('A' . $rowNumber)->getFont()->setBold(true);
            }
        }

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function columnLetter(int $columnNumber): string
    {
        $letter = '';

        while ($columnNumber > 0) {
            $columnNumber--;
            $letter = chr(65 + ($columnNumber % 26)) . $letter;
            $columnNumber = intdiv($columnNumber, 26);
        }

        return $letter;
    }
}
