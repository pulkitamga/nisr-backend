<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Warranty;
use App\Models\Blacklist;
use App\Jobs\SendActivationOTPJob;
use App\Models\ActivationReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Brian2694\Toastr\Facades\Toastr;
use App\Utils\Helpers;

class WarrantyActivationController extends Controller
{
    public function activate(Request $request)
    {
        $key = 'warranty:activate:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            Toastr::error(translate('Too many attempts.'));
            return back();
        }

        $validator = Validator::make($request->all(), [
            'serial_number' => 'required|string|max:255',
            'purchase_date' => 'required|date|before:today',
            'retailer_name' => 'required|string|max:255',
            'retailer_branch_id' => 'nullable|integer',
            'invoice_number' => 'nullable|string|max:100',
            'receipt' => 'nullable|file|mimes:jpg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            $errors = Helpers::validationErrorProcessor($validator);
            foreach ($errors as $error) {
                Toastr::error(translate($error['message']));
            }
            return back();
        }

        $warranty = Warranty::where('serial_number', $request->serial_number)
            ->whereIn('status', ['preactivated', 'cancelled'])
            ->with('product')
            ->firstOrFail();

        if (Warranty::where('serial_number', $request->serial_number)->active()->exists()) {
            Toastr::error(translate('Already has an active warranty.'));
            return back();
        }

        if (Blacklist::where('serial_number', $request->serial_number)->exists()) {
            Toastr::error(translate('Serial is blacklisted.'));
            return back();
        }

        if ($request->purchase_date->gt(now()->subYears(5)) || $request->purchase_date->addYears(1)->lt(now())) {
            Toastr::error(translate('Invalid purchase date.'));
            return back();
        }

        $isGuest = !$request->user('customer');  // Your customer guard
        $requireOtp = getWebConfig(name: 'warranty_require_otp')['status'] ?? 0;
        $isFlagged = !$this->isKnownRetailer($request->retailer_name, $request->retailer_branch_id);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('warranty/receipts', 'public');
        }

        if ($requireOtp) {
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $contact = $isGuest ? ($request->phone ?? $request->email) : ($request->user('customer')->phone ?? $request->user('customer')->email);
            SendActivationOTPJob::dispatch($otp, $contact, $warranty->id);
            session(['otp_warranty_id' => $warranty->id, 'pending_data' => $request->all()]);
            RateLimiter::hit($key, 3600);
            Toastr::success(translate('OTP sent. Please verify.'));
            return back();
        }

        $this->commitActivation($warranty, $request, $isGuest, $receiptPath);
        event(new \App\Events\WarrantyActivatedEvent($warranty));

        if ($isFlagged && !(getWebConfig(name: 'warranty_auto_approve_off_platform')['status'] ?? 1)) {
            ActivationReview::create([
                'warranty_id' => $warranty->id,
                'status' => 'pending',
                'review_notes' => "Off-platform: {$request->retailer_name}",
            ]);
            // Notify agent via event
            event(new \App\Events\ActivationReviewNeededEvent($warranty));
        }

        Toastr::success(translate('Warranty activated!'));
        return back()->with('warranty_card', $this->generateWarrantyCard($warranty));
    }

    // ... verifyOtp, commitActivation, isKnownRetailer, generateWarrantyCard similar to before, but use Toastr and customer guard ...
    // e.g., in commitActivation: use $request->user('customer')->id for final_user_id

    private function isKnownRetailer($name, $branchId)
    {
        return \App\Models\Branch::where('name', 'like', "%{$name}%")->orWhere('id', $branchId)->exists();
    }
}