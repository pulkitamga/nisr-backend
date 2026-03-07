<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StockTransferReportExport implements WithMultipleSheets
{
    public function __construct(private readonly array $data)
    {
    }

    public function sheets(): array
    {
        $isRtl = session('direction') === 'rtl';
        $statistics = $this->data['statistics'] ?? [];
        $transfers = collect($this->data['transfers'] ?? []);

        $topFromBranch = $this->formatTopBranch($statistics['top_from_branch'] ?? null);
        $topToBranch = $this->formatTopBranch($statistics['top_to_branch'] ?? null);

        $summaryHeadings = [translate('metric'), translate('value')];
        $summaryRows = [
            [translate('total_transfers'), (int)($statistics['total_transfers'] ?? 0)],
            [translate('pending'), (int)($statistics['pending_transfers'] ?? 0)],
            [translate('approved'), (int)($statistics['approved_transfers'] ?? 0)],
            [translate('rejected'), (int)($statistics['rejected_transfers'] ?? 0)],
            [translate('total_quantity'), (int)($statistics['total_quantity'] ?? 0)],
            [translate('top_from_branch'), $topFromBranch],
            [translate('top_to_branch'), $topToBranch],
        ];

        $detailsHeadings = [
            translate('date'),
            translate('from_branch'),
            translate('to_branch'),
            translate('items'),
            translate('status'),
        ];

        $detailsRows = $transfers->map(function ($transfer) {
            $date = '';
            if (!empty($transfer->transfer_date)) {
                $date = Carbon::parse((string)$transfer->transfer_date)->format('Y-m-d');
            }

            $fromBranch = (string)(
                data_get($transfer, 'fromBranch.branch_name')
                ?? data_get($transfer, 'from_branch.branch_name')
                ?? '-'
            );
            $toBranch = (string)(
                data_get($transfer, 'toBranch.branch_name')
                ?? data_get($transfer, 'to_branch.branch_name')
                ?? '-'
            );

            $products = collect(data_get($transfer, 'products', []));
            $items = (int)$products->sum(fn($product) => (int)data_get($product, 'quantity', 0));
            $status = $products
                ->pluck('status')
                ->filter()
                ->map(fn($value) => translate(strtolower((string)$value)))
                ->unique()
                ->implode(', ');

            return [
                $date,
                $fromBranch,
                $toBranch,
                $items,
                $status !== '' ? $status : '-',
            ];
        })->values()->all();

        return [
            new InhouseProductSaleSheetExport(
                title: translate('summary'),
                headings: $summaryHeadings,
                rows: $summaryRows,
                isRtl: $isRtl
            ),
            new InhouseProductSaleSheetExport(
                title: translate('details'),
                headings: $detailsHeadings,
                rows: $detailsRows,
                isRtl: $isRtl
            ),
        ];
    }

    private function formatTopBranch(mixed $branch): string
    {
        if (is_array($branch)) {
            $name = (string)($branch['name'] ?? '-');
            $count = (int)($branch['count'] ?? 0);
            return $count > 0 ? "{$name} ({$count})" : $name;
        }

        if (is_object($branch)) {
            $name = (string)data_get($branch, 'name', '-');
            $count = (int)data_get($branch, 'count', 0);
            return $count > 0 ? "{$name} ({$count})" : $name;
        }

        return '-';
    }
}

