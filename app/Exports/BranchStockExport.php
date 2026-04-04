<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BranchStockExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $branchData;
    protected $product;
    protected $filters;

    public function __construct(array $branchData, $product = null, $filters = [])
    {
        $this->branchData = $branchData;
        $this->product = $product;
        $this->filters = $filters;
    }

    public function array(): array
    {
        $exportArray = [];
        $serialNumber = 1;

        foreach ($this->branchData as $branch) {
            $row = [
                $serialNumber++, // Add serial number
                $branch['branch_name'],
                $branch['current_stock'],
            ];

            // Add product name if product filter is applied
            if ($this->product) {
                $row[] = $this->product->name;
            }

            // Add variation if variation filter is applied
            if (!empty($this->filters['variation_type'])) {
                $row[] = $this->filters['variation_type'];
            }

            $exportArray[] = $row;
        }

        // Add total row
        if (count($this->branchData) > 0) {
            $totalRow = ['Total', '', array_sum(array_column($this->branchData, 'current_stock'))];

            // Add empty cells for product and variation columns if they exist
            if ($this->product) {
                $totalRow[] = '';
            }
            if (!empty($this->filters['variation_type'])) {
                $totalRow[] = '';
            }

            $exportArray[] = $totalRow;
        }

        return $exportArray;
    }

    public function headings(): array
    {
        $headings = ['#', 'Branch Name', 'Stock Quantity'];

        // Add product column if product filter is applied
        if ($this->product) {
            $headings[] = 'Product';
        }

        // Add variation column if variation filter is applied
        if (!empty($this->filters['variation_type'])) {
            $headings[] = 'Variation';
        }

        return $headings;
    }

    public function title(): string
    {
        return 'Branch Stock Report';
    }

    public function styles(Worksheet $sheet)
    {
        //  FIX: Limit header color to only used columns
        $lastColumn = $sheet->getHighestColumn();

        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF239E92'], // Your Green color
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // 1. Hide Gridlines for clean white background
                $event->sheet->getDelegate()->setShowGridlines(false);

                $lastColumn = $event->sheet->getHighestColumn();
                $lastRow = count($this->branchData) + (count($this->branchData) > 0 ? 2 : 1);
                $range = "A1:{$lastColumn}{$lastRow}";

                // 2. Apply Thick Black Outline and Thin Inside Borders
                $event->sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color' => ['argb' => 'FF000000'],
                        ],
                        'inside' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFD1D5DB'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // 3. Bold the total row specifically
                if (count($this->branchData) > 0) {
                    $event->sheet->getStyle("A{$lastRow}:{$lastColumn}{$lastRow}")
                        ->getFont()->setBold(true);
                }
            },
        ];
    }
}
