<?php

namespace App\Exports;

use App\Models\WarrantyClaim;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class WarrantyClaimListExport implements FromCollection, WithMapping, WithHeadings, WithEvents, WithCustomStartCell, WithColumnWidths, ShouldAutoSize
{
    private int $rowNumber = 0;

    public function __construct(
        private readonly Collection $claims,
        private readonly string $locale,
        private readonly bool $isRtl,
        private readonly string $title,
        private readonly string $dateRangeLabel,
        private readonly string $filterSummary,
        private readonly CarbonInterface $exportedAt,
    ) {
        app()->setLocale($this->locale);
    }

    public function collection(): Collection
    {
        return $this->claims;
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function headings(): array
    {
        return [
            translate('SL'),
            translate('claim_number'),
            translate('serial'),
            translate('status'),
            translate('customer'),
            translate('product'),
            translate('distributor'),
            translate('branch'),
            translate('activation_method'),
            translate('submitted_at'),
            translate('sla_due'),
            translate('resolved_at'),
            translate('reopen_count'),
            translate('Technician'),
            translate('sla_result'),
        ];
    }

    public function map($claim): array
    {
        /** @var WarrantyClaim $claim */
        $this->rowNumber++;

        $submittedAt = $claim->submitted_at ?? $claim->created_at;
        $resolutionDue = $claim->resolution_due;
        $resolvedAt = $claim->resolved_at;

        return [
            $this->rowNumber,
            (string) $claim->claim_number,
            (string) $claim->serial_number,
            $this->translateClaimStatus((string) $claim->status),
            $this->resolveCustomerName($claim),
            $this->resolveProductName($claim),
            $claim->warranty?->distributor?->company_name ?? '-',
            $this->resolveBranchName($claim),
            translate((string) ($claim->warranty?->activation_method ?: 'unknown')),
            $submittedAt ? $submittedAt->format('Y-m-d H:i') : '-',
            $resolutionDue ? $resolutionDue->format('Y-m-d H:i') : '-',
            $resolvedAt ? $resolvedAt->format('Y-m-d H:i') : '-',
            (int) ($claim->reopen_count ?? 0),
            $claim->technician?->name ?? '-',
            $this->resolveSlaResult($claim),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 20,
            'C' => 18,
            'D' => 20,
            'E' => 26,
            'F' => 24,
            'G' => 24,
            'H' => 20,
            'I' => 22,
            'J' => 20,
            'K' => 20,
            'L' => 20,
            'M' => 14,
            'N' => 20,
            'O' => 18,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setRightToLeft($this->isRtl);
                $sheet->setShowGridlines(false);
                $sheet->freezePane('A7');

                $lastColumn = 'O';
                $dataStartRow = 7;
                $dataEndRow = $this->claims->isEmpty()
                    ? $dataStartRow - 1
                    : $dataStartRow + $this->claims->count() - 1;
                $totalRow = $dataEndRow + 1;
                $tableEndRow = max(6, $totalRow);
                $textAlignment = $this->isRtl ? Alignment::HORIZONTAL_RIGHT : Alignment::HORIZONTAL_LEFT;
                $metaDate = $this->exportedAt->translatedFormat('Y-m-d H:i');
                $totalReopens = (int) $this->claims->sum(fn(WarrantyClaim $claim) => (int) ($claim->reopen_count ?? 0));

                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->setCellValue('A1', $this->title);

                $sheet->setCellValueExplicit('A2', translate('report_period'), DataType::TYPE_STRING);
                $sheet->mergeCells('B2:D2');
                $sheet->setCellValueExplicit('B2', $this->dateRangeLabel, DataType::TYPE_STRING);

                $sheet->setCellValueExplicit('E2', translate('exported_at'), DataType::TYPE_STRING);
                $sheet->mergeCells('F2:H2');
                $sheet->setCellValueExplicit('F2', $metaDate, DataType::TYPE_STRING);

                $sheet->setCellValueExplicit('I2', translate('count'), DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('J2', (string) $this->claims->count(), DataType::TYPE_STRING);

                $sheet->setCellValueExplicit('K2', translate('reopen_count'), DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('L2', (string) $totalReopens, DataType::TYPE_STRING);

                $sheet->setCellValueExplicit('A3', translate('filters'), DataType::TYPE_STRING);
                $sheet->mergeCells("B3:{$lastColumn}3");
                $sheet->setCellValueExplicit('B3', $this->filterSummary, DataType::TYPE_STRING);

                $sheet->setCellValue("A{$totalRow}", translate('grand_total'));
                $sheet->setCellValue("B{$totalRow}", $this->claims->isEmpty() ? 0 : "=COUNTA(B{$dataStartRow}:B{$dataEndRow})");
                $sheet->setCellValue("M{$totalRow}", $this->claims->isEmpty() ? 0 : "=SUM(M{$dataStartRow}:M{$dataEndRow})");

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

                $sheet->getStyle('A2:O3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FF0F172A'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFE6FFFB'],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFD1D5DB'],
                        ],
                    ],
                ]);

                $sheet->getStyle('B2:D2')->getAlignment()->setHorizontal($textAlignment);
                $sheet->getStyle('F2:H2')->getAlignment()->setHorizontal($textAlignment);
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

                if (!$this->claims->isEmpty()) {
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

                $sheet->getStyle("A{$totalRow}:A{$totalRow}")->getAlignment()->setHorizontal($textAlignment);
                $sheet->getStyle("B{$totalRow}:B{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("M{$totalRow}:M{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("A{$dataStartRow}:D{$tableEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("I{$dataStartRow}:M{$tableEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("O{$dataStartRow}:O{$tableEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E{$dataStartRow}:H{$tableEndRow}")->getAlignment()->setHorizontal($textAlignment);
                $sheet->getStyle("N{$dataStartRow}:N{$tableEndRow}")->getAlignment()->setHorizontal($textAlignment);

                $sheet->getStyle("E{$dataStartRow}:O{$tableEndRow}")->getAlignment()->setWrapText(true);
                $sheet->setAutoFilter("A6:{$lastColumn}" . max(6, $dataEndRow));

                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getRowDimension(2)->setRowHeight(22);
                $sheet->getRowDimension(3)->setRowHeight(36);
                $sheet->getRowDimension(6)->setRowHeight(24);
            },
        ];
    }

    private function translateClaimStatus(string $status): string
    {
        $translationKey = 'warranty_claim_status_' . $status;
        $translated = translate($translationKey);

        return $translated === $translationKey
            ? ucwords(str_replace('_', ' ', $status))
            : $translated;
    }

    private function resolveCustomerName(WarrantyClaim $claim): string
    {
        $user = $claim->warranty?->user;
        $fullName = trim(implode(' ', array_filter([
            (string) ($user?->f_name ?? ''),
            (string) ($user?->l_name ?? ''),
        ])));

        if ($fullName !== '') {
            return $fullName;
        }

        if (filled($user?->name)) {
            return (string) $user->name;
        }

        return $claim->warranty?->activated_by_name ?: '-';
    }

    private function resolveSlaResult(WarrantyClaim $claim): string
    {
        if (!$claim->resolution_due) {
            return '-';
        }

        if ($claim->resolved_at) {
            return $claim->resolved_at->lte($claim->resolution_due)
                ? translate('warranty_sla_within')
                : translate('warranty_sla_breached');
        }

        return $claim->resolution_due->lt(now())
            ? translate('warranty_sla_breached')
            : translate('warranty_sla_within');
    }

    private function resolveProductName(WarrantyClaim $claim): string
    {
        return $this->resolveRawModelAttribute($claim->warranty?->product, 'name');
    }

    private function resolveBranchName(WarrantyClaim $claim): string
    {
        return $this->resolveRawModelAttribute($claim->branch, 'branch_name');
    }

    private function resolveRawModelAttribute(?object $model, string $attribute): string
    {
        if ($model && method_exists($model, 'getRawOriginal')) {
            $rawValue = $model->getRawOriginal($attribute);
            if (filled($rawValue)) {
                return (string) $rawValue;
            }
        }

        if ($model && method_exists($model, 'getAttributes')) {
            $attributes = $model->getAttributes();
            if (filled($attributes[$attribute] ?? null)) {
                return (string) $attributes[$attribute];
            }
        }

        return $model?->{$attribute} ?? '-';
    }
}
