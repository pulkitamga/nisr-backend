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

        if ($this->request->filled('date_range')) {
            [$start, $end] = explode(' - ', $this->request->date_range);
            $start = Carbon::parse($start);
            $end = Carbon::parse($end)->endOfDay();
            $query->whereBetween('submitted_at', [$start, $end]);
        }

        if ($this->request->filled('branch_id')) {
            $query->where('branch_id', $this->request->branch_id);
        }

        if ($this->request->filled('product_id')) {
            $query->whereHas('warranty', function ($q) {
                $q->where('product_id', $this->request->product_id);
            });
        }

        if ($this->request->filled('status') && $this->request->status != 'all') {
            $query->where('status', $this->request->status);
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