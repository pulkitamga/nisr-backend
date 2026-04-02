<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SerialTransferHistory;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WarrantyTransferController extends Controller
{
    private const MAX_TRANSACTION_RESULTS_LIMIT = 500;

    public function list(Request $request)
    {
        $transactions = $this->buildTransactionQuery($request)
            ->paginate($this->resolveListPerPage($request))
            ->appends($request->query());

        $branches = Branch::pluck('branch_name', 'id');
        $types = $this->transferTypeOptions();

        return view('admin-views.warranty.serial-transaction.list', compact('transactions', 'branches', 'types'));
    }

    public function export(Request $request): StreamedResponse
    {
        $query = $this->buildTransactionQuery($request);
        $limit = $this->resolveResultsLimit($request);
        $notAvailableLabel = $this->notAvailableLabel();

        if ($limit !== null) {
            $query->limit($limit);
        }

        $transactions = $query->get();
        $typeLabels = $this->transferTypeOptions();

        return response()->streamDownload(function () use ($transactions, $typeLabels, $notAvailableLabel) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Serial', 'From', 'To', 'Type', 'Date']);

            foreach ($transactions as $transaction) {
                $destination = $transaction->toBranch?->branch_name
                    ?? $transaction->distributor?->company_name
                    ?? $notAvailableLabel;

                fputcsv($handle, [
                    $transaction->serial_number,
                    $transaction->fromBranch?->branch_name ?? $notAvailableLabel,
                    $destination,
                    $typeLabels[$transaction->transfer_type] ?? $transaction->transfer_type,
                    optional($transaction->transferred_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, 'warranty-serial-transactions-' . now()->format('Ymd_His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function historyModal($serial)
    {
        $history = SerialTransferHistory::with(['fromBranch', 'toBranch', 'distributor', 'stockTransfer', 'wholesaleDelivery'])
            ->where('serial_number', $serial)
            ->latest()
            ->get();

        $typeLabels = $this->transferTypeOptions();
        $fallbackLabels = [
            'unknown' => translate('Unknown'),
            'initial_import' => translate('initial_import'),
            'wholesaler' => translate('Wholesaler'),
            'not_available' => $this->notAvailableLabel(),
        ];

        return view('admin-views.warranty.serial-transaction._history-modal', compact('history', 'serial', 'typeLabels', 'fallbackLabels'))->render();
    }

    private function buildTransactionQuery(Request $request)
    {
        $query = SerialTransferHistory::with(['fromBranch', 'toBranch', 'distributor', 'stockTransfer', 'wholesaleDelivery'])
            ->latest();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where('serial_number', 'like', "%{$search}%");
        }

        if ($request->filled('from_branch')) {
            $query->where('from_branch_id', $request->input('from_branch'));
        }

        if ($request->filled('to_branch')) {
            $query->where('to_branch_id', $request->input('to_branch'));
        }

        if ($request->filled('transfer_type')) {
            $query->where('transfer_type', $request->input('transfer_type'));
        }

        $this->applyTransferDateFilter($query, $request);

        return $query;
    }

    private function applyTransferDateFilter($query, Request $request): void
    {
        $dateType = (string) $request->input('date_type', '');
        $from = null;
        $to = null;

        switch ($dateType) {
            case 'this_week':
                $from = now()->startOfWeek();
                $to = now()->endOfWeek();
                break;
            case 'this_month':
                $from = now()->startOfMonth();
                $to = now()->endOfMonth();
                break;
            case 'this_year':
                $from = now()->startOfYear();
                $to = now()->endOfYear();
                break;
            case 'custom_date':
            default:
                if ($request->filled('from') && $request->filled('to')) {
                    try {
                        $from = Carbon::parse($request->input('from'))->startOfDay();
                        $to = Carbon::parse($request->input('to'))->endOfDay();
                    } catch (\Throwable) {
                        $from = null;
                        $to = null;
                    }
                }
                break;
        }

        if ($from && $to) {
            if ($from->gt($to)) {
                [$from, $to] = [$to, $from];
            }

            $query->whereBetween('transferred_at', [$from, $to]);
        }
    }

    private function resolveListPerPage(Request $request): int
    {
        return $this->resolveResultsLimit($request)
            ?? (int) (getWebConfig('pagination_limit') ?? 10);
    }

    private function resolveResultsLimit(Request $request): ?int
    {
        $value = $request->input('choose_first');

        if (!is_numeric($value)) {
            return null;
        }

        $limit = (int) $value;

        if ($limit <= 0) {
            return null;
        }

        return min($limit, self::MAX_TRANSACTION_RESULTS_LIMIT);
    }

    private function transferTypeOptions(): array
    {
        return [
            'branch_to_branch' => translate('Branch → Branch'),
            'branch_to_wholesale' => translate('Branch → Wholesaler'),
        ];
    }

    private function notAvailableLabel(): string
    {
        return translate('not_available');
    }
}
