<?php

namespace App\Exports;

use App\Models\WarrantyClaim;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WarrantyClaimsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $request;
    protected $locale;

    public function __construct(Request $request, $locale)
    {
        $this->request = $request;
        $this->locale = $locale;
        app()->setLocale($locale);
    }

    public function query()
    {
        $query = WarrantyClaim::with('warranty.user', 'warranty.product', 'branch')
            ->orderBy('submitted_at', 'desc');

        [$start, $end] = $this->resolveDateRange();
        $query->whereBetween('submitted_at', [$start, $end]);

        if ($this->request->filled('branch_id')) {
            $query->where('branch_id', $this->request->branch_id);
        }

        if ($this->request->filled('product_id')) {
            $query->whereHas('warranty', function ($q) {
                $q->where('product_id', $this->request->product_id);
            });
        }

        $status = (string)$this->request->input('status', '');
        if ($status !== '' && $status !== 'all' && in_array($status, $this->allowedStatuses(), true)) {
            $query->where('status', $status);
        }

        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function ($q) use ($search) {
                $q->where('claim_number', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    private function resolveDateRange(): array
    {
        $dateType = (string)$this->request->input('date_type', 'this_year');
        $from = $this->request->input('from');
        $to = $this->request->input('to');

        switch ($dateType) {
            case 'this_month':
                $start = now()->startOfMonth()->startOfDay();
                $end = now()->endOfMonth()->endOfDay();
                break;
            case 'this_week':
                $start = now()->startOfWeek()->startOfDay();
                $end = now()->endOfWeek()->endOfDay();
                break;
            case 'today':
                $start = now()->startOfDay();
                $end = now()->endOfDay();
                break;
            case 'custom_date':
                $start = $this->parseOptionalDate($from, now()->subDays(29)->startOfDay(), true);
                $end = $this->parseOptionalDate($to, now()->endOfDay(), false);
                break;
            case 'this_year':
            default:
                $start = now()->startOfYear()->startOfDay();
                $end = now()->endOfYear()->endOfDay();
                break;
        }

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return [$start, $end];
    }

    private function allowedStatuses(): array
    {
        return [
            'new',
            'approved',
            'rma_issued',
            'received',
            'repair_pending',
            'replacement_pending',
            'qc_pending',
            'shipped_ready',
            'dispatched',
            'waiting_customer',
            'waiting_parts',
            'waiting_payment',
            'resolved',
            'rejected',
            'closed',
        ];
    }

    private function parseOptionalDate(mixed $value, Carbon $fallback, bool $startOfDay): Carbon
    {
        if (blank($value)) {
            return $fallback->copy();
        }

        try {
            $date = Carbon::parse((string)$value);
        } catch (\Throwable) {
            return $fallback->copy();
        }

        return $startOfDay ? $date->startOfDay() : $date->endOfDay();
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
            translate('submitted_at'),
            translate('sla_due'),
            translate('branch'),
        ];
    }

    public function map($claim): array
    {
        static $index = 0;
        $index++;
        return [
            $index,
            $claim->claim_number,
            $claim->serial_number,
            translate($claim->status),
            $claim->warranty?->user?->name ?? $claim->warranty?->activated_by_name ?? '',
            $claim->warranty?->product?->name ?? '-',
            $claim->submitted_at ? $claim->submitted_at->format('Y-m-d H:i') : '',
            $claim->resolution_due ? $claim->resolution_due->format('Y-m-d H:i') : '-',
            $claim->branch?->branch_name ?? '',
        ];
    }
}
