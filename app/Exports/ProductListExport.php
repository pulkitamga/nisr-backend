<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductListExport implements FromView, ShouldAutoSize, WithStyles, WithColumnWidths, WithHeadings, WithEvents
{
    use Exportable;
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('file-exports.product-list', [
            'data' => $this->data,
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'C' => 25,
            'D' => 25,
            'F' => 50,
            'G' => 20,
            'H' => 20,
            'I' => 20,
            'J' => 20,
            'R' => 25,
        ];
    }

    private function getDrawingLabel(object $item, int $index): string
    {
        $name = method_exists($item, 'getRawOriginal')
            ? $item->getRawOriginal('name')
            : ($item->name ?? null);

        $name = is_scalar($name) ? trim((string)$name) : '';
        if ($name !== '') {
            return $name;
        }

        $code = is_scalar($item->code ?? null) ? trim((string)$item->code) : '';
        if ($code !== '') {
            return $code;
        }

        return 'product-' . ($index + 1);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet->getStyle('A3:S3')->getFont()->setBold(true)->getColor()
            ->setARGB('FFFFFF');

        $sheet->getStyle('A3:S3')->getFill()->applyFromArray([
            'fillType' => 'solid',
            'rotation' => 0,
            'color' => ['rgb' => '239e92'],
        ]);

        $sheet->setShowGridlines(false);
        return [
            // Define the style for cells with data
            'A1:S' . $this->data['products']->count() + 3 => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => '000000'], // Specify the color of the border (optional)
                    ],
                ],
                'alignment' => [
                    'wrapText' => true,
                ],
            ],
        ];
    }
    public function setImage($workSheet)
    {
        $this->data['products']->each(function ($item, $index) use ($workSheet) {
            $tempImagePath = null;
            $thumbnail = $item->thumbnail_full_url ?? [];
            $thumbnailKey = is_array($thumbnail) ? ($thumbnail['key'] ?? null) : null;
            $thumbnailPath = is_array($thumbnail) ? ($thumbnail['path'] ?? null) : null;
            $filePath = $thumbnailKey ? 'product/thumbnail/' . $thumbnailKey : null;
            $fileCheck = fileCheck(disk: 'public', path: $filePath);
            if ($thumbnailPath && !$fileCheck) {
                $tempImagePath = getTemporaryImageForExport($thumbnailPath);
                $imagePath = getImageForExport($thumbnailPath);
                $drawing = new MemoryDrawing();
                $drawing->setImageResource($imagePath);
            } else {
                $drawing = new Drawing();
                $drawing->setPath($filePath && is_file(storage_path('app/public/' . $filePath)) ? storage_path('app/public/' . $filePath) : public_path('assets/back-end/img/products.png'));
            }
            $drawingLabel = $this->getDrawingLabel($item, $index);
            $drawing->setName($drawingLabel);
            $drawing->setDescription($drawingLabel);
            $drawing->setHeight(50);
            $drawing->setOffsetX(45);
            $drawing->setOffsetY(70);
            $drawing->setResizeProportional(true);
            $index += 4;
            $drawing->setCoordinates("B$index");
            $drawing->setWorksheet($workSheet);
            if ($tempImagePath) {
                imagedestroy($tempImagePath);
            }
        });
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:S1')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $event->sheet->getStyle('A3:S' . $this->data['products']->count() + 3)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $event->sheet->getStyle('A2:S2')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $event->sheet->mergeCells('A1:S1');
                $event->sheet->mergeCells('A2:B2');
                $event->sheet->mergeCells('C2:S2');
                $event->sheet->mergeCells('D2:S2');
                if ($this->data['type'] != 'seller') {
                    $event->sheet->mergeCells('F3:G3');
                    $this->data['products']->each(function ($item, $index) use ($event) {
                        $index += 4;
                        $event->sheet->mergeCells("F$index:G$index");
                    });
                }
                $event->sheet->getRowDimension(2)->setRowHeight(100);
                $event->sheet->getRowDimension(1)->setRowHeight(30);
                $event->sheet->getRowDimension(3)->setRowHeight(30);
                $event->sheet->getDefaultRowDimension()->setRowHeight(150);

                $workSheet = $event->sheet->getDelegate();
                $this->setImage($workSheet);
            },
        ];
    }
    public function headings(): array
    {
        return [
            '1'
        ];
    }
}
