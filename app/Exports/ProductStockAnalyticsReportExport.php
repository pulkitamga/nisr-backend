<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductStockAnalyticsReportExport implements WithMultipleSheets
{
    public function __construct(private readonly array $data)
    {
    }

    public function sheets(): array
    {
        $isRtl = session('direction') === 'rtl';

        $summaryRows = [
            [translate('from'), (string)($this->data['filters']['from'] ?? '')],
            [translate('to'), (string)($this->data['filters']['to'] ?? '')],
            [translate('total_current_stock'), (int)($this->data['summary']['total_current_stock'] ?? 0)],
            [translate('total_stock_in'), (int)($this->data['summary']['total_stock_in'] ?? 0)],
            [translate('total_stock_out'), (int)($this->data['summary']['total_stock_out'] ?? 0)],
            [translate('net_stock_movement'), (int)($this->data['summary']['net_stock_movement'] ?? 0)],
            [translate('products_count'), (int)($this->data['summary']['products_count'] ?? 0)],
            [translate('branches_count'), (int)($this->data['summary']['branches_count'] ?? 0)],
            [translate('exported_at'), optional($this->data['exportedAt'] ?? now())->format('Y-m-d H:i:s')],
        ];

        $stockByProductRows = collect($this->data['stockByProductRows'] ?? [])->values()->map(function ($row, $index) {
            return [
                $index + 1,
                (string)($row->product_name ?? ''),
                (int)($row->current_stock ?? 0),
                (int)($row->stock_in ?? 0),
                (int)($row->stock_out ?? 0),
                (int)($row->net_movement ?? 0),
            ];
        })->all();

        $stockByBranchRows = collect($this->data['stockByBranchRows'] ?? [])->values()->map(function ($row, $index) {
            return [
                $index + 1,
                (string)($row->branch_name ?? ''),
                (int)($row->current_stock ?? 0),
                (int)($row->products_count ?? 0),
            ];
        })->all();

        $stockByBranchProductRows = collect($this->data['stockByBranchProductRows'] ?? [])->values()->map(function ($row, $index) {
            return [
                $index + 1,
                (string)($row->branch_name ?? ''),
                (string)($row->product_name ?? ''),
                (int)($row->current_stock ?? 0),
            ];
        })->all();

        $movementRows = collect($this->data['movementRows'] ?? [])->values()->map(function ($row, $index) {
            return [
                $index + 1,
                Carbon::parse($row->date)->format('Y-m-d H:i'),
                (string)($row->product_name ?? ''),
                (string)($row->branch_name ?? ''),
                (string)($row->type ?? ''),
                (int)($row->quantity ?? 0),
                (string)($row->category ?? ''),
                (string)($row->reference ?? ''),
            ];
        })->all();

        return [
            new ProductStockAnalyticsSheetExport(
                title: translate('summary'),
                headings: [translate('metric'), translate('value')],
                rows: $summaryRows,
                isRtl: $isRtl
            ),
            new ProductStockAnalyticsSheetExport(
                title: translate('stock_by_product'),
                headings: [
                    translate('SL'),
                    translate('product'),
                    translate('current_stock'),
                    translate('stock_in'),
                    translate('stock_out'),
                    translate('net_stock_movement'),
                ],
                rows: $stockByProductRows,
                isRtl: $isRtl
            ),
            new ProductStockAnalyticsSheetExport(
                title: translate('stock_by_branch'),
                headings: [
                    translate('SL'),
                    translate('branch'),
                    translate('current_stock'),
                    translate('products_count'),
                ],
                rows: $stockByBranchRows,
                isRtl: $isRtl
            ),
            new ProductStockAnalyticsSheetExport(
                title: translate('stock_by_branch_and_product'),
                headings: [
                    translate('SL'),
                    translate('branch'),
                    translate('product'),
                    translate('current_stock'),
                ],
                rows: $stockByBranchProductRows,
                isRtl: $isRtl
            ),
            new ProductStockAnalyticsSheetExport(
                title: translate('stock_movement_history'),
                headings: [
                    translate('SL'),
                    translate('date'),
                    translate('product'),
                    translate('branch'),
                    translate('type'),
                    translate('quantity'),
                    translate('category'),
                    translate('reference'),
                ],
                rows: $movementRows,
                isRtl: $isRtl
            ),
        ];
    }
}
