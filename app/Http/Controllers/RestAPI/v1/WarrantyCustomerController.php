<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\Blacklist;
use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Policy;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use App\Models\WarrantyClaimPayment;
use App\Models\WarrantyTimelineEvent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WarrantyCustomerController extends Controller
{
    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
    ) {}

    public function warranties(Request $request): JsonResponse
    {
        $customer = $request->user();
        $warranties = $this->customerWarrantyQuery($customer->id)
            ->latest('activation_date')
            ->get();

        return response()->json([
            'success' => true,
            'warranties' => $warranties
                ->map(fn(Warranty $warranty) => $this->formatWarrantyListItem($warranty))
                ->values(),
        ]);
    }

    public function warrantyDetail(Request $request, string $warrantyPublicId): JsonResponse
    {
        $customer = $request->user();
        $warranty = $this->customerWarrantyQuery($customer->id)
            ->where('warranty_public_id', $warrantyPublicId)
            ->first();

        if (!$warranty) {
            return response()->json([
                'success' => false,
                'message' => 'Warranty not found',
            ], 404);
        }

        return response()->json($this->buildWarrantyViewPayload($warranty, false));
    }

    public function claims(Request $request): JsonResponse
    {
        $customer = $request->user();
        $claims = $this->customerClaimQuery($customer->id)
            ->latest('updated_at')
            ->get();

        return response()->json([
            'success' => true,
            'claims' => $claims
                ->map(fn(WarrantyClaim $claim) => $this->formatClaimListItem($claim))
                ->values(),
        ]);
    }

    public function claimDetail(Request $request, string $claimNumber): JsonResponse
    {
        $customer = $request->user();
        $claim = $this->customerClaimQuery($customer->id)
            ->where('claim_number', $claimNumber)
            ->first();

        if (!$claim) {
            return response()->json([
                'success' => false,
                'message' => 'Warranty claim not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'claim' => $this->formatClaimDetail($claim),
        ]);
    }

    public function claimPaymentRequest(Request $request, string $claimNumber): JsonResponse
    {
        $customer = $request->user();
        $claim = $this->customerClaimQuery($customer->id)
            ->where('claim_number', $claimNumber)
            ->first();

        if (!$claim) {
            return response()->json([
                'success' => false,
                'message' => 'Warranty claim not found',
            ], 404);
        }

        $payment = $this->resolveActiveClaimPayment($claim);
        if (!$payment || empty($payment->payment_link)) {
            return response()->json([
                'success' => false,
                'message' => 'No active payment link is available for this claim',
            ], 422);
        }

        if ($payment->payment_link_expires_at && $payment->payment_link_expires_at->isPast()) {
            $payment->update(['payment_status' => 'expired']);

            return response()->json([
                'success' => false,
                'message' => 'Payment link has expired',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'payment' => $this->formatPaymentSummary($payment),
        ]);
    }

    public function orderWarrantySupport(Request $request, int $orderId): JsonResponse
    {
        $customer = $request->user();
        $order = Order::query()
            ->with(['details.product', 'details.productAllStatus'])
            ->where('id', $orderId)
            ->where('customer_id', $customer->id)
            ->where('is_guest', 0)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $deliveredDays = Carbon::parse($order->updated_at)->diffInDays(now());
        $warrantyActivationDays = (int)(getWebConfig('warranty_activation_days') ?? 7);
        $orderDetailWarrantyMap = $this->buildOrderDetailWarrantyMap($order, $customer->id);

        $items = $order->details->map(function (OrderDetail $detail) use (
            $order,
            $orderDetailWarrantyMap,
            $deliveredDays,
            $warrantyActivationDays
        ) {
            $warrantyData = $orderDetailWarrantyMap[$detail->id] ?? [
                'items' => collect(),
                'first' => null,
                'activated_count' => 0,
                'remaining_count' => (int)$detail->qty,
            ];

            $isDeliveredItem = $order->order_status === 'delivered'
                && $detail->delivery_status === 'delivered';
            $isTraceable = (bool)($detail->product?->is_traceable);
            $withinActivationWindow = $deliveredDays <= $warrantyActivationDays;
            $firstWarranty = $warrantyData['first'];
            $remainingCount = (int)($warrantyData['remaining_count'] ?? 0);
            $canActivate = $isDeliveredItem
                && $isTraceable
                && $withinActivationWindow
                && $remainingCount > 0;

            return [
                'order_detail_id' => $detail->id,
                'order_id' => $detail->order_id,
                'product_id' => $detail->product_id,
                'product_name' => $detail->product?->name
                    ?? $detail->productAllStatus?->name
                    ?? 'Product',
                'quantity' => (int)$detail->qty,
                'is_traceable' => $isTraceable,
                'warranty_status' => $firstWarranty?->statusLabel() ?? 'not_activated',
                'warranty_public_id' => $firstWarranty?->warranty_public_id,
                'serial_number' => $firstWarranty?->serial_number,
                'activated_count' => (int)($warrantyData['activated_count'] ?? 0),
                'remaining_count' => $remainingCount,
                'warranty_activation_window_open' => $withinActivationWindow,
                'activation_window_days' => $warrantyActivationDays,
                'delivered_days' => $deliveredDays,
                'can_activate' => $canActivate,
                'can_view_warranty' => $firstWarranty?->warranty_public_id !== null,
                'warranty_support_message' => $this->buildWarrantySupportMessage(
                    isTraceable: $isTraceable,
                    isDeliveredItem: $isDeliveredItem,
                    withinActivationWindow: $withinActivationWindow,
                    remainingCount: $remainingCount
                ),
                'existing_warranties' => collect($warrantyData['items'] ?? [])
                    ->map(fn(Warranty $warranty) => [
                        'warranty_public_id' => $warranty->warranty_public_id,
                        'serial_number' => $warranty->serial_number,
                        'status' => $warranty->statusLabel(),
                    ])->values(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'status' => $order->order_status,
                'delivered_days' => $deliveredDays,
                'activation_window_days' => $warrantyActivationDays,
            ],
            'items' => $items,
        ]);
    }

    public function activateOrderWarranty(Request $request, int $orderDetailId): JsonResponse
    {
        $customer = $request->user();
        $serialNumbersInput = $request->input('serial_numbers', $request->input('serial_no', []));
        if (!is_array($serialNumbersInput)) {
            $serialNumbersInput = [$serialNumbersInput];
        }

        $request->merge(['serial_numbers' => $serialNumbersInput]);

        $validator = Validator::make($request->all(), [
            'serial_numbers' => 'required|array',
            'serial_numbers.*' => 'nullable|string|max:255',
            'agree_terms' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $detail = OrderDetail::query()
            ->with(['product', 'order'])
            ->find($orderDetailId);

        if (!$detail || !$detail->order || (int)$detail->order->customer_id !== (int)$customer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Order item not found',
            ], 404);
        }

        $deliveredDays = Carbon::parse($detail->order->updated_at)->diffInDays(now());
        $warrantyActivationDays = (int)(getWebConfig('warranty_activation_days') ?? 7);
        $isDeliveredItem = $detail->order->order_status === 'delivered'
            && $detail->delivery_status === 'delivered';
        $isTraceable = (bool)($detail->product?->is_traceable);
        $withinActivationWindow = $deliveredDays <= $warrantyActivationDays;

        if (!$isDeliveredItem || !$isTraceable || !$withinActivationWindow) {
            return response()->json([
                'success' => false,
                'message' => 'This order item is not eligible for warranty activation',
            ], 422);
        }

        $activatedCount = Warranty::query()
            ->where('invoice_number', $detail->order_id)
            ->where('product_id', $detail->product_id)
            ->where('final_user_id', $customer->id)
            ->where('activation_method', 'order_activation')
            ->whereNotNull('activation_date')
            ->count();

        $remainingQty = max(0, (int)$detail->qty - $activatedCount);
        if ($remainingQty <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'All warranty units for this item are already activated',
            ], 422);
        }

        $serialNumbers = collect($request->input('serial_numbers', []))
            ->map(fn($serial) => trim((string)$serial))
            ->filter(fn($serial) => $serial !== '')
            ->unique()
            ->values();

        if ($serialNumbers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter at least one serial number',
            ], 422);
        }

        if ($serialNumbers->count() > $remainingQty) {
            return response()->json([
                'success' => false,
                'message' => 'You can only activate the remaining quantity for this item',
            ], 422);
        }

        $defaultDuration = (int)($this->businessSettingRepo->getFirstWhere(['type' => 'warranty_months'])['value'] ?? 12);
        $start = $detail->updated_at ?? now();
        $end = Carbon::parse($start)->copy()->addMonths($defaultDuration);

        $activatedSerials = [];
        $failedSerials = [];
        $activatedWarrantyIds = [];

        foreach ($serialNumbers as $serialNumber) {
            $warranty = Warranty::query()
                ->where('serial_number', $serialNumber)
                ->whereIn('status', ['preactivated', 'cancelled'])
                ->first();

            if (!$warranty || (int)$warranty->product_id !== (int)$detail->product_id) {
                $failedSerials[] = $serialNumber;
                continue;
            }

            if (
                Warranty::query()
                    ->where('serial_number', $serialNumber)
                    ->where('status', 'active')
                    ->where('end_date', '>', now())
                    ->exists()
            ) {
                $failedSerials[] = $serialNumber;
                continue;
            }

            if (Blacklist::query()->where('serial_number', $serialNumber)->exists()) {
                $failedSerials[] = $serialNumber;
                continue;
            }

            $warranty->update([
                'status' => 'active',
                'activation_date' => now(),
                'start_date' => $start,
                'end_date' => $end,
                'purchase_date' => $detail->updated_at,
                'invoice_number' => $detail->order_id,
                'final_user_id' => $customer->id,
                'activation_method' => 'order_activation',
                'consent_checked' => true,
                'consent_timestamp' => now(),
                'consent_ip' => $request->ip(),
                'policy_version' => $this->resolvePublishedPolicyVersion(),
            ]);

            WarrantyTimelineEvent::create([
                'warranty_id' => $warranty->id,
                'event_type' => 'activated',
                'description' => 'Activated via mobile order support',
                'timestamp' => now(),
                'user_id' => null,
            ]);

            $activatedSerials[] = $serialNumber;
            $activatedWarrantyIds[] = $warranty->id;
        }

        if (!empty($activatedSerials)) {
            $detail->warranty_status = ($activatedCount + count($activatedSerials)) >= (int)$detail->qty ? 1 : 0;
            $detail->save();
        }

        $supportPayload = $this->orderWarrantySupport($request, (int)$detail->order_id)->getData(true);
        $latestWarranty = Warranty::query()
            ->whereIn('id', $activatedWarrantyIds)
            ->latest('activation_date')
            ->first();

        return response()->json([
            'success' => !empty($activatedSerials),
            'status' => !empty($activatedSerials) && !empty($failedSerials)
                ? 'partial_success'
                : (!empty($activatedSerials) ? 'success' : 'error'),
            'message' => !empty($activatedSerials)
                ? 'Warranty activated successfully'
                : 'No serial number could be activated',
            'activated_serials' => $activatedSerials,
            'failed_serials' => $failedSerials,
            'warranty_public_id' => $latestWarranty?->warranty_public_id,
            'support' => [
                'order' => $supportPayload['order'] ?? null,
                'items' => $supportPayload['items'] ?? [],
            ],
        ], !empty($activatedSerials) ? 200 : 422);
    }

    private function customerWarrantyQuery(int $customerId)
    {
        return Warranty::query()
            ->with([
                'product:id,name,sku',
                'user:id,f_name,l_name,email,phone',
                'claims' => fn($query) => $query
                    ->latest('updated_at')
                    ->with([
                        'attachments',
                        'payments',
                        'charges',
                        'timelineEvents' => fn($timelineQuery) => $timelineQuery->latest('timestamp'),
                    ]),
                'timelineEvents' => fn($query) => $query->latest('timestamp'),
            ])
            ->where('final_user_id', $customerId)
            ->whereNotNull('activation_date');
    }

    private function customerClaimQuery(int $customerId)
    {
        return WarrantyClaim::query()
            ->with([
                'attachments',
                'payments',
                'charges',
                'timelineEvents' => fn($query) => $query->latest('timestamp'),
                'warranty.product:id,name,sku',
                'warranty.user:id,f_name,l_name,email,phone',
            ])
            ->whereHas('warranty', fn($query) => $query->where('final_user_id', $customerId));
    }

    private function buildWarrantyViewPayload(Warranty $warranty, bool $maskOwner): array
    {
        $openClaim = $warranty->claims->first(
            fn(WarrantyClaim $claim) => !in_array($claim->status, ['closed', 'rejected'], true)
        );
        $payment = $openClaim ? $this->resolveActiveClaimPayment($openClaim) : null;

        return [
            'success' => true,
            'warranty' => $this->formatWarrantyData($warranty, $maskOwner),
            'timeline_events' => $this->formatTimelineEvents($warranty->timelineEvents),
            'open_claim' => $openClaim ? $this->formatClaimSummary($openClaim) : null,
            'payment' => $openClaim ? $this->formatPaymentSummary($payment) : null,
            'available_actions' => $this->formatAvailableActions($warranty, $openClaim, $payment),
        ];
    }

    private function formatWarrantyListItem(Warranty $warranty): array
    {
        $openClaim = $warranty->claims->first(
            fn(WarrantyClaim $claim) => !in_array($claim->status, ['closed', 'rejected'], true)
        );
        $payment = $openClaim ? $this->resolveActiveClaimPayment($openClaim) : null;

        return [
            ...$this->formatWarrantyData($warranty, false),
            'open_claim' => $openClaim ? $this->formatClaimSummary($openClaim) : null,
            'payment' => $openClaim ? $this->formatPaymentSummary($payment) : null,
            'available_actions' => $this->formatAvailableActions($warranty, $openClaim, $payment),
        ];
    }

    private function formatWarrantyData(Warranty $warranty, bool $maskOwner): array
    {
        $customerName = trim((string)($warranty->user?->f_name . ' ' . $warranty->user?->l_name));
        if ($customerName === '') {
            $customerName = (string)($warranty->activated_by_name ?? '');
        }

        $email = (string)($warranty->user?->email ?? $warranty->activated_by_email ?? '');
        $phone = (string)($warranty->user?->phone ?? $warranty->activated_by_phone ?? '');

        return [
            'warranty_public_id' => $warranty->warranty_public_id,
            'serial_number' => $warranty->serial_number,
            'status' => $warranty->statusLabel(),
            'activation_status' => $warranty->status,
            'activation_date' => optional($warranty->activation_date)?->toIso8601String(),
            'start_date' => optional($warranty->start_date)?->toIso8601String(),
            'end_date' => optional($warranty->end_date)?->toIso8601String(),
            'purchase_date' => optional($warranty->purchase_date)?->toDateString(),
            'invoice_number' => $warranty->invoice_number,
            'policy_version' => $warranty->policy_version,
            'remaining_days' => $warranty->remaining_days,
            'product_name' => $warranty->product?->name,
            'customer_name' => $maskOwner ? $this->maskName($customerName) : $customerName,
            'activated_by_name' => $maskOwner ? $this->maskName($customerName) : $customerName,
            'email' => $maskOwner ? $this->maskEmail($email) : $email,
            'activated_by_email' => $maskOwner ? $this->maskEmail($email) : $email,
            'phone' => $maskOwner ? $this->maskPhone($phone) : $phone,
            'activated_by_phone' => $maskOwner ? $this->maskPhone($phone) : $phone,
            'product' => [
                'id' => $warranty->product?->id,
                'name' => $warranty->product?->name,
                'sku' => $warranty->product?->sku,
            ],
        ];
    }

    private function formatClaimListItem(WarrantyClaim $claim): array
    {
        $payment = $this->resolveActiveClaimPayment($claim);
        $warranty = $claim->warranty;

        return [
            'claim_number' => $claim->claim_number,
            'status' => $claim->status,
            'grouped_status' => $this->groupClaimStatus($claim->status),
            'customer_meaning' => $this->claimStatusMeaning($claim->status),
            'submitted_at' => optional($claim->submitted_at)?->toIso8601String(),
            'updated_at' => optional($claim->updated_at)?->toIso8601String(),
            'serial_number' => $claim->serial_number,
            'product_name' => $warranty?->product?->name,
            'warranty_public_id' => $warranty?->warranty_public_id,
            'warranty_status' => $warranty?->statusLabel(),
            'needs_action' => in_array($claim->status, ['waiting_customer', 'waiting_payment'], true),
            'payment' => $this->formatPaymentSummary($payment),
        ];
    }

    private function formatClaimDetail(WarrantyClaim $claim): array
    {
        $payment = $this->resolveActiveClaimPayment($claim);
        $parsedDescription = $this->parseClaimDescription((string)$claim->description);

        return [
            'claim_number' => $claim->claim_number,
            'status' => $claim->status,
            'grouped_status' => $this->groupClaimStatus($claim->status),
            'customer_meaning' => $this->claimStatusMeaning($claim->status),
            'subject' => $parsedDescription['subject'],
            'details' => $parsedDescription['details'],
            'issue' => $parsedDescription['issue'],
            'submitted_at' => optional($claim->submitted_at)?->toIso8601String(),
            'updated_at' => optional($claim->updated_at)?->toIso8601String(),
            'serial_number' => $claim->serial_number,
            'attachments' => collect($claim->attachments_full_url ?? [])
                ->values()
                ->map(fn($url, $index) => ['id' => $index + 1, 'url' => $url]),
            'timeline_events' => $this->formatTimelineEvents($claim->timelineEvents),
            'payment' => $this->formatPaymentSummary($payment),
            'warranty' => $claim->warranty ? $this->formatWarrantyData($claim->warranty, false) : null,
            'available_actions' => [
                'can_pay' => $payment !== null && !empty($payment->payment_link),
                'can_view_warranty' => $claim->warranty?->warranty_public_id !== null,
            ],
        ];
    }

    private function formatClaimSummary(WarrantyClaim $claim): array
    {
        $latestEventAt = $claim->timelineEvents->first()?->timestamp
            ?? $claim->updated_at
            ?? $claim->submitted_at;

        return [
            'claim_number' => $claim->claim_number,
            'status' => $claim->status,
            'grouped_status' => $this->groupClaimStatus($claim->status),
            'latest_event_at' => optional($latestEventAt)?->toIso8601String(),
            'updated_at' => optional($claim->updated_at)?->toIso8601String(),
        ];
    }

    private function formatPaymentSummary(?WarrantyClaimPayment $payment): ?array
    {
        if (!$payment) {
            return null;
        }

        return [
            'required' => $payment->payment_status === 'pending',
            'payment_id' => $payment->id,
            'status' => $payment->payment_status,
            'amount' => (float)$payment->amount,
            'amount_label' => $this->formatMoneyLabel((float)$payment->amount),
            'redirect_link' => $payment->payment_link,
            'expires_at' => optional($payment->payment_link_expires_at)?->toIso8601String(),
        ];
    }

    private function formatAvailableActions(
        Warranty $warranty,
        ?WarrantyClaim $openClaim,
        ?WarrantyClaimPayment $payment,
    ): array {
        $warrantyStatus = $warranty->statusLabel();

        return [
            'can_claim' => $warrantyStatus === 'active' && $openClaim === null,
            'can_pay' => $payment !== null && !empty($payment->payment_link),
            'can_view_claim' => $openClaim !== null,
        ];
    }

    private function formatTimelineEvents(Collection $events): array
    {
        return $events
            ->take(20)
            ->map(fn(WarrantyTimelineEvent $event) => [
                'event_type' => $event->event_type,
                'description' => $event->description,
                'timestamp' => optional($event->timestamp ?? $event->created_at)?->toIso8601String(),
                'created_at' => optional($event->created_at)?->toIso8601String(),
            ])
            ->values()
            ->all();
    }

    private function resolveActiveClaimPayment(WarrantyClaim $claim): ?WarrantyClaimPayment
    {
        return $claim->payments
            ->sortByDesc('id')
            ->first(function (WarrantyClaimPayment $payment) {
                if ($payment->payment_status !== 'pending') {
                    return false;
                }

                if ($payment->payment_link_expires_at && $payment->payment_link_expires_at->isPast()) {
                    return false;
                }

                return true;
            });
    }

    private function parseClaimDescription(string $description): array
    {
        $parsed = ['subject' => '', 'details' => '', 'issue' => ''];
        foreach (preg_split("/(\r\n|\n|\r)/", $description) ?: [] as $line) {
            if (str_starts_with($line, 'Subject: ')) {
                $parsed['subject'] = trim(substr($line, 9));
            } elseif (str_starts_with($line, 'Details: ')) {
                $parsed['details'] = trim(substr($line, 9));
            } elseif (str_starts_with($line, 'Issue: ')) {
                $parsed['issue'] = trim(substr($line, 7));
            }
        }

        if ($parsed['details'] === '' && $description !== '') {
            $parsed['details'] = $description;
        }

        return $parsed;
    }

    private function groupClaimStatus(string $status): string
    {
        return match ($status) {
            'new', 'triage_pending', 'approved', 'rma_issued' => 'Submitted',
            'received', 'diagnosis_pending', 'repair_pending', 'replacement_pending', 'qc_pending' => 'In Service',
            'waiting_customer', 'waiting_parts', 'waiting_payment' => 'Waiting',
            'shipped_ready', 'dispatched', 'resolved' => 'Ready/Delivered',
            'closed', 'rejected' => 'Ended',
            default => 'Submitted',
        };
    }

    private function claimStatusMeaning(string $status): string
    {
        return match ($status) {
            'new' => 'Claim received',
            'triage_pending' => 'Under first review',
            'approved' => 'Approved for next action',
            'rma_issued' => 'Return instructions issued',
            'received' => 'Item received by service team',
            'diagnosis_pending' => 'Diagnosis in progress',
            'repair_pending' => 'Repair work pending',
            'replacement_pending' => 'Replacement decision in progress',
            'qc_pending' => 'Quality check pending',
            'waiting_customer' => 'Waiting on customer response',
            'waiting_parts' => 'Waiting on stock or parts',
            'waiting_payment' => 'Customer payment required',
            'shipped_ready' => 'Ready for dispatch',
            'dispatched' => 'On the way back to customer',
            'resolved' => 'Work completed',
            'closed' => 'Claim finished',
            'rejected' => 'Claim rejected',
            default => 'Claim status updated',
        };
    }

    private function buildOrderDetailWarrantyMap(Order $order, int $customerId): array
    {
        $productIds = $order->details->pluck('product_id')->filter()->unique()->values()->toArray();
        $warrantiesByProduct = [];

        if (!empty($productIds)) {
            $warrantiesByProduct = Warranty::query()
                ->where('final_user_id', $customerId)
                ->where('invoice_number', $order->id)
                ->whereIn('product_id', $productIds)
                ->where('activation_method', 'order_activation')
                ->whereNotNull('activation_date')
                ->orderBy('activation_date')
                ->get()
                ->groupBy('product_id');
        }

        $consumedWarrantyCountByProduct = [];
        $orderDetailWarrantyMap = [];

        foreach ($order->details as $detail) {
            $productId = (int)$detail->product_id;
            $detailQty = max(0, (int)$detail->qty);
            $productWarranties = collect($warrantiesByProduct[$productId] ?? [])->values();
            $offset = $consumedWarrantyCountByProduct[$productId] ?? 0;
            $detailWarranties = $productWarranties->slice($offset, $detailQty)->values();
            $consumedWarrantyCountByProduct[$productId] = $offset + $detailWarranties->count();

            $activatedCount = $detailWarranties->count();
            $remainingCount = max(0, $detailQty - $activatedCount);

            $orderDetailWarrantyMap[$detail->id] = [
                'items' => $detailWarranties,
                'first' => $detailWarranties->first(),
                'activated_count' => $activatedCount,
                'remaining_count' => $remainingCount,
            ];
        }

        return $orderDetailWarrantyMap;
    }

    private function buildWarrantySupportMessage(
        bool $isTraceable,
        bool $isDeliveredItem,
        bool $withinActivationWindow,
        int $remainingCount,
    ): string {
        if (!$isTraceable) {
            return 'This item does not support serial-based warranty activation';
        }
        if (!$isDeliveredItem) {
            return 'Warranty activation becomes available after delivery';
        }
        if (!$withinActivationWindow) {
            return 'The activation window has closed for this item';
        }
        if ($remainingCount <= 0) {
            return 'All warranty units for this item are already activated';
        }

        return 'Activation window is open for this delivered item';
    }

    private function resolvePublishedPolicyVersion(): ?string
    {
        return Policy::query()
            ->published()
            ->orderByDesc('effective_date')
            ->orderByDesc('published_at')
            ->value('version');
    }

    private function maskEmail(string $email): string
    {
        if ($email === '' || !str_contains($email, '@')) {
            return $email;
        }

        [$localPart, $domain] = explode('@', $email, 2);
        if (strlen($localPart) <= 2) {
            return substr($localPart, 0, 1) . '***@' . $domain;
        }

        return substr($localPart, 0, 2) . '***@' . $domain;
    }

    private function maskPhone(string $phone): string
    {
        $digitsOnly = preg_replace('/\D+/', '', $phone ?? '');
        if (!$digitsOnly || strlen($digitsOnly) <= 4) {
            return '****';
        }

        return str_repeat('*', strlen($digitsOnly) - 4) . substr($digitsOnly, -4);
    }

    private function maskName(string $name): string
    {
        $trimmed = trim($name);
        if ($trimmed === '') {
            return '';
        }

        return collect(preg_split('/\s+/', $trimmed) ?: [])
            ->map(function (string $part) {
                if (strlen($part) <= 1) {
                    return $part . '*';
                }

                return substr($part, 0, 1) . str_repeat('*', max(strlen($part) - 1, 1));
            })
            ->implode(' ');
    }

    private function formatMoneyLabel(float $amount): string
    {
        $currencyModel = getWebConfig(name: 'currency_model');
        if ($currencyModel === 'multi_currency') {
            return number_format($amount, 2) . ' USD';
        }

        $defaultCurrencyId = getWebConfig(name: 'system_default_currency');
        $currencyCode = Currency::query()->find($defaultCurrencyId)?->code ?? 'EGP';

        return number_format($amount, 2) . ' ' . $currencyCode;
    }
}
