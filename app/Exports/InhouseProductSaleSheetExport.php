<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InhouseProductSaleSheetExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithTitle, WithEvents
{
    public function __construct(
        private readonly string $title,
        private readonly array $headings,
        private readonly array $rows,
        private readonly bool $isRtl = false,
    ) {}

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return mb_substr($this->title, 0, 31);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF239E92'],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // 1. THIS REMOVES THE GRIDLINES (Makes the page white)
                $event->sheet->getDelegate()->setShowGridlines(false);

                $lastColumn = $this->getColumnLetter(count($this->headings));
                $lastRow = max(1, count($this->rows) + 1);
                $range = "A1:{$lastColumn}{$lastRow}";

                // 2. APPLY BLACK OUTSIDE BORDER AND LIGHT INSIDE BORDERS
                $event->sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color' => ['argb' => 'FF000000'], // Pure Black
                        ],
                        'inside' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFD1D5DB'], // Light Gray
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $event->sheet->getDelegate()->setRightToLeft($this->isRtl);
            },
        ];
    }

    private function getColumnLetter(int $index): string
    {
        $index = max(1, $index);
        $letter = '';
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $index = (int)(($index - 1) / 26);
        }

        return $letter;
    }
}
