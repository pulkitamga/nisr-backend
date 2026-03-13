<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

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
        // Style the header row
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')
            ->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => '4e73df'],
                ],
            ]);

        // Add borders to all cells
        $lastRow = count($this->branchData) + 2; // +2 for header and total row
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $lastRow)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle('thin');

        // Style the total row
        if (count($this->branchData) > 0) {
            $totalRow = count($this->branchData) + 2;
            $sheet->getStyle('A' . $totalRow . ':' . $sheet->getHighestColumn() . $totalRow)
                ->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => 'f8f9fa'],
                    ],
                ]);
        }

        return [];
    }
}