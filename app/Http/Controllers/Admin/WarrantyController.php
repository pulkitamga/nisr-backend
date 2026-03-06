<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\SerialImport;
use App\Models\Warranty;
use App\Models\ActivationReview;
use App\Models\Blacklist;
use App\Models\Product;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\WarrantyClaim;
use App\Models\WarrantyClaimCharge;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ValidationHeading implements WithHeadingRow {}

class WarrantyController extends Controller
{
    public function importView(): \Illuminate\Contracts\View\View
    {
        $history = Warranty::where('status', 'preactivated')
            ->selectRaw('DATE(created_at) as import_date, COUNT(*) as count')
            ->groupBy('import_date')
            ->orderBy('import_date', 'desc')
            ->get();

        return view('admin-views.warranty.import', compact('history'));
    }

    public function import(Request $request)
    {
        session()->forget(['import_summary', 'error_csv_path', 'validation_errors', 'temp_file', 'failed_rows', 'total_rows']);

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt'
        ]);

        $upload = $request->file('csv_file');
        $tempPath = $upload->store('temp_imports', 'public');
        $fullPath = storage_path('app/public/' . $tempPath);

        session(['temp_file' => $tempPath]);

        $rows = Excel::toArray(new ValidationHeading(), $fullPath)[0];
        $errors = [];
        $totalRows = count($rows);

        foreach ($rows as $index => $row) {
            $validator = Validator::make($row, [
                'serial_number' => 'required|string|unique:warranties,serial_number',
                'product_id' => 'nullable|integer',
                'warranty_months' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                $errMsg = [];
                foreach ($validator->errors()->all() as $msg) {
                    $errMsg[] = $msg;
                }
                $errors[] = [
                    'row' => $index + 2,
                    'data' => $row,
                    'error' => implode('; ', $errMsg)
                ];
            }
        }

        if (!empty($errors)) {
            $csv = fopen('php://temp', 'r+');
            fputcsv($csv, ['Row', 'Serial Number', 'Product ID', 'Warranty Months', 'Error']);

            foreach ($errors as $err) {
                fputcsv($csv, [
                    $err['row'],
                    $err['data']['serial_number'] ?? '',
                    $err['data']['product_id'] ?? '',
                    $err['data']['warranty_months'] ?? '',
                    $err['error']
                ]);
            }

            rewind($csv);
            $filename = 'validation_errors_' . now()->format('Ymd_His') . '.csv';
            $errorPath = 'warranty_errors/' . $filename;
            Storage::disk('public')->put($errorPath, stream_get_contents($csv));
            fclose($csv);

            $errorCsvPath = Storage::url($errorPath);

            session([
                'validation_errors' => true,
                'error_csv_path' => $errorCsvPath,
                'failed_rows' => count($errors),
                'total_rows' => $totalRows,
            ]);

            Toastr::warning(translate('CSV validation failed! ' . count($errors) . ' errors found. Please review the error CSV.'));
            return back();
        } else {
            // Proceed to import
            $import = new SerialImport();
            Excel::import($import, $fullPath);

            $errorCsvPath = null;
            if (count($import->errors) > 0) {
                $csv = fopen('php://temp', 'r+');
                fputcsv($csv, ['Serial Number', 'Product ID', 'Warranty Months', 'Error']);

                foreach ($import->errors as $err) {
                    fputcsv($csv, [
                        $err['row']['serial_number'] ?? '',
                        $err['row']['product_id'] ?? '',
                        $err['row']['warranty_months'] ?? '',
                        $err['error']
                    ]);
                }

                rewind($csv);
                $filename = 'import_errors_' . now()->format('Ymd_His') . '.csv';
                $path = 'warranty_errors/' . $filename;
                Storage::disk('public')->put($path, stream_get_contents($csv));
                fclose($csv);

                $errorCsvPath = Storage::url($path);
            }

            $summary = [
                'created' => $import->created,
                'updated' => $import->updated,
                'failed'  => $import->failed,
                'errors'  => $import->errors,
            ];

            session([
                'import_summary' => $summary,
                'error_csv_path' => $errorCsvPath
            ]);

            Storage::disk('public')->delete($tempPath);
            session()->forget('temp_file');

            clearWebConfigCacheKeys();

            if ($import->failed > 0 && $import->created == 0 && $import->updated == 0) {
                Toastr::error(translate('Import failed! Please check the error CSV file.'));
            } elseif ($import->failed > 0) {
                Toastr::warning(translate('Import completed with some errors. Check the error CSV.'));
            } else {
                Toastr::success(translate('Serials imported successfully!'));
            }

            return back();
        }
    }

    public function continueImport()
    {
        $tempFile = session('temp_file');

        if (!$tempFile || !Storage::disk('public')->exists($tempFile)) {
            Toastr::error(translate('Temporary file not found.'));
            return back();
        }

        $fullPath = storage_path('app/public/' . $tempFile);

        $import = new SerialImport();
        Excel::import($import, $fullPath);

        $errorCsvPath = null;
        if (count($import->errors) > 0) {
            $csv = fopen('php://temp', 'r+');
            fputcsv($csv, ['Serial Number', 'Product ID', 'Warranty Months', 'Error']);

            foreach ($import->errors as $err) {
                fputcsv($csv, [
                    $err['row']['serial_number'] ?? '',
                    $err['row']['product_id'] ?? '',
                    $err['row']['warranty_months'] ?? '',
                    $err['error']
                ]);
            }

            rewind($csv);
            $filename = 'import_errors_' . now()->format('Ymd_His') . '.csv';
            $path = 'warranty_errors/' . $filename;
            Storage::disk('public')->put($path, stream_get_contents($csv));
            fclose($csv);

            $errorCsvPath = Storage::url($path);
        }

        $summary = [
            'created' => $import->created,
            'updated' => $import->updated,
            'failed'  => $import->failed,
            'errors'  => $import->errors,
        ];

        session([
            'import_summary' => $summary,
            'error_csv_path' => $errorCsvPath
        ]);

        Storage::disk('public')->delete($tempFile);
        session()->forget(['temp_file', 'validation_errors', 'failed_rows', 'total_rows']);

        clearWebConfigCacheKeys();

        if ($import->failed > 0) {
            Toastr::warning(translate('Import completed with some errors. Check the error CSV.'));
        } else {
            Toastr::success(translate('Serials imported successfully!'));
        }

        return back();
    }

    public function reupload()
    {
        $tempFile = session('temp_file');
        if ($tempFile && Storage::disk('public')->exists($tempFile)) {
            Storage::disk('public')->delete($tempFile);
        }

        session()->forget(['validation_errors', 'error_csv_path', 'temp_file', 'failed_rows', 'total_rows', 'import_summary']);

        return $this->importView();
    }

    public function downloadErrorCsv()
    {
        $errorCsvPath = session('error_csv_path');

        if (!$errorCsvPath) {
            Toastr::error(translate('Error CSV not found.'));
            return back();
        }

        $path = str_replace('/storage/', '', $errorCsvPath);
        if (!Storage::disk('public')->exists($path)) {
            Toastr::error(translate('Error CSV not found.'));
            return back();
        }

        if (session('validation_errors')) {
            session()->forget('import_summary');
        } else {
            session()->forget(['import_summary', 'error_csv_path']);
        }

        return Storage::disk('public')->download($path);
    }

    // Import History List
    public function importHistory()
    {
        $history = Warranty::where('status', 'preactivated')
            ->selectRaw('DATE(created_at) as import_date, COUNT(*) as count')
            ->groupBy('import_date')
            ->orderBy('import_date', 'desc')
            ->paginate(10);

        return view('admin-views.warranty.import-history', compact('history'));
    }

    public function exportImportHistory(Request $request): StreamedResponse
    {
        $history = Warranty::where('status', 'preactivated')
            ->selectRaw('DATE(created_at) as import_date, COUNT(*) as count')
            ->groupBy('import_date')
            ->orderBy('import_date', 'desc')
            ->get();

        $filename = 'warranty-import-history-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($history) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Imported Serials']);
            foreach ($history as $row) {
                fputcsv($handle, [$row->import_date, $row->count]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    // Manual Activate View
    public function manualActivateView()
    {
        return view('admin-views.warranty.manual-activate', [
            'prefillSerial' => request('serial_number'),
        ]);
    }

    // Manual Activate
    public function manualActivate(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|string|exists:warranties,serial_number',
            'purchase_date' => 'required|date',
            'reason' => 'required|string',
            'docs' => 'nullable|file|mimes:pdf,jpg|max:2048',
        ]);

        $warranty = Warranty::where('serial_number', $request->serial_number)->firstOrFail();
        $docPath = $request->hasFile('docs') ? $request->file('docs')->store('warranty/manual', 'public') : null;

        $startDate = now();
        $months = $warranty->warranty_months ?? ($warranty->product->warranty_duration ?? 12);
        $endDate = $startDate->copy()->addMonths($months);

        $warranty->update([
            'status' => 'active',
            'activation_date' => $startDate,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'purchase_date' => $request->purchase_date,
            'is_admin_manual_activation' => true,
            'activation_method' => 'admin_manual',
            'receipt_path' => $docPath,
        ]);

        $warranty->timelineEvents()->create([
            'event_type' => 'manual_activated',
            'description' => "Manual by admin: {$request->reason}",
            'user_id' => auth('admin')->id(),
        ]);

        event(new \App\Events\WarrantyActivatedEvent($warranty));

        Toastr::success(translate('Manual activation complete.'));
        return redirect()->route('admin.warranty.activation.list');
    }

   

    // Activation List
    public function activationList(Request $request)
    {
        $query = Warranty::with('user', 'product');
        if ($request->searchValue) {
            $query->where('serial_number', 'like', '%' . $request->searchValue . '%');
        }
        if ($request->method && $request->method != '') {
            $query->where('activation_method', $request->method);
        }
        $dataLimit = getWebConfig('pagination_limit') ?? 10;

        $activations = $query->paginate($dataLimit);

        return view('admin-views.warranty.activation-list', compact('activations'));
    }

    public function activationView(Warranty $warranty)
    {
        $warranty->load([
            'user',
            'product',
            'timelineEvents.user',
            'originalWarranty',
            'replacements.newWarranty',
            'replacements.technician'
        ]);
        return view('admin-views.warranty.activation-view', compact('warranty'));
    }

    public function blacklistView(Request $request)
    {
        $dataLimit = getWebConfig('pagination_limit') ?? 10;

        $blacklists = Blacklist::with('warranty');

        if ($request->searchValue) {
            $blacklists = $blacklists->whereHas('warranty', function ($q) use ($request) {
                $q->where('serial_number', 'like', "%{$request->searchValue}%");
            });
        }

        $blacklists = $blacklists->paginate($dataLimit);

        return view('admin-views.warranty.blacklist', compact('blacklists'));
    }


    // Blacklist Add
    public function blacklist(Request $request)
    {
        $request->validate([
            'serial_number' => [
                'required',
                'string',
                'exists:warranties,serial_number',
                function ($attribute, $value, $fail) {
                    if (Blacklist::where('serial_number', $value)->exists()) {
                        $fail(translate('This serial number is already blacklisted.'));
                    }
                },
            ],
            'reason' => 'required|string',
        ]);

        Blacklist::create([
            'serial_number' => $request->serial_number,
            'reason' => $request->reason,
        ]);

        Toastr::success(translate('Blacklisted successfully.'));
        return back();
    }


    // Blacklist Remove
    public function blacklistRemove($id)
    {
        $blacklist = Blacklist::findOrFail($id);
        $blacklist->delete();

        Toastr::success(translate('Blacklist removed.'));
        return back();
    }

    // History Details
    public function historyDetails(Request $request, $date)
    {
        $dataLimit = getWebConfig('pagination_limit') ?? 10;

        $search = $request->searchValue;

        $details = Warranty::where('status', 'preactivated')
            ->whereDate('created_at', $date)
            ->when($search, function ($q) use ($search) {
                $q->where('serial_number', 'LIKE', "%{$search}%");
            })
            ->paginate($dataLimit)
            ->appends(['searchValue' => $search]); // pagination me search carry forward

        return view('admin-views.warranty.history-details', compact('details', 'date'));
    }

    public function exportHistoryDetails(Request $request, $date): StreamedResponse
    {
        $search = $request->searchValue;

        $details = Warranty::with('product')
            ->where('status', 'preactivated')
            ->whereDate('created_at', $date)
            ->when($search, function ($q) use ($search) {
                $q->where('serial_number', 'LIKE', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'warranty-import-details-' . $date . '-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($details) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Serial Number', 'Product', 'Status', 'Created At']);
            foreach ($details as $warranty) {
                fputcsv($handle, [
                    $warranty->serial_number,
                    $warranty->product?->name ?? '-',
                    $warranty->status,
                    optional($warranty->created_at)->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }


    // Reviews (Activation)
    public function activationReviews()
    {
        $dataLimit = getWebConfig('pagination_limit') ?? 10;

        $reviews = ActivationReview::with('warranty.product')->where('status', 'pending')->paginate($dataLimit);

        return view('admin-views.warranty.review-activation', compact('reviews'));
    }

    // Reviews (Claim)
    public function claimReviews()
    {
        $dataLimit = getWebConfig('pagination_limit') ?? 10;

        $reviews = WarrantyClaim::where('status', 'triage_pending')->with('warranty.user')->paginate($dataLimit);

        return view('admin-views.warranty.review-claim', compact('reviews'));
    }

    // Reports (Claims)
    public function reportClaims()
    {
        $reports = [
            'claim_rate' => \App\Models\WarrantyClaim::count() / max(1, \App\Models\Warranty::count()) * 100,
        ];

        $dataLimit = getWebConfig('pagination_limit') ?? 10;

        $claimsByStatus = \App\Models\WarrantyClaim::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->orderBy('count', 'desc')
            ->paginate($dataLimit);

        return view('admin-views.warranty.report-claims', compact('reports', 'claimsByStatus'));
    }

    // Reports (SLA)
    public function reportSLA()
    {
        $totalClaims = WarrantyClaim::count();
        $onTimeClaims = WarrantyClaim::where('resolution_due', '>', now())->count();
        $breached = $totalClaims - $onTimeClaims;

        $sla = [
            'compliance' => $totalClaims > 0 ? ($onTimeClaims / $totalClaims) * 100 : 0,
        ];

        $firstResponseDetails = WarrantyClaim::select(
            'claim_number',
            'warranty_id',
            DB::raw('(select serial_number from warranties where warranties.id = warranty_claims.warranty_id) as serial_number'),
            DB::raw('response_due as due_date'),
            DB::raw('"response_due" as type'),
            DB::raw('IF(response_due > NOW(), 1, 0) as is_within_sla')
        )->whereNotNull('response_due');

        $decisionDetails = WarrantyClaim::select(
            'claim_number',
            'warranty_id',
            DB::raw('(select serial_number from warranties where warranties.id = warranty_claims.warranty_id) as serial_number'),
            DB::raw('resolution_due as due_date'),
            DB::raw('"decision" as type'),
            DB::raw('IF(resolution_due > NOW(), 1, 0) as is_within_sla')
        )->whereNotNull('resolution_due');

        $dataLimit = getWebConfig('pagination_limit') ?? 10;

        $slaDetails = $firstResponseDetails->union($decisionDetails)
            ->orderBy('due_date', 'asc')
            ->paginate($dataLimit);

        return view('admin-views.warranty.report-sla', compact('sla', 'breached', 'slaDetails'));
    }

    // Reports (Activations)
    public function reportActivations()
    {
        $total = Warranty::count();
        $active = Warranty::where('status', 'active')->count();
        $rate = $total > 0 ? ($active / $total) * 100 : 0;

        $activationMethods = [
            'user_public_form' => 'Public Form',
            'admin_manual'     => 'Admin Panel',
            'auto_activation'  => 'Auto Activation',
            'mobile_app'       => 'Mobile App',
            'order_activation' => 'Order Activation',
            'replacement'      => 'Replacement',
        ];

        $methodCounts = [];

        foreach ($activationMethods as $key => $label) {
            $count = Warranty::where('status', 'active')->where('activation_method', $key)->count();
            $methodCounts[] = [
                'label' => $label,
                'count' => $count,
                'percentage' => $active > 0 ? round(($count / $active) * 100, 2) : 0
            ];
        }

        $knownMethods = array_keys($activationMethods);
        $otherCount = Warranty::where('status', 'active')
            ->whereNotIn('activation_method', $knownMethods)
            ->count();
        if ($otherCount > 0) {
            $methodCounts[] = [
                'label' => 'Other',
                'count' => $otherCount,
                'percentage' => $active > 0 ? round(($otherCount / $active) * 100, 2) : 0,
            ];
        }

        return view('admin-views.warranty.report-activations', compact('rate', 'methodCounts'));
    }

    public function reportAnalytics(): View
    {
        $snapshotFrom = now()->subDays(89)->startOfDay();
        $snapshotTo = now()->endOfDay();
        $trendStart = now()->copy()->startOfMonth()->subMonths(11);
        $trendEnd = now()->endOfDay();

        $warrantyQuery = Warranty::query()
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo]);
        $claimQuery = WarrantyClaim::query()
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo]);

        $totalWarranties = (clone $warrantyQuery)->count();
        $activeWarranties = (clone $warrantyQuery)->where('status', 'active')->count();
        $activatedInPeriod = Warranty::query()
            ->whereNotNull('activation_date')
            ->whereBetween('activation_date', [$snapshotFrom, $snapshotTo])
            ->count();
        $activationRate = $totalWarranties > 0 ? ($activeWarranties / $totalWarranties) * 100 : 0;

        $totalClaims = (clone $claimQuery)->count();
        $resolvedClaims = (clone $claimQuery)->whereIn('status', ['resolved', 'closed'])->count();
        $rejectedClaims = (clone $claimQuery)->where('status', 'rejected')->count();
        $openClaims = max(0, $totalClaims - $resolvedClaims - $rejectedClaims);
        $claimRate = $totalWarranties > 0 ? ($totalClaims / $totalWarranties) * 100 : 0;
        $closureRate = $totalClaims > 0 ? ($resolvedClaims / $totalClaims) * 100 : 0;

        $slaTrackedClaims = (clone $claimQuery)
            ->whereNotNull('resolution_due')
            ->count();
        $slaOnTimeClaims = (clone $claimQuery)
            ->whereNotNull('resolution_due')
            ->where(function ($query) {
                $query->where(function ($pendingQuery) {
                    $pendingQuery->whereNull('resolved_at')
                        ->where('resolution_due', '>', now());
                })->orWhere(function ($resolvedQuery) {
                    $resolvedQuery->whereNotNull('resolved_at')
                        ->whereColumn('resolved_at', '<=', 'resolution_due');
                });
            })
            ->count();
        $slaCompliance = $slaTrackedClaims > 0 ? ($slaOnTimeClaims / $slaTrackedClaims) * 100 : 0;

        $avgResolutionHoursRaw = (clone $claimQuery)
            ->whereNotNull('submitted_at')
            ->whereNotNull('resolved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, submitted_at, resolved_at)) as avg_hours')
            ->value('avg_hours');
        $avgResolutionHours = $avgResolutionHoursRaw !== null ? round((float)$avgResolutionHoursRaw, 1) : null;

        $chargeRows = WarrantyClaimCharge::query()
            ->join('warranty_claims', 'warranty_claims.id', '=', 'warranty_claim_charges.warranty_claim_id')
            ->whereBetween('warranty_claims.created_at', [$snapshotFrom, $snapshotTo])
            ->selectRaw("COALESCE(NULLIF(warranty_claim_charges.charge_type, ''), 'other') as charge_type")
            ->selectRaw('COUNT(*) as charges_count')
            ->selectRaw('SUM(COALESCE(warranty_claim_charges.amount, 0)) as total_amount')
            ->groupBy(DB::raw("COALESCE(NULLIF(warranty_claim_charges.charge_type, ''), 'other')"))
            ->orderByDesc('total_amount')
            ->limit(8)
            ->get();
        $totalChargeAmount = (float)$chargeRows->sum('total_amount');

        $activationTrendRows = Warranty::query()
            ->whereNotNull('activation_date')
            ->whereBetween('activation_date', [$trendStart, $trendEnd])
            ->selectRaw('YEAR(activation_date) as year, MONTH(activation_date) as month, COUNT(*) as total')
            ->groupBy('year', 'month')
            ->get();
        $claimTrendRows = WarrantyClaim::query()
            ->whereBetween('created_at', [$trendStart, $trendEnd])
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('year', 'month')
            ->get();
        $resolvedTrendRows = WarrantyClaim::query()
            ->whereNotNull('resolved_at')
            ->whereBetween('resolved_at', [$trendStart, $trendEnd])
            ->selectRaw('YEAR(resolved_at) as year, MONTH(resolved_at) as month, COUNT(*) as total')
            ->groupBy('year', 'month')
            ->get();

        $activationTrendMap = $this->buildMonthlyCountMap($activationTrendRows->toArray());
        $claimTrendMap = $this->buildMonthlyCountMap($claimTrendRows->toArray());
        $resolvedTrendMap = $this->buildMonthlyCountMap($resolvedTrendRows->toArray());

        $trendLabels = [];
        $activationTrend = [];
        $claimTrend = [];
        $resolvedTrend = [];
        for ($monthIndex = 0; $monthIndex < 12; $monthIndex++) {
            $monthDate = $trendStart->copy()->addMonths($monthIndex);
            $monthKey = $monthDate->format('Y-m');
            $trendLabels[] = $monthDate->format('M Y');
            $activationTrend[] = (int)($activationTrendMap[$monthKey] ?? 0);
            $claimTrend[] = (int)($claimTrendMap[$monthKey] ?? 0);
            $resolvedTrend[] = (int)($resolvedTrendMap[$monthKey] ?? 0);
        }

        $statusRows = (clone $claimQuery)
            ->selectRaw("COALESCE(NULLIF(status, ''), 'new') as status_name")
            ->selectRaw('COUNT(*) as total')
            ->groupBy(DB::raw("COALESCE(NULLIF(status, ''), 'new')"))
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $agingBuckets = [
            '0-2 Days' => 0,
            '3-7 Days' => 0,
            '8-14 Days' => 0,
            '15+ Days' => 0,
        ];
        $openClaimsAging = WarrantyClaim::query()
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo])
            ->whereNotIn('status', ['resolved', 'closed', 'rejected'])
            ->select(['submitted_at', 'created_at'])
            ->get();
        foreach ($openClaimsAging as $claim) {
            $referenceDate = $claim->submitted_at ?: $claim->created_at;
            if (!$referenceDate) {
                continue;
            }
            $ageDays = $referenceDate->diffInDays($snapshotTo);
            if ($ageDays <= 2) {
                $agingBuckets['0-2 Days']++;
            } elseif ($ageDays <= 7) {
                $agingBuckets['3-7 Days']++;
            } elseif ($ageDays <= 14) {
                $agingBuckets['8-14 Days']++;
            } else {
                $agingBuckets['15+ Days']++;
            }
        }

        $topProducts = WarrantyClaim::query()
            ->join('warranties', 'warranties.id', '=', 'warranty_claims.warranty_id')
            ->leftJoin('products', 'products.id', '=', 'warranties.product_id')
            ->whereBetween('warranty_claims.created_at', [$snapshotFrom, $snapshotTo])
            ->selectRaw("COALESCE(products.name, 'Unknown Product') as product_name")
            ->selectRaw('COUNT(*) as claims_count')
            ->groupBy('warranties.product_id', 'products.name')
            ->orderByDesc('claims_count')
            ->limit(8)
            ->get();

        $kpi = [
            'total_warranties' => $totalWarranties,
            'active_warranties' => $activeWarranties,
            'activated_in_period' => $activatedInPeriod,
            'activation_rate' => $activationRate,
            'total_claims' => $totalClaims,
            'open_claims' => $openClaims,
            'resolved_claims' => $resolvedClaims,
            'claim_rate' => $claimRate,
            'closure_rate' => $closureRate,
            'sla_compliance' => $slaCompliance,
            'avg_resolution_hours' => $avgResolutionHours,
            'total_charge_amount' => $totalChargeAmount,
        ];

        $trendChartData = [
            'labels' => $trendLabels,
            'activations' => $activationTrend,
            'claims' => $claimTrend,
            'resolved' => $resolvedTrend,
        ];

        $statusChartData = [
            'labels' => $statusRows->pluck('status_name')->map(fn($value) => ucwords(str_replace('_', ' ', (string)$value)))->values()->all(),
            'counts' => $statusRows->pluck('total')->map(fn($value) => (int)$value)->values()->all(),
        ];

        $agingChartData = [
            'labels' => array_keys($agingBuckets),
            'counts' => array_values($agingBuckets),
        ];

        $chargeChartData = [
            'labels' => $chargeRows->pluck('charge_type')->map(fn($value) => ucwords(str_replace('_', ' ', (string)$value)))->values()->all(),
            'amounts' => $chargeRows->pluck('total_amount')->map(fn($value) => (float)$value)->values()->all(),
        ];

        $insights = $this->buildWarrantyInsights(
            kpi: $kpi,
            trendLabels: $trendLabels,
            claimTrend: $claimTrend,
            resolvedTrend: $resolvedTrend,
            statusRows: $statusRows->toArray()
        );

        return view('admin-views.warranty.report-analytics', compact(
            'kpi',
            'trendChartData',
            'statusChartData',
            'agingChartData',
            'chargeChartData',
            'topProducts',
            'insights',
            'snapshotFrom',
            'snapshotTo'
        ));
    }

    public function blacklistAddView()
    {
        return view('admin-views.warranty.blacklist-add');
    }

    public function approveActivation(ActivationReview $review, Request $request)
    {
        $request->validate([
            'review_notes' => 'nullable|string|max:1000'
        ]);

        $review->update([
            'status' => 'approved',
            'review_notes' => $request->review_notes,
            'agent_id' => auth('admin')->id(),
            'reviewed_at' => now(),
        ]);

        $warranty = $review->warranty;
        $warranty->update([
            'status' => 'active',
        ]);

        $warranty->timelineEvents()->create([
            'warranty_id' => $warranty->id,
            'event_type' => 'activation_approved',
            'description' => translate('Activation approved by admin'),
            'timestamp' => now(),
            'user_id' => auth('admin')->id(),
        ]);

        Toastr::success(translate('Activation approved successfully.'));
        return back();
    }

    public function rejectActivation(ActivationReview $review, Request $request)
    {
        $request->validate([
            'review_notes' => 'required|string|max:1000'
        ]);

        $review->update([
            'status' => 'rejected',
            'review_notes' => $request->review_notes,
            'agent_id' => auth('admin')->id(),
            'reviewed_at' => now(),
        ]);

        // Update Warranty
        $warranty = $review->warranty;
        $warranty->update([
            'status' => 'cancelled',
        ]);

        // Timeline Event
        $warranty->timelineEvents()->create([
            'warranty_id' => $warranty->id,
            'event_type' => 'activation_rejected',
            'description' => translate('Activation rejected') . ': ' . $request->review_notes,
            'timestamp' => now(),
            'user_id' => auth('admin')->id(),
        ]);

        Toastr::success(translate('Activation rejected.'));
        return back();
    }

    private function buildMonthlyCountMap(array $rows): array
    {
        $map = [];
        foreach ($rows as $row) {
            $year = (int)data_get($row, 'year', 0);
            $month = (int)data_get($row, 'month', 0);
            if ($year > 0 && $month > 0) {
                $map[sprintf('%04d-%02d', $year, $month)] = (int)data_get($row, 'total', 0);
            }
        }

        return $map;
    }

    private function buildWarrantyInsights(
        array $kpi,
        array $trendLabels,
        array $claimTrend,
        array $resolvedTrend,
        array $statusRows
    ): array {
        if (($kpi['total_warranties'] ?? 0) === 0 && ($kpi['total_claims'] ?? 0) === 0) {
            return ['No warranty activity was found in the last 90 days.'];
        }

        $insights = [];
        $insights[] = 'Claim rate is ' . number_format((float)$kpi['claim_rate'], 1) . '% with closure rate at ' . number_format((float)$kpi['closure_rate'], 1) . '%.';
        $insights[] = 'SLA compliance is ' . number_format((float)$kpi['sla_compliance'], 1) . '% and open claims are currently ' . number_format((int)$kpi['open_claims']) . '.';

        if (($kpi['avg_resolution_hours'] ?? null) !== null) {
            $insights[] = 'Average claim resolution time is ' . number_format((float)$kpi['avg_resolution_hours'], 1) . ' hours.';
        }

        $peakClaims = max($claimTrend);
        if ($peakClaims > 0) {
            $peakIndex = array_search($peakClaims, $claimTrend, true);
            if ($peakIndex !== false && isset($trendLabels[$peakIndex])) {
                $resolved = (int)($resolvedTrend[$peakIndex] ?? 0);
                $insights[] = 'Highest claim month was ' . $trendLabels[$peakIndex] . ' with ' . $peakClaims . ' claims (' . $resolved . ' resolved).';
            }
        }

        if (!empty($statusRows)) {
            $topStatus = $statusRows[0];
            $statusName = ucwords(str_replace('_', ' ', (string)data_get($topStatus, 'status_name', 'new')));
            $statusCount = (int)data_get($topStatus, 'total', 0);
            $insights[] = 'Most common claim status is ' . $statusName . ' with ' . $statusCount . ' claims.';
        }

        return $insights;
    }
}
