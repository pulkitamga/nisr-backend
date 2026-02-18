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

    // Manual Activate View
    public function manualActivateView()
    {
        return view('admin-views.warranty.manual-activate');
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

        return view('admin-views.warranty.report-activations', compact('rate', 'methodCounts'));
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
            'description' => 'Activation approved by admin',
            'timestamp' => now(),
            'user_id' => auth('admin')->id(),
        ]);

        Toastr::success('Activation approved successfully.');
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
            'description' => 'Activation rejected: ' . $request->review_notes,
            'timestamp' => now(),
            'user_id' => auth('admin')->id(),
        ]);

        Toastr::success('Activation rejected.');
        return back();
    }
}
