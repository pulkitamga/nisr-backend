<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\SerialImport;
use App\Models\Warranty;
use App\Models\ActivationReview;
use App\Models\Blacklist;
use App\Models\Product;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Models\WarrantyClaim;
use App\Models\WarrantyClaimCharge;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\ReportPdfService;

class ValidationHeading implements WithHeadingRow {}

class WarrantyController extends Controller
{
    private const MAX_WARRANTY_LIST_LIMIT = 500;

    public function importView(): \Illuminate\Contracts\View\View
    {
        $history = $this->buildImportHistoryQuery(request())
            ->paginate($this->resolveListPerPage(request()))
            ->appends(request()->query());

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
                'product_sku' => 'nullable|string|exists:products,code',
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
            fputcsv($csv, ['Row', 'Serial Number', 'Product SKU', 'Warranty Months', 'Error']);

            foreach ($errors as $err) {
                fputcsv($csv, [
                    $err['row'],
                    $err['data']['serial_number'] ?? '',
                    $err['data']['product_sku'] ?? '',
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
                fputcsv($csv, ['Serial Number', 'Product SKU', 'Warranty Months', 'Error']);

                foreach ($import->errors as $err) {
                    fputcsv($csv, [
                        $err['row']['serial_number'] ?? '',
                        $err['row']['product_sku'] ?? '',
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
            fputcsv($csv, ['Serial Number', 'Product SKU', 'Warranty Months', 'Error']);

            foreach ($import->errors as $err) {
                fputcsv($csv, [
                    $err['row']['serial_number'] ?? '',
                    $err['row']['product_sku'] ?? '',
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
    public function importHistory(Request $request)
    {
        $history = $this->buildImportHistoryQuery($request)
            ->paginate($this->resolveListPerPage($request))
            ->appends($request->query());

        return view('admin-views.warranty.import-history', compact('history'));
    }

    public function exportImportHistory(Request $request): StreamedResponse
    {
        $historyQuery = $this->buildImportHistoryQuery($request);
        $limit = $this->resolveResultsLimit($request);
        if ($limit !== null) {
            $historyQuery->limit($limit);
        }

        $history = $historyQuery->get();

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

    public function manualActivateCustomerSuggestions(Request $request)
    {
        $phone = trim((string)$request->query('phone', ''));
        $email = trim((string)$request->query('email', ''));

        if ($phone === '' && $email === '') {
            return response()->json(['customers' => []]);
        }

        $phoneDigits = preg_replace('/\D+/', '', $phone);

        $customers = User::query()
            ->where('user_type', 0)
            ->where(function ($query) use ($email, $phoneDigits) {
                if ($email !== '') {
                    $query->where('email', 'like', '%' . $email . '%');
                }

                if ($phoneDigits !== '') {
                    $method = $email !== '' ? 'orWhereRaw' : 'whereRaw';
                    $query->{$method}(
                        "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, '+', ''), '-', ''), ' ', ''), '(', ''), ')', '') like ?",
                        ['%' . $phoneDigits . '%']
                    );
                }
            })
            ->select('id', 'name', 'f_name', 'l_name', 'phone', 'email')
            ->limit(8)
            ->get()
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => trim(($user->f_name ?? '') . ' ' . ($user->l_name ?? '')) ?: ($user->name ?? ('Customer #' . $user->id)),
                    'phone' => $user->phone,
                    'email' => $user->email,
                ];
            })
            ->values();

        return response()->json(['customers' => $customers]);
    }

    // Manual Activate
    public function manualActivate(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|string|exists:warranties,serial_number',
            'purchase_date' => 'required|date',
            'reason' => 'required|string',
            'customer_phone' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'final_user_id' => 'nullable|exists:users,id',
            'docs' => 'nullable|file|mimes:pdf,jpg|max:2048',
        ], [
            'serial_number.required' => translate('Please enter a serial number.'),
            'serial_number.exists' => translate('The entered serial number was not found in warranties.'),
            'purchase_date.required' => translate('Please select the purchase date.'),
            'purchase_date.date' => translate('The purchase date must be a valid date.'),
            'reason.required' => translate('Please enter the reason for manual activation.'),
            'customer_phone.max' => translate('The phone number may not be greater than 50 characters.'),
            'customer_email.email' => translate('Please enter a valid email address.'),
            'customer_email.max' => translate('The email may not be greater than 255 characters.'),
            'final_user_id.exists' => translate('The selected customer profile is invalid. Please choose a valid customer from suggestions.'),
            'docs.file' => translate('The uploaded document must be a valid file.'),
            'docs.mimes' => translate('The document must be a PDF or JPG file.'),
            'docs.max' => translate('The document may not be greater than 2 MB.'),
        ]);

        if (Warranty::query()->where('serial_number', $request->serial_number)->active()->exists()) {
            Toastr::error(translate('An active warranty already exists for this serial.'));
            return back();
        }

        $warranty = Warranty::where('serial_number', $request->serial_number)->firstOrFail();
        $docPath = $request->hasFile('docs') ? $request->file('docs')->store('warranty/manual', 'public') : null;
        $customer = $request->filled('final_user_id')
            ? User::query()->where('user_type', 0)->find($request->final_user_id)
            : null;

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
            'final_user_id' => $customer?->id,
            'activated_by_name' => $customer ? (trim(($customer->f_name ?? '') . ' ' . ($customer->l_name ?? '')) ?: $customer->name) : null,
            'activated_by_phone' => $request->customer_phone ?: $customer?->phone,
            'activated_by_email' => $request->customer_email ?: $customer?->email,
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
        $activations = $this->buildActivationListQuery($request)
            ->paginate($this->resolveListPerPage($request))
            ->appends($request->query());

        return view('admin-views.warranty.activation-list', compact('activations'));
    }

    public function exportActivationList(Request $request): StreamedResponse
    {
        $query = $this->buildActivationListQuery($request);
        $limit = $this->resolveResultsLimit($request);
        if ($limit !== null) {
            $query->limit($limit);
        }

        $activations = $query->latest('id')->get();

        return response()->streamDownload(function () use ($activations) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Serial', 'Customer', 'Method', 'Start Date', 'End Date', 'Status']);

            foreach ($activations as $warranty) {
                fputcsv($handle, [
                    $warranty->serial_number,
                    $warranty->user?->name ?? $warranty->activated_by_name,
                    $this->resolveActivationMethodLabel((string) $warranty->activation_method),
                    optional($warranty->start_date)->format('Y-m-d'),
                    optional($warranty->end_date)->format('Y-m-d'),
                    translate($warranty->statusLabel()),
                ]);
            }

            fclose($handle);
        }, 'warranty-activations-' . now()->format('Ymd_His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function activationView(Warranty $warranty)
    {
        $warranty->load([
            'user',
            'product',
            'claims',
            'timelineEvents.user',
            'originalWarranty',
            'replacements.newWarranty',
            'replacements.technician'
        ]);
        return view('admin-views.warranty.activation-view', compact('warranty'));
    }

    public function blacklistView(Request $request)
    {
        $blacklists = $this->buildBlacklistQuery($request)
            ->paginate($this->resolveListPerPage($request))
            ->appends($request->query());

        return view('admin-views.warranty.blacklist', compact('blacklists'));
    }

    public function exportBlacklist(Request $request): StreamedResponse
    {
        $query = $this->buildBlacklistQuery($request);
        $limit = $this->resolveResultsLimit($request);
        if ($limit !== null) {
            $query->limit($limit);
        }

        $blacklists = $query->latest('id')->get();

        return response()->streamDownload(function () use ($blacklists) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Serial Number', 'Reason', 'Blacklisted At']);

            foreach ($blacklists as $item) {
                fputcsv($handle, [
                    $item->serial_number,
                    $item->reason,
                    optional($item->blacklisted_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, 'warranty-blacklist-' . now()->format('Ymd_His') . '.csv', ['Content-Type' => 'text/csv']);
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

    private function buildImportHistoryQuery(Request $request)
    {
        $query = Warranty::query()
            ->where('status', 'preactivated')
            ->selectRaw('DATE(created_at) as import_date, COUNT(*) as count')
            ->groupBy('import_date')
            ->orderBy('import_date', 'desc');

        if ($request->filled('searchValue')) {
            $searchValue = $this->sanitizeListSearch($request->input('searchValue'));
            $query->whereRaw('DATE(created_at) LIKE ?', ['%' . $searchValue . '%']);
        }

        return $query;
    }

    private function buildActivationListQuery(Request $request)
    {
        $query = Warranty::with('user', 'product');

        if ($request->filled('searchValue')) {
            $query->where('serial_number', 'like', '%' . $this->sanitizeListSearch($request->input('searchValue')) . '%');
        }

        if ($request->filled('method')) {
            $query->where('activation_method', $request->input('method'));
        }

        return $query->latest('id');
    }

    private function buildBlacklistQuery(Request $request)
    {
        $query = Blacklist::with('warranty');

        if ($request->filled('searchValue')) {
            $searchValue = $this->sanitizeListSearch($request->input('searchValue'));
            $query->where(function ($subQuery) use ($searchValue) {
                $subQuery->where('serial_number', 'like', '%' . $searchValue . '%')
                    ->orWhereHas('warranty', function ($warrantyQuery) use ($searchValue) {
                        $warrantyQuery->where('serial_number', 'like', '%' . $searchValue . '%');
                    });
            });
        }

        return $query->latest('id');
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

        return min($limit, self::MAX_WARRANTY_LIST_LIMIT);
    }

    private function sanitizeListSearch(?string $value): string
    {
        return mb_substr(trim((string) $value), 0, 100);
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
                    translate($warranty->status),
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
    public function reportClaims(Request $request): View|BinaryFileResponse|Response
    {
        [$fromDate, $toDate] = $this->resolveAnalyticsDateRange($request);
        $isRtl = app()->getLocale() === 'ar' || session('direction') === 'rtl';

        if ($isRtl) {
            $fromDateDisplay = $fromDate->translatedFormat('d F Y');
            $toDateDisplay   = $toDate->translatedFormat('d F Y');
        } else {
            $fromDateDisplay = $fromDate->format('d M, Y');
            $toDateDisplay   = $toDate->format('d M, Y');
        }
        $filters = [
            'date_type' => (string)$request->input('date_type', 'this_year'),
            'from' => $fromDate->toDateString(),
            'to' => $toDate->toDateString(),
            'status' => (string)$request->input('status', 'all'),
            'search' => trim((string)$request->input('search', '')),
        ];

        $claimsQuery = WarrantyClaim::query()
            ->where(function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('submitted_at', [$fromDate, $toDate])
                    ->orWhere(function ($fallbackQuery) use ($fromDate, $toDate) {
                        $fallbackQuery->whereNull('submitted_at')
                            ->whereBetween('created_at', [$fromDate, $toDate]);
                    });
            });

        if ($filters['status'] !== '' && $filters['status'] !== 'all') {
            $claimsQuery->where('status', $filters['status']);
        }

        if ($filters['search'] !== '') {
            $search = $filters['search'];
            $claimsQuery->where(function ($query) use ($search) {
                $query->where('claim_number', 'like', '%' . $search . '%')
                    ->orWhere('serial_number', 'like', '%' . $search . '%');
            });
        }

        $totalClaims = (clone $claimsQuery)->count();
        $resolvedClaims = (clone $claimsQuery)->whereIn('status', ['resolved', 'closed'])->count();
        $rejectedClaims = (clone $claimsQuery)->where('status', 'rejected')->count();
        $openClaims = max(0, $totalClaims - $resolvedClaims - $rejectedClaims);
        $totalWarranties = Warranty::query()
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->count();

        $kpi = [
            'total_claims' => $totalClaims,
            'claim_rate' => $totalWarranties > 0 ? ($totalClaims / $totalWarranties) * 100 : 0,
            'open_claims' => $openClaims,
            'resolved_claims' => $resolvedClaims,
        ];

        $statusRows = (clone $claimsQuery)
            ->selectRaw("COALESCE(NULLIF(status, ''), 'unknown') as status_name")
            ->selectRaw('COUNT(*) as total')
            ->groupBy(DB::raw("COALESCE(NULLIF(status, ''), 'unknown')"))
            ->orderByDesc('total')
            ->get();

        $trendUnit = $this->resolveReportTrendUnit($fromDate, $toDate);
        $periodKeys = $this->buildAnalyticsPeriodKeys($fromDate, $toDate, $trendUnit);
        $trendSelect = $this->resolveTrendSelectExpression('COALESCE(submitted_at, created_at)', $trendUnit);

        $trendRows = (clone $claimsQuery)
            ->selectRaw($trendSelect . ' as period_key')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('period_key')
            ->get();
        $trendMap = $trendRows->pluck('total', 'period_key')->all();

        $trendLabels = [];
        $trendValues = [];
        foreach ($periodKeys as $periodKey) {
            $trendLabels[] = $this->formatAnalyticsPeriodLabel($periodKey, $trendUnit);
            $trendValues[] = (int)($trendMap[$periodKey] ?? 0);
        }

        $statusChartData = [
            'labels' => $statusRows
                ->pluck('status_name')
                ->map(fn($value) => ucwords(str_replace('_', ' ', (string)$value)))
                ->values()
                ->all(),
            'counts' => $statusRows
                ->pluck('total')
                ->map(fn($value) => (int)$value)
                ->values()
                ->all(),
        ];

        $trendChartData = [
            'labels' => $trendLabels,
            'counts' => $trendValues,
        ];

        $detailQuery = (clone $claimsQuery)
            ->with(['warranty.user', 'warranty.product', 'branch'])
            ->orderByRaw('COALESCE(submitted_at, created_at) DESC');

        $download = (string)$request->input('download', '');
        if ($download === 'excel') {
            $rows = $detailQuery->get()->map(function (WarrantyClaim $claim) {
                return [
                    (string)$claim->claim_number,
                    (string)$claim->serial_number,
                    ucwords(str_replace('_', ' ', (string)$claim->status)),
                    $this->resolveClaimCustomerName($claim),
                    optional($claim->submitted_at ?? $claim->created_at)->format('Y-m-d H:i:s'),
                    optional($claim->resolution_due)->format('Y-m-d H:i:s') ?? '-',
                    $claim->branch?->branch_name ?? '-',
                ];
            })->values()->all();

            return Excel::download(new class($rows) implements FromArray, WithHeadings, WithStyles, WithEvents, ShouldAutoSize {
                public function __construct(private readonly array $rows) {}
                public function array(): array
                {
                    return $this->rows;
                }
                public function headings(): array
                {
                    return ['Claim Number', 'Serial', 'Status', 'Customer', 'Submitted At', 'Resolution Due', 'Branch'];
                }
                public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
                {
                    return [
                        // Row 1: Green Header with Bold White Text
                        1 => [
                            'font' => [
                                'bold' => true,
                                'color' => ['argb' => 'FFFFFFFF'],
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FF239E92'],
                            ],
                        ],
                    ];
                }

                public function registerEvents(): array
                {
                    return [
                        \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                            // 1. Remove gridlines for a clean white background
                            $event->sheet->getDelegate()->setShowGridlines(false);

                            // 2. Calculate the data range (Columns A to G)
                            $lastRow = count($this->rows) + 1;
                            $range = "A1:G{$lastRow}";

                            // 3. Apply Thick Black Outline and Light Inside Borders
                            $event->sheet->getStyle($range)->applyFromArray([
                                'borders' => [
                                    'outline' => [
                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                                        'color' => ['argb' => 'FF000000'],
                                    ],
                                    'inside' => [
                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                        'color' => ['argb' => 'FFD1D5DB'],
                                    ],
                                ],
                                'alignment' => [
                                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                                ],
                            ]);
                        },
                    ];
                }
            }, 'warranty-claims-report.xlsx');
        }

        if ($download === 'pdf') {

            $isRtl = app()->getLocale() === 'ar' || session('direction') === 'rtl';

            $claimsForPdf = $detailQuery->get();

            // Receive chart images from JS
            $statusChartImage = $request->input('status_chart');
            $trendChartImage  = $request->input('trend_chart');

            return app(ReportPdfService::class)->download(
                view: 'admin-views.warranty.report-claims-pdf',
                data: array_merge(
                    compact(
                        'kpi',
                        'claimsForPdf',
                        'fromDate',
                        'toDate',
                        'fromDateDisplay',
                        'toDateDisplay',
                        'filters',
                        'isRtl',
                        'statusChartImage',
                        'trendChartImage'
                    ),
                    [
                        'report_title' => translate('warranty_claims_report')
                    ]
                ),

                fileName: 'warranty-claims-report.pdf',
                orientation: 'landscape'
            );
        }
        $dataLimit = getWebConfig('pagination_limit') ?? 10;
        $claims = $detailQuery->paginate($dataLimit)->withQueryString();

        return view('admin-views.warranty.report-claims', compact(
            'kpi',
            'filters',
            'claims',
            'statusChartData',
            'trendChartData',
            'fromDate',
            'toDate',
            'fromDateDisplay',
            'toDateDisplay'
        ));
    }

    // Reports (SLA)
    public function reportSLA(Request $request): View|BinaryFileResponse|Response
    {
        [$fromDate, $toDate] = $this->resolveAnalyticsDateRange($request);
        $filters = [
            'date_type' => (string)$request->input('date_type', 'this_year'),
            'from' => $fromDate->toDateString(),
            'to' => $toDate->toDateString(),
            'sla_type' => (string)$request->input('sla_type', 'all'),
        ];

        if (!in_array($filters['sla_type'], ['all', 'response', 'resolution'], true)) {
            $filters['sla_type'] = 'all';
        }

        if ($filters['sla_type'] === 'response') {
            $slaRowsQuery = DB::query()->fromSub(
                $this->buildSlaDeadlineQuery($fromDate, $toDate, 'response'),
                'sla_rows'
            );
        } elseif ($filters['sla_type'] === 'resolution') {
            $slaRowsQuery = DB::query()->fromSub(
                $this->buildSlaDeadlineQuery($fromDate, $toDate, 'resolution'),
                'sla_rows'
            );
        } else {
            $allDeadlinesQuery = $this->buildSlaDeadlineQuery($fromDate, $toDate, 'response')
                ->unionAll($this->buildSlaDeadlineQuery($fromDate, $toDate, 'resolution'));
            $slaRowsQuery = DB::query()->fromSub($allDeadlinesQuery, 'sla_rows');
        }

        $slaSummaryRows = (clone $slaRowsQuery)->orderByDesc('due_date')->get();
        $totalDeadlines = $slaSummaryRows->count();
        $onTime = $slaSummaryRows->where('is_within_sla', 1)->count();
        $breached = max(0, $totalDeadlines - $onTime);

        $lateRows = $slaSummaryRows->where('is_within_sla', 0);
        $averageBreachHours = null;
        if ($lateRows->isNotEmpty()) {
            $totalLateHours = $lateRows->sum(function ($row) {
                try {
                    $dueDate = Carbon::parse((string)$row->due_date);
                    $reference = $row->completed_at ? Carbon::parse((string)$row->completed_at) : now();
                    return $reference->gt($dueDate) ? $dueDate->diffInHours($reference) : 0;
                } catch (\Throwable $exception) {
                    return 0;
                }
            });
            $averageBreachHours = round($totalLateHours / $lateRows->count(), 1);
        }

        $kpi = [
            'total_deadlines' => $totalDeadlines,
            'on_time' => $onTime,
            'breached' => $breached,
            'compliance' => $totalDeadlines > 0 ? ($onTime / $totalDeadlines) * 100 : 0,
            'avg_breach_hours' => $averageBreachHours,
        ];

        $typeRows = (clone $slaRowsQuery)
            ->selectRaw("COALESCE(NULLIF(sla_type_key, ''), 'unknown') as sla_type_key")
            ->selectRaw('COUNT(*) as total')
            ->groupBy(DB::raw("COALESCE(NULLIF(sla_type_key, ''), 'unknown')"))
            ->orderByDesc('total')
            ->get();

        $trendUnit = $this->resolveReportTrendUnit($fromDate, $toDate);
        $periodKeys = $this->buildAnalyticsPeriodKeys($fromDate, $toDate, $trendUnit);
        $trendSelect = $this->resolveTrendSelectExpression('due_date', $trendUnit);

        $trendRows = (clone $slaRowsQuery)
            ->selectRaw($trendSelect . ' as period_key')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN is_within_sla = 0 THEN 1 ELSE 0 END) as breached_total')
            ->groupBy('period_key')
            ->get();
        $totalMap = $trendRows->pluck('total', 'period_key')->all();
        $breachedMap = $trendRows->pluck('breached_total', 'period_key')->all();

        $trendLabels = [];
        $trendTotals = [];
        $trendBreached = [];
        foreach ($periodKeys as $periodKey) {
            $trendLabels[] = $this->formatAnalyticsPeriodLabel($periodKey, $trendUnit);
            $trendTotals[] = (int)($totalMap[$periodKey] ?? 0);
            $trendBreached[] = (int)($breachedMap[$periodKey] ?? 0);
        }

        $slaComplianceChartData = [
            'labels' => [translate('on_time'), translate('breached')],
            'counts' => [(int)$onTime, (int)$breached],
        ];

        $slaTypeChartData = [
            'labels' => $typeRows
                ->pluck('sla_type_key')
                ->map(function ($type) {
                    return $type === 'response'
                        ? translate('first_response_sla')
                        : ($type === 'resolution' ? translate('resolution_sla') : ucwords(str_replace('_', ' ', (string)$type)));
                })
                ->values()
                ->all(),
            'counts' => $typeRows
                ->pluck('total')
                ->map(fn($value) => (int)$value)
                ->values()
                ->all(),
        ];

        $slaTrendChartData = [
            'labels' => $trendLabels,
            'total' => $trendTotals,
            'breached' => $trendBreached,
        ];

        $download = (string)$request->input('download', '');
        if ($download === 'excel') {
            $rows = $slaSummaryRows->map(function ($row) {
                $slaLabel = $row->sla_type_key === 'response'
                    ? translate('first_response_sla')
                    : translate('resolution_sla');
                return [
                    (string)$row->claim_number,
                    (string)$row->serial_number,
                    (string)$row->product_name,
                    $slaLabel,
                    Carbon::parse((string)$row->due_date)->format('Y-m-d H:i:s'),
                    $row->completed_at ? Carbon::parse((string)$row->completed_at)->format('Y-m-d H:i:s') : '-',
                    ((int)$row->is_within_sla === 1) ? translate('on_time') : translate('breached'),
                    ucwords(str_replace('_', ' ', (string)$row->status)),
                ];
            })->values()->all();

            return Excel::download(new class($rows) implements FromArray, WithHeadings, WithStyles, WithEvents, ShouldAutoSize {
                public function __construct(private readonly array $rows) {}
                public function array(): array
                {
                    return $this->rows;
                }
                public function headings(): array
                {
                    return ['Claim Number', 'Serial', 'Product', 'SLA Type', 'Due Date', 'Completed At', 'SLA Status', 'Claim Status'];
                }
                public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
                {
                    $lastColumn = $sheet->getHighestColumn();
                    // Apply green header only to the data columns in Row 1
                    $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['argb' => 'FFFFFFFF'],
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FF239E92'],
                        ],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    return [];
                }

                public function registerEvents(): array
                {
                    return [
                        \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                            // 1. Hide default gridlines for white background
                            $event->sheet->getDelegate()->setShowGridlines(false);

                            // 2. Define range based on data
                            $lastColumn = $event->sheet->getHighestColumn();
                            $lastRow = count($this->rows) + 1;
                            $range = "A1:{$lastColumn}{$lastRow}";

                            // 3. Apply Thick Black Outline and Light Inside Borders
                            $event->sheet->getStyle($range)->applyFromArray([
                                'borders' => [
                                    'outline' => [
                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                                        'color' => ['argb' => 'FF000000'],
                                    ],
                                    'inside' => [
                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                        'color' => ['argb' => 'FFD1D5DB'],
                                    ],
                                ],
                                'alignment' => [
                                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                                ],
                            ]);
                        },
                    ];
                }
            }, 'warranty-sla-report.xlsx');
        }

        if ($download === 'pdf') {
            $isRtl = app()->getLocale() === 'ar' || session('direction') === 'rtl';
            $slaRowsForPdf = $slaSummaryRows->values();

            // चार्ट इमेज प्राप्त करें (POST से आई हैं)
            $complianceChartImage = $request->input('compliance_chart');
            $typeChartImage = $request->input('type_chart');
            $trendChartImage = $request->input('trend_chart');

            // कंपनी का लोगो और नाम (जरूरत हो तो)
            $companyName = getWebConfig('company_name') ?? 'ElNisr';
            $companyLogo = getWebConfig('company_web_logo') ?? [];

            return app(ReportPdfService::class)->download(
                view: 'admin-views.warranty.report-sla-pdf',
                data: array_merge(
                    compact(
                        'kpi',
                        'slaRowsForPdf',
                        'fromDate',
                        'toDate',
                        'filters',
                        'isRtl',
                        'complianceChartImage',
                        'typeChartImage',
                        'trendChartImage',
                        'companyName',
                        'companyLogo'
                    ),
                    [
                        'report_title' => translate('sla_report')
                    ]
                ),

                fileName: 'warranty-sla-report.pdf',
                orientation: 'landscape'
            );
        }

        $dataLimit = getWebConfig('pagination_limit') ?? 10;
        $slaDetails = (clone $slaRowsQuery)
            ->orderByDesc('due_date')
            ->paginate($dataLimit)
            ->withQueryString();

        return view('admin-views.warranty.report-sla', compact(
            'kpi',
            'filters',
            'slaDetails',
            'slaComplianceChartData',
            'slaTypeChartData',
            'slaTrendChartData',
            'fromDate',
            'toDate'
        ));
    }
    // Reports (Activations)
    public function reportActivations(Request $request): View|BinaryFileResponse|Response
    {
        [$fromDate, $toDate] = $this->resolveAnalyticsDateRange($request);
        $filters = [
            'date_type' => (string)$request->input('date_type', 'this_year'),
            'from' => $fromDate->toDateString(),
            'to' => $toDate->toDateString(),
            'activation_method' => (string)$request->input('activation_method', 'all'),
            'search' => trim((string)$request->input('search', '')),
        ];

        $activationQuery = Warranty::query()
            ->whereNotNull('activation_date')
            ->whereBetween('activation_date', [$fromDate, $toDate]);

        if ($filters['activation_method'] !== '' && $filters['activation_method'] !== 'all') {
            $activationQuery->where('activation_method', $filters['activation_method']);
        }

        if ($filters['search'] !== '') {
            $search = $filters['search'];
            $activationQuery->where(function ($query) use ($search) {
                $query->where('serial_number', 'like', '%' . $search . '%')
                    ->orWhereHas('product', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $totalActivations = (clone $activationQuery)->count();
        $activeWarranties = (clone $activationQuery)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->count();
        $expiredWarranties = (clone $activationQuery)
            ->whereNotNull('end_date')
            ->where('end_date', '<', now())
            ->count();
        $averageMonthsRaw = (clone $activationQuery)->avg('warranty_months');

        $kpi = [
            'total_activations' => $totalActivations,
            'active_warranties' => $activeWarranties,
            'expired_warranties' => $expiredWarranties,
            'activation_rate' => $totalActivations > 0 ? ($activeWarranties / $totalActivations) * 100 : 0,
            'avg_warranty_months' => $averageMonthsRaw !== null ? round((float)$averageMonthsRaw, 1) : null,
        ];

        $methodRows = (clone $activationQuery)
            ->selectRaw("COALESCE(NULLIF(activation_method, ''), 'unknown') as method_key")
            ->selectRaw('COUNT(*) as total')
            ->groupBy(DB::raw("COALESCE(NULLIF(activation_method, ''), 'unknown')"))
            ->orderByDesc('total')
            ->get();

        $methodBreakdown = $methodRows->map(function ($row) use ($totalActivations) {
            $count = (int)$row->total;
            return [
                'method_key' => (string)$row->method_key,
                'label' => $this->resolveActivationMethodLabel((string)$row->method_key),
                'count' => $count,
                'percentage' => $totalActivations > 0 ? round(($count / $totalActivations) * 100, 2) : 0.0,
            ];
        })->values();

        $trendUnit = $this->resolveReportTrendUnit($fromDate, $toDate);
        $periodKeys = $this->buildAnalyticsPeriodKeys($fromDate, $toDate, $trendUnit);
        $trendSelect = $this->resolveTrendSelectExpression('activation_date', $trendUnit);

        $trendRows = (clone $activationQuery)
            ->selectRaw($trendSelect . ' as period_key')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('period_key')
            ->get();
        $trendMap = $trendRows->pluck('total', 'period_key')->all();

        $trendLabels = [];
        $trendValues = [];
        foreach ($periodKeys as $periodKey) {
            $trendLabels[] = $this->formatAnalyticsPeriodLabel($periodKey, $trendUnit);
            $trendValues[] = (int)($trendMap[$periodKey] ?? 0);
        }

        $activationTrendChartData = [
            'labels' => $trendLabels,
            'counts' => $trendValues,
        ];

        $activationMethodChartData = [
            'labels' => $methodBreakdown->pluck('label')->values()->all(),
            'counts' => $methodBreakdown->pluck('count')->map(fn($value) => (int)$value)->values()->all(),
        ];

        $topProducts = (clone $activationQuery)
            ->leftJoin('products', 'products.id', '=', 'warranties.product_id')
            ->selectRaw("COALESCE(products.name, 'Unknown') as product_name")
            ->selectRaw('COUNT(*) as total')
            ->groupBy(DB::raw("COALESCE(products.name, 'Unknown')"))
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $detailQuery = (clone $activationQuery)
            ->with(['product', 'user', 'branch'])
            ->orderByDesc('activation_date');

        $download = (string)$request->input('download', '');
        if ($download === 'excel') {
            $rows = $detailQuery->get()->map(function (Warranty $warranty) {
                return [
                    (string)$warranty->serial_number,
                    $warranty->product?->name ?? '-',
                    $this->resolveWarrantyCustomerName($warranty),
                    $warranty->branch?->branch_name ?? '-',
                    $this->resolveActivationMethodLabel((string)$warranty->activation_method),
                    optional($warranty->activation_date)->format('Y-m-d H:i:s') ?? '-',
                    ucwords(str_replace('_', ' ', (string)$warranty->status)),
                    optional($warranty->end_date)->format('Y-m-d') ?? '-',
                ];
            })->values()->all();

            return Excel::download(new class($rows) implements FromArray, WithHeadings, WithStyles, WithEvents, ShouldAutoSize {
                public function __construct(private readonly array $rows) {}
                public function array(): array
                {
                    return $this->rows;
                }
                public function headings(): array
                {
                    return ['Serial', 'Product', 'Customer', 'Branch', 'Activation Method', 'Activated At', 'Status', 'Warranty End'];
                }
                public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
                {
                    return [
                        // Row 1: Green Header with Bold White Text
                        1 => [
                            'font' => [
                                'bold' => true,
                                'color' => ['argb' => 'FFFFFFFF'],
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FF239E92'],
                            ],
                        ],
                    ];
                }

                public function registerEvents(): array
                {
                    return [
                        \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                            // 1. Hide Gridlines (makes background pure white)
                            $event->sheet->getDelegate()->setShowGridlines(false);

                            // 2. Calculate the data range (A to H based on headings)
                            $lastRow = count($this->rows) + 1;
                            $range = "A1:H{$lastRow}";

                            // 3. Apply Thick Black Outside Border and Light Inside Borders
                            $event->sheet->getStyle($range)->applyFromArray([
                                'borders' => [
                                    'outline' => [
                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                                        'color' => ['argb' => 'FF000000'],
                                    ],
                                    'inside' => [
                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                        'color' => ['argb' => 'FFD1D5DB'],
                                    ],
                                ],
                                'alignment' => [
                                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                                ],
                            ]);
                        },
                    ];
                }
            }, 'warranty-activations-report.xlsx');
        }

        if ($download === 'pdf') {
            $isRtl = app()->getLocale() === 'ar' || session('direction') === 'rtl';
            $activationRowsForPdf = $detailQuery->get();

            // Get chart images from request
            $trendChartImage = $request->input('trend_chart');
            $methodChartImage = $request->input('method_chart');

            return app(ReportPdfService::class)->download(
                view: 'admin-views.warranty.report-activations-pdf',
                data: array_merge(
                    compact(
                        'kpi',
                        'methodBreakdown',
                        'topProducts',
                        'activationRowsForPdf',
                        'fromDate',
                        'toDate',
                        'filters',
                        'isRtl',
                        'trendChartImage',
                        'methodChartImage'
                    ),
                    [
                        'report_title' => translate('activation_report')
                    ]
                ),
                fileName: 'warranty-activations-report.pdf',
                orientation: 'landscape'
            );
        }

        $dataLimit = getWebConfig('pagination_limit') ?? 10;
        $activations = $detailQuery->paginate($dataLimit)->withQueryString();

        return view('admin-views.warranty.report-activations', compact(
            'kpi',
            'filters',
            'methodBreakdown',
            'topProducts',
            'activations',
            'activationTrendChartData',
            'activationMethodChartData',
            'fromDate',
            'toDate'
        ));
    }

    private function buildSlaDeadlineQuery(Carbon $fromDate, Carbon $toDate, string $slaType)
    {
        $isResponse = $slaType === 'response';
        $dueColumn = $isResponse ? 'warranty_claims.response_due' : 'warranty_claims.resolution_due';
        $completedColumn = $isResponse ? 'warranty_claims.first_response_at' : 'warranty_claims.resolved_at';
        $typeLiteral = $isResponse ? 'response' : 'resolution';

        return WarrantyClaim::query()
            ->leftJoin('warranties', 'warranties.id', '=', 'warranty_claims.warranty_id')
            ->leftJoin('products', 'products.id', '=', 'warranties.product_id')
            ->selectRaw('warranty_claims.id as claim_id')
            ->selectRaw('warranty_claims.claim_number as claim_number')
            ->selectRaw("COALESCE(warranty_claims.serial_number, warranties.serial_number, '-') as serial_number")
            ->selectRaw("COALESCE(products.name, '-') as product_name")
            ->selectRaw('warranty_claims.status as status')
            ->selectRaw("'" . $typeLiteral . "' as sla_type_key")
            ->selectRaw($dueColumn . ' as due_date')
            ->selectRaw($completedColumn . ' as completed_at')
            ->selectRaw(
                'CASE ' .
                    'WHEN ' . $completedColumn . ' IS NOT NULL THEN IF(' . $completedColumn . ' <= ' . $dueColumn . ', 1, 0) ' .
                    'ELSE IF(' . $dueColumn . ' >= NOW(), 1, 0) ' .
                    'END as is_within_sla'
            )
            ->whereNotNull($dueColumn)
            ->whereBetween($dueColumn, [$fromDate, $toDate]);
    }

    private function resolveReportTrendUnit(Carbon $fromDate, Carbon $toDate): string
    {
        $days = $fromDate->diffInDays($toDate);
        if ($days <= 31) {
            return 'day';
        }
        if ($days <= 180) {
            return 'week';
        }
        return 'month';
    }

    private function resolveTrendSelectExpression(string $columnExpression, string $unit): string
    {
        return match ($unit) {
            'day' => 'DATE(' . $columnExpression . ')',
            'week' => "DATE_FORMAT(" . $columnExpression . ", '%x-W%v')",
            default => "DATE_FORMAT(" . $columnExpression . ", '%Y-%m')",
        };
    }

    private function resolveActivationMethodLabel(string $methodKey): string
    {
        $normalized = trim($methodKey) !== '' ? $methodKey : 'unknown';
        $translated = translate($normalized);
        if ($translated === $normalized) {
            return ucwords(str_replace('_', ' ', $normalized));
        }
        return $translated;
    }

    private function resolveWarrantyCustomerName(?Warranty $warranty): string
    {
        if (!$warranty) {
            return '-';
        }

        $customer = $warranty->user;
        if ($customer) {
            $fullName = trim(((string)($customer->f_name ?? '')) . ' ' . ((string)($customer->l_name ?? '')));
            if ($fullName !== '') {
                return $fullName;
            }
            if (!empty($customer->name)) {
                return (string)$customer->name;
            }
        }

        return (string)($warranty->activated_by_name ?: '-');
    }

    private function resolveClaimCustomerName(WarrantyClaim $claim): string
    {
        $resolvedName = $this->resolveWarrantyCustomerName($claim->warranty);
        if ($resolvedName !== '-') {
            return $resolvedName;
        }

        return (string)($claim->activated_by_name ?? '-');
    }

    public function reportAnalytics(Request $request): View|BinaryFileResponse|Response
    {
        [$snapshotFrom, $snapshotTo] = $this->resolveAnalyticsDateRange($request);
        $filters = [
            'date_type' => (string)$request->input('date_type', 'this_year'),
            'from' => $snapshotFrom->toDateString(),
            'to' => $snapshotTo->toDateString(),
            'claim_status' => (string)$request->input('claim_status', ''),
            'product_id' => (int)$request->input('product_id', 0),
        ];
        $trendGrouping = $this->resolveAnalyticsTrendGrouping($snapshotFrom, $snapshotTo);
        $periodKeys = $this->buildAnalyticsPeriodKeys($snapshotFrom, $snapshotTo, $trendGrouping['unit']);

        $warrantyQuery = Warranty::query()
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo]);
        if ($filters['product_id'] > 0) {
            $warrantyQuery->where('product_id', $filters['product_id']);
        }
        $claimQuery = WarrantyClaim::query()
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo]);
        if ($filters['claim_status'] !== '') {
            $claimQuery->where('status', $filters['claim_status']);
        }
        if ($filters['product_id'] > 0) {
            $claimQuery->whereHas('warranty', fn($query) => $query->where('product_id', $filters['product_id']));
        }

        $totalWarranties = (clone $warrantyQuery)->count();
        $activeWarranties = (clone $warrantyQuery)->where('status', 'active')->count();
        $activatedInPeriod = Warranty::query()
            ->whereNotNull('activation_date')
            ->whereBetween('activation_date', [$snapshotFrom, $snapshotTo])
            ->when($filters['product_id'] > 0, fn($query) => $query->where('product_id', $filters['product_id']))
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
            ->whereBetween('activation_date', [$snapshotFrom, $snapshotTo])
            ->when($filters['product_id'] > 0, fn($query) => $query->where('product_id', $filters['product_id']))
            ->selectRaw($trendGrouping['activation_select'] . ' as period_key, COUNT(*) as total')
            ->groupBy('period_key')
            ->get();
        $claimTrendRows = WarrantyClaim::query()
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo])
            ->when($filters['claim_status'] !== '', fn($query) => $query->where('status', $filters['claim_status']))
            ->when($filters['product_id'] > 0, fn($query) => $query->whereHas('warranty', fn($subQuery) => $subQuery->where('product_id', $filters['product_id'])))
            ->selectRaw($trendGrouping['created_select'] . ' as period_key, COUNT(*) as total')
            ->groupBy('period_key')
            ->get();
        $resolvedTrendRows = WarrantyClaim::query()
            ->whereNotNull('resolved_at')
            ->whereBetween('resolved_at', [$snapshotFrom, $snapshotTo])
            ->when($filters['claim_status'] !== '', fn($query) => $query->where('status', $filters['claim_status']))
            ->when($filters['product_id'] > 0, fn($query) => $query->whereHas('warranty', fn($subQuery) => $subQuery->where('product_id', $filters['product_id'])))
            ->selectRaw($trendGrouping['resolved_select'] . ' as period_key, COUNT(*) as total')
            ->groupBy('period_key')
            ->get();

        $activationTrendMap = $activationTrendRows->pluck('total', 'period_key')->all();
        $claimTrendMap = $claimTrendRows->pluck('total', 'period_key')->all();
        $resolvedTrendMap = $resolvedTrendRows->pluck('total', 'period_key')->all();

        $trendLabels = [];
        $activationTrend = [];
        $claimTrend = [];
        $resolvedTrend = [];
        foreach ($periodKeys as $periodKey) {
            $trendLabels[] = $this->formatAnalyticsPeriodLabel($periodKey, $trendGrouping['unit']);
            $activationTrend[] = (int)($activationTrendMap[$periodKey] ?? 0);
            $claimTrend[] = (int)($claimTrendMap[$periodKey] ?? 0);
            $resolvedTrend[] = (int)($resolvedTrendMap[$periodKey] ?? 0);
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
            ->when($filters['claim_status'] !== '', fn($query) => $query->where('warranty_claims.status', $filters['claim_status']))
            ->when($filters['product_id'] > 0, fn($query) => $query->where('warranties.product_id', $filters['product_id']))
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

        $download = (string)$request->input('download', '');
        if ($download === 'excel') {
            $rows = $topProducts->map(fn($row) => [(string)$row->product_name, (int)$row->claims_count])->values()->all();
            return Excel::download(new class($rows) implements FromArray, WithHeadings, WithStyles, WithEvents, ShouldAutoSize {
                public function __construct(private readonly array $rows) {}
                public function array(): array
                {
                    return $this->rows;
                }
                public function headings(): array
                {
                    return ['Product', 'Claims'];
                }
                public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
                {
                    return [
                        // Row 1: Green Header (#239e92) with Bold White Text
                        1 => [
                            'font' => [
                                'bold' => true,
                                'color' => ['argb' => 'FFFFFFFF'],
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FF239E92'],
                            ],
                        ],
                    ];
                }

                public function registerEvents(): array
                {
                    return [
                        \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                            // 1. Hide default Excel gridlines (makes the background pure white)
                            $event->sheet->getDelegate()->setShowGridlines(false);

                            // 2. Define the data range (Columns A to B based on headings)
                            $lastRow = count($this->rows) + 1;
                            $range = "A1:B{$lastRow}";

                            // 3. Apply Thick Black Outside Border and Light Inside Borders
                            $event->sheet->getStyle($range)->applyFromArray([
                                'borders' => [
                                    'outline' => [
                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                                        'color' => ['argb' => 'FF000000'], // Solid Black
                                    ],
                                    'inside' => [
                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                        'color' => ['argb' => 'FFD1D5DB'], // Light Gray
                                    ],
                                ],
                                'alignment' => [
                                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                                ],
                            ]);
                        },
                    ];
                }
            }, 'warranty-analytics-report.xlsx');
        }

        if ($download === 'pdf') {
            $isRtl = app()->getLocale() === 'ar' || session('direction') === 'rtl';

            // Get chart images from request
            $trendChartImage = $request->input('trend_chart');
            $statusChartImage = $request->input('status_chart');
            $agingChartImage = $request->input('aging_chart');
            $chargeChartImage = $request->input('charge_chart');

            return app(ReportPdfService::class)->download(
                view: 'admin-views.warranty.report-analytics-pdf',
                data: array_merge(
                    compact(
                        'kpi',
                        'topProducts',
                        'snapshotFrom',
                        'snapshotTo',
                        'isRtl',
                        'trendChartImage',
                        'statusChartImage',
                        'agingChartImage',
                        'chargeChartImage'
                    ),
                    [
                        'report_title' => translate('warranty_analytics_report')
                    ]
                ),
                fileName: 'warranty-analytics-report.pdf',
                orientation: 'landscape'
            );
        }

        $products = Product::query()->select('id', 'name')->orderBy('name')->get();

        return view('admin-views.warranty.report-analytics', compact(
            'kpi',
            'trendChartData',
            'statusChartData',
            'agingChartData',
            'chargeChartData',
            'topProducts',
            'insights',
            'snapshotFrom',
            'snapshotTo',
            'filters',
            'products'
        ));
    }

    private function resolveAnalyticsDateRange(Request $request): array
    {
        $dateType = (string)$request->input('date_type', 'this_year');
        $from = $request->input('from');
        $to = $request->input('to');

        switch ($dateType) {
            case 'this_month':
                $fromDate = now()->startOfMonth()->startOfDay();
                $toDate = now()->endOfMonth()->endOfDay();
                break;
            case 'this_week':
                $fromDate = now()->startOfWeek()->startOfDay();
                $toDate = now()->endOfWeek()->endOfDay();
                break;
            case 'today':
                $fromDate = now()->startOfDay();
                $toDate = now()->endOfDay();
                break;
            case 'custom_date':
                $fromDate = $this->parseOptionalAnalyticsDate($from, now()->subDays(29)->startOfDay(), true);
                $toDate = $this->parseOptionalAnalyticsDate($to, now()->endOfDay(), false);
                break;
            case 'this_year':
            default:
                $fromDate = now()->startOfYear()->startOfDay();
                $toDate = now()->endOfYear()->endOfDay();
                break;
        }

        if ($fromDate->gt($toDate)) {
            [$fromDate, $toDate] = [$toDate->copy()->startOfDay(), $fromDate->copy()->endOfDay()];
        }

        return [$fromDate, $toDate];
    }

    private function parseOptionalAnalyticsDate(mixed $value, Carbon $fallback, bool $startOfDay): Carbon
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

    private function resolveAnalyticsTrendGrouping(Carbon $fromDate, Carbon $toDate): array
    {
        $days = $fromDate->diffInDays($toDate);
        if ($days <= 31) {
            return ['unit' => 'day', 'created_select' => 'DATE(created_at)', 'activation_select' => 'DATE(activation_date)', 'resolved_select' => 'DATE(resolved_at)'];
        }
        if ($days <= 180) {
            return ['unit' => 'week', 'created_select' => "DATE_FORMAT(created_at, '%x-W%v')", 'activation_select' => "DATE_FORMAT(activation_date, '%x-W%v')", 'resolved_select' => "DATE_FORMAT(resolved_at, '%x-W%v')"];
        }
        return ['unit' => 'month', 'created_select' => "DATE_FORMAT(created_at, '%Y-%m')", 'activation_select' => "DATE_FORMAT(activation_date, '%Y-%m')", 'resolved_select' => "DATE_FORMAT(resolved_at, '%Y-%m')"];
    }

    private function buildAnalyticsPeriodKeys(Carbon $fromDate, Carbon $toDate, string $unit): array
    {
        $keys = [];
        $cursor = $fromDate->copy();
        if ($unit === 'day') {
            while ($cursor->lte($toDate)) {
                $keys[] = $cursor->format('Y-m-d');
                $cursor->addDay();
            }
            return $keys;
        }
        if ($unit === 'week') {
            $cursor = $fromDate->copy()->startOfWeek();
            $limit = $toDate->copy()->endOfWeek();
            while ($cursor->lte($limit)) {
                $keys[] = $cursor->format('o-\WW');
                $cursor->addWeek();
            }
            return $keys;
        }
        $cursor = $fromDate->copy()->startOfMonth();
        $limit = $toDate->copy()->endOfMonth();
        while ($cursor->lte($limit)) {
            $keys[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }
        return $keys;
    }

    private function formatAnalyticsPeriodLabel(string $periodKey, string $unit): string
    {
        if ($unit === 'day') {
            return Carbon::parse($periodKey)->format('M d');
        }
        if ($unit === 'week') {
            [$year, $week] = explode('-W', $periodKey);
            return 'W' . $week . ' ' . $year;
        }
        return Carbon::createFromFormat('Y-m', $periodKey)->format('M Y');
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

        if (Warranty::query()->where('serial_number', $review->warranty?->serial_number)->active()->exists()) {
            Toastr::error(translate('An active warranty already exists for this serial.'));
            return back();
        }

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
            return [translate('no_warranty_activity_found_in_last_90_days')];
        }

        $insights = [];
        $insights[] = strtr(translate('warranty_insight_claim_and_closure_rate'), [
            ':claim_rate' => number_format((float)$kpi['claim_rate'], 1),
            ':closure_rate' => number_format((float)$kpi['closure_rate'], 1),
        ]);
        $insights[] = strtr(translate('warranty_insight_sla_open_claims'), [
            ':sla_compliance' => number_format((float)$kpi['sla_compliance'], 1),
            ':open_claims' => number_format((int)$kpi['open_claims']),
        ]);

        if (($kpi['avg_resolution_hours'] ?? null) !== null) {
            $insights[] = strtr(translate('warranty_insight_avg_resolution_time'), [
                ':hours' => number_format((float)$kpi['avg_resolution_hours'], 1),
            ]);
        }

        $peakClaims = max($claimTrend);
        if ($peakClaims > 0) {
            $peakIndex = array_search($peakClaims, $claimTrend, true);
            if ($peakIndex !== false && isset($trendLabels[$peakIndex])) {
                $resolved = (int)($resolvedTrend[$peakIndex] ?? 0);
                $insights[] = strtr(translate('warranty_insight_peak_claim_month'), [
                    ':period' => $trendLabels[$peakIndex],
                    ':claims' => (string)$peakClaims,
                    ':resolved' => (string)$resolved,
                ]);
            }
        }

        if (!empty($statusRows)) {
            $topStatus = $statusRows[0];
            $statusName = ucwords(str_replace('_', ' ', (string)data_get($topStatus, 'status_name', 'new')));
            $statusCount = (int)data_get($topStatus, 'total', 0);
            $insights[] = strtr(translate('warranty_insight_top_claim_status'), [
                ':status_name' => $statusName,
                ':claims' => (string)$statusCount,
            ]);
        }

        return $insights;
    }
}
