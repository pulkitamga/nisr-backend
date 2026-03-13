<?php

namespace App\Exports;

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\Exportable;

class ProductListWithPriceRangeExport implements FromView, ShouldAutoSize, WithStyles, WithColumnWidths
{
    use Exportable;
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('file-exports.product-wholesale-price-list', [
            'data' => $this->data,
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10, 
            'B' => 30, 
            'C' => 25,
            'D' => 25,
            'E' => 20,
            'F' => 15,
            'G' => 15,
            'H' => 20,
        ];
    }


    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFF');

        // Set background color for header row
        $sheet->getStyle('A1:H1')->getFill()->applyFromArray([
            'fillType' => 'solid',
            'rotation' => 0,
            'color' => ['rgb' => '063C93'],
        ]);

        // Hide gridlines
        $sheet->setShowGridlines(false);

        // Ensure $this->data is iterable and countable
        $rowCount = $this->data[count($this->data) - 1]['total_rows'] ? $this->data[count($this->data) - 1]['total_rows'] + 1 : 0 ;
        return [
            'A1:H' . $rowCount => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
                'alignment' => [
                    'wrapText' => true,
                ],
            ],
        ];
    }
}
 