<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class FormattedTableExport implements FromArray, WithHeadings, WithEvents, WithCustomStartCell, WithColumnWidths, ShouldAutoSize
{
    public function __construct(
        private readonly array $rows,
        private readonly array $headings,
        private readonly string $title,
        private readonly string $locale,
        private readonly bool $isRtl,
        private readonly array $metaPairs = [],
        private readonly ?string $filterSummary = null,
        private readonly array $columnWidths = [],
        private readonly array $centerColumns = [],
        private readonly array $sumColumns = [],
    ) {
        app()->setLocale($this->locale);
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function columnWidths(): array
    {
        return $this->columnWidths;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setRightToLeft($this->isRtl);
                $sheet->setShowGridlines(false);
                $sheet->freezePane('A7');

                $lastColumn = $this->columnLetter(count($this->headings));
                $dataStartRow = 7;
                $dataEndRow = count($this->rows) > 0 ? $dataStartRow + count($this->rows) - 1 : 6;
                $totalRow = max(7, $dataEndRow + 1);
                $tableEndRow = max(6, $totalRow);
                $textAlignment = $this->isRtl ? Alignment::HORIZONTAL_RIGHT : Alignment::HORIZONTAL_LEFT;

                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->setCellValue('A1', $this->title);

                $this->renderMetaPairs($sheet, $lastColumn, $textAlignment);

                $sheet->setCellValue('A3', translate('filters'));
                $sheet->mergeCells("B3:{$lastColumn}3");
                $sheet->setCellValue('B3', $this->filterSummary ?: '-');

                $sheet->setCellValue("A{$totalRow}", translate('grand_total'));
                $sheet->setCellValue("B{$totalRow}", count($this->rows));

                foreach ($this->sumColumns as $column) {
                    $column = strtoupper((string) $column);
                    $sheet->setCellValue(
                        "{$column}{$totalRow}",
                        count($this->rows) > 0 ? "=SUM({$column}{$dataStartRow}:{$column}{$dataEndRow})" : 0
                    );
                }

                $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 15,
                        'color' => ['argb' => 'FFFFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF0F766E'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle("A2:{$lastColumn}3")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FF0F172A'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFE6FFFB'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFD1D5DB'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle("B3:{$lastColumn}3")->getAlignment()->setHorizontal($textAlignment)->setWrapText(true);

                $sheet->getStyle("A6:{$lastColumn}6")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF239E92'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);

                $sheet->getStyle("A6:{$lastColumn}{$tableEndRow}")->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color' => ['argb' => 'FF0F172A'],
                        ],
                        'inside' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFD1D5DB'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                if (count($this->rows) > 0) {
                    for ($row = $dataStartRow; $row <= $dataEndRow; $row++) {
                        if (($row - $dataStartRow) % 2 === 1) {
                            $sheet->getStyle("A{$row}:{$lastColumn}{$row}")
                                ->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setARGB('FFF8FAFC');
                        }
                    }
                }

                $sheet->getStyle("A{$totalRow}:{$lastColumn}{$totalRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FF0F172A'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFFDE68A'],
                    ],
                ]);

                $sheet->getStyle("A{$dataStartRow}:{$lastColumn}{$tableEndRow}")->getAlignment()->setHorizontal($textAlignment);

                foreach ($this->centerColumns as $column) {
                    $column = strtoupper((string) $column);
                    $sheet->getStyle("{$column}{$dataStartRow}:{$column}{$tableEndRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                $sheet->getStyle("A{$totalRow}:A{$totalRow}")->getAlignment()->setHorizontal($textAlignment);
                $sheet->getStyle("B{$totalRow}:B{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->setAutoFilter("A6:{$lastColumn}" . max(6, $dataEndRow));

                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getRowDimension(2)->setRowHeight(24);
                $sheet->getRowDimension(3)->setRowHeight(38);
                $sheet->getRowDimension(6)->setRowHeight(24);
            },
        ];
    }

    private function renderMetaPairs(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $lastColumn, string $textAlignment): void
    {
        $pairs = array_values(array_filter($this->metaPairs, fn($pair) => (string) ($pair['label'] ?? '') !== ''));

        if ($pairs === []) {
            return;
        }

        $columnCount = $this->columnIndex($lastColumn);
        $pairCount = count($pairs);
        $currentColumnIndex = 1;

        if ($columnCount >= $pairCount * 2) {
            foreach (self::resolveMetaSegments($columnCount, $pairCount) as $index => $segmentWidth) {
                $pair = $pairs[$index];
                $label = (string) ($pair['label'] ?? '');
                $value = (string) ($pair['value'] ?? '');

                $labelColumn = $this->columnLetter($currentColumnIndex);
                $valueStartIndex = min($currentColumnIndex + 1, $columnCount);
                $valueStartColumn = $this->columnLetter($valueStartIndex);
                $valueEndIndex = min($currentColumnIndex + $segmentWidth - 1, $columnCount);
                $valueEndColumn = $this->columnLetter($valueEndIndex);

                $sheet->setCellValueExplicit("{$labelColumn}2", $label, DataType::TYPE_STRING);

                if ($valueStartIndex <= $valueEndIndex) {
                    if ($valueStartIndex < $valueEndIndex) {
                        $sheet->mergeCells("{$valueStartColumn}2:{$valueEndColumn}2");
                    }

                    $sheet->setCellValueExplicit("{$valueStartColumn}2", $value, DataType::TYPE_STRING);
                    $sheet->getStyle("{$valueStartColumn}2:{$valueEndColumn}2")->getAlignment()->setHorizontal($textAlignment);
                }

                $currentColumnIndex += $segmentWidth;
            }

            return;
        }

        foreach (self::resolveMetaSegments($columnCount, $pairCount) as $index => $segmentWidth) {
            $pair = $pairs[$index];
            $label = (string) ($pair['label'] ?? '');
            $value = (string) ($pair['value'] ?? '');
            $segmentStartColumn = $this->columnLetter($currentColumnIndex);
            $segmentEndIndex = min($currentColumnIndex + $segmentWidth - 1, $columnCount);
            $segmentEndColumn = $this->columnLetter($segmentEndIndex);
            $cellValue = $value !== '' ? "{$label}: {$value}" : $label;

            if ($currentColumnIndex < $segmentEndIndex) {
                $sheet->mergeCells("{$segmentStartColumn}2:{$segmentEndColumn}2");
            }

            $sheet->setCellValueExplicit("{$segmentStartColumn}2", $cellValue, DataType::TYPE_STRING);
            $sheet->getStyle("{$segmentStartColumn}2:{$segmentEndColumn}2")->getAlignment()->setHorizontal($textAlignment);

            $currentColumnIndex += $segmentWidth;
        }
    }

    public static function resolveMetaSegments(int $columnCount, int $pairCount): array
    {
        $columnCount = max(1, $columnCount);
        $pairCount = max(1, $pairCount);
        $baseWidth = intdiv($columnCount, $pairCount);
        $extraColumns = $columnCount % $pairCount;
        $segments = [];

        for ($index = 0; $index < $pairCount; $index++) {
            $segments[] = $baseWidth + ($index < $extraColumns ? 1 : 0);
        }

        return $segments;
    }

    private function columnLetter(int $index): string
    {
        $index = max(1, $index);
        $letter = '';

        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $index = (int) (($index - 1) / 26);
        }

        return $letter;
    }

    private function columnIndex(string $letter): int
    {
        $letter = strtoupper($letter);
        $index = 0;

        for ($i = 0; $i < strlen($letter); $i++) {
            $index = ($index * 26) + (ord($letter[$i]) - 64);
        }

        return $index;
    }
}
