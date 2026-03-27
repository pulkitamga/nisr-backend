<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use App\Models\WarrantyClaimPayment;
use App\Models\WarrantyTimelineEvent;
use App\Services\WarrantyOrderActivationService;
use App\Services\WarrantyPolicyVersionResolver;
use App\Support\WarrantyOrderSupport;
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
                'message' => translate('warranty_not_found'),
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
                'message' => translate('warranty_claim_not_found'),
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
                'message' => translate('warranty_claim_not_found'),
            ], 404);
        }

        $payment = $this->resolveActiveClaimPayment($claim);
        if (!$payment || empty($payment->payment_link)) {
            return response()->json([
                'success' => false,
                'message' => translate('warranty_claim_payment_link_unavailable'),
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
        $orderDetailWarrantyMap = $this->buildOrderDetailWarrantyMap($order, $customer->id);

        $items = $order->details->map(function (OrderDetail $detail) use (
            $order,
            $orderDetailWarrantyMap,
            $deliveredDays
        ) {
            $warrantyData = $orderDetailWarrantyMap[$detail->id] ?? [
                'items' => collect(),
                'first' => null,
                'activated_count' => 0,
                'remaining_count' => (int)$detail->qty,
            ];

            $isDeliveredItem = WarrantyOrderSupport::isDeliveredItem($order, $detail);
            $isWarrantyEnabled = (bool)($detail->product?->is_warranty);
            $firstWarranty = $warrantyData['first'];
            $remainingCount = (int)($warrantyData['remaining_count'] ?? 0);
            $canActivate = WarrantyOrderSupport::canActivate(
                $isWarrantyEnabled,
                $isDeliveredItem,
                $remainingCount
            );

            return [
                'order_detail_id' => $detail->id,
                'order_id' => $detail->order_id,
                'product_id' => $detail->product_id,
                'product_name' => $detail->product?->name
                    ?? $detail->productAllStatus?->name
                    ?? 'Product',
                'quantity' => (int)$detail->qty,
                'is_warranty' => $isWarrantyEnabled,
                'warranty_status' => $firstWarranty?->statusLabel() ?? 'not_activated',
                'warranty_public_id' => $firstWarranty?->warranty_public_id,
                'serial_number' => $firstWarranty?->serial_number,
                'activated_count' => (int)($warrantyData['activated_count'] ?? 0),
                'remaining_count' => $remainingCount,
                'warranty_activation_window_open' => $isDeliveredItem,
                'activation_window_days' => null,
                'delivered_days' => $deliveredDays,
                'can_activate' => $canActivate,
                'can_view_warranty' => $firstWarranty?->warranty_public_id !== null,
                'warranty_support_message' => $this->buildWarrantySupportMessage(
                    isWarrantyEnabled: $isWarrantyEnabled,
                    isDeliveredItem: $isDeliveredItem,
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
                'activation_window_days' => null,
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

        $isDeliveredItem = WarrantyOrderSupport::isDeliveredItem($detail->order, $detail);
        $isWarrantyEnabled = (bool)($detail->product?->is_warranty);

        if (!$isDeliveredItem || !$isWarrantyEnabled) {
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

        $activationResult = (new WarrantyOrderActivationService($this->businessSettingRepo))->activate(
            $detail,
            $serialNumbers,
            (int) $customer->id,
            (string) $request->ip(),
            [
                'activated_count' => $activatedCount,
                'timeline_description' => 'Activated via mobile order support',
                'policy_version' => $this->resolvePublishedPolicyVersion(),
                'user_id' => null,
            ]
        );

        $activatedSerials = $activationResult['activated_serials'];
        $failedSerials = $activationResult['failed_serials'];
        $activatedWarrantyIds = $activationResult['activated_warranty_ids'];

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
                'product:id,name,code',
                'user:id,f_name,l_name,email,phone',
                'claims' => fn($query) => $query
                    ->latest('updated_at')
                    ->with([
                        'attachments',
                        'payments',
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
                'timelineEvents' => fn($query) => $query->latest('timestamp'),
                'warranty.product:id,name,code',
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
        $warrantyStatusKey = $warranty->statusLabel();

        return [
            'warranty_public_id' => $warranty->warranty_public_id,
            'serial_number' => $warranty->serial_number,
            'status_key' => $warrantyStatusKey,
            'status' => $warrantyStatusKey,
            'status_label' => $this->translateWarrantyStatus($warrantyStatusKey),
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
                'code' => $warranty->product?->code,
            ],
        ];
    }

    private function formatClaimListItem(WarrantyClaim $claim): array
    {
        $payment = $this->resolveActiveClaimPayment($claim);
        $warranty = $claim->warranty;
        $groupedStatusKey = $this->groupClaimStatusKey($claim->status);

        return [
            'claim_number' => $claim->claim_number,
            'status_key' => $claim->status,
            'status' => $this->claimStatusLabel($claim->status),
            'grouped_status_key' => $groupedStatusKey,
            'grouped_status' => $this->groupClaimStatus($claim->status),
            'customer_meaning' => $this->claimStatusMeaning($claim->status),
            'submitted_at' => optional($claim->submitted_at)?->toIso8601String(),
            'updated_at' => optional($claim->updated_at)?->toIso8601String(),
            'serial_number' => $claim->serial_number,
            'product_name' => $warranty?->product?->name,
            'warranty_public_id' => $warranty?->warranty_public_id,
            'warranty_status_key' => $warranty?->statusLabel(),
            'warranty_status' => $warranty ? $this->translateWarrantyStatus($warranty->statusLabel()) : null,
            'needs_action' => in_array($claim->status, ['waiting_customer', 'waiting_payment'], true),
            'payment' => $this->formatPaymentSummary($payment),
        ];
    }

    private function formatClaimDetail(WarrantyClaim $claim): array
    {
        $payment = $this->resolveActiveClaimPayment($claim);
        $parsedDescription = $this->parseClaimDescription((string)$claim->description);
        $groupedStatusKey = $this->groupClaimStatusKey($claim->status);

        return [
            'claim_number' => $claim->claim_number,
            'status_key' => $claim->status,
            'status' => $this->claimStatusLabel($claim->status),
            'grouped_status_key' => $groupedStatusKey,
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
        $groupedStatusKey = $this->groupClaimStatusKey($claim->status);

        return [
            'claim_number' => $claim->claim_number,
            'status_key' => $claim->status,
            'status' => $this->claimStatusLabel($claim->status),
            'grouped_status_key' => $groupedStatusKey,
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
                'description' => $this->translateTimelineDescription($event),
                'description_raw' => $event->description,
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

    private function groupClaimStatusKey(string $status): string
    {
        return match ($status) {
            'new', 'triage_pending', 'approved', 'rma_issued' => 'submitted',
            'received', 'diagnosis_pending', 'repair_pending', 'replacement_pending', 'qc_pending' => 'in_service',
            'waiting_customer', 'waiting_parts', 'waiting_payment' => 'waiting',
            'shipped_ready', 'dispatched', 'resolved' => 'ready_delivered',
            'closed', 'rejected' => 'ended',
            default => 'submitted',
        };
    }

    private function groupClaimStatus(string $status): string
    {
        return translate('warranty_claim_group_' . $this->groupClaimStatusKey($status));
    }

    private function translateWarrantyStatus(string $status): string
    {
        return match ($status) {
            'preactivated' => translate('warranty_status_preactivated'),
            'active' => translate('warranty_status_active'),
            'expired' => translate('warranty_status_expired'),
            'replaced' => translate('warranty_status_replaced'),
            'cancelled' => translate('warranty_status_cancelled'),
            default => $this->humanizeStatus($status),
        };
    }

    private function claimStatusLabel(string $status): string
    {
        return match ($status) {
            'new' => translate('warranty_claim_status_new'),
            'triage_pending' => translate('warranty_claim_status_triage_pending'),
            'approved' => translate('warranty_claim_status_approved'),
            'rma_issued' => translate('warranty_claim_status_rma_issued'),
            'received' => translate('warranty_claim_status_received'),
            'diagnosis_pending' => translate('warranty_claim_status_diagnosis_pending'),
            'repair_pending' => translate('warranty_claim_status_repair_pending'),
            'replacement_pending' => translate('warranty_claim_status_replacement_pending'),
            'qc_pending' => translate('warranty_claim_status_qc_pending'),
            'waiting_customer' => translate('warranty_claim_status_waiting_customer'),
            'waiting_parts' => translate('warranty_claim_status_waiting_parts'),
            'waiting_payment' => translate('warranty_claim_status_waiting_payment'),
            'shipped_ready' => translate('warranty_claim_status_shipped_ready'),
            'dispatched' => translate('warranty_claim_status_dispatched'),
            'resolved' => translate('warranty_claim_status_resolved'),
            'closed' => translate('warranty_claim_status_closed'),
            'rejected' => translate('warranty_claim_status_rejected'),
            default => $this->humanizeStatus($status),
        };
    }

    private function claimStatusMeaning(string $status): string
    {
        return match ($status) {
            'new' => translate('warranty_claim_meaning_new'),
            'triage_pending' => translate('warranty_claim_meaning_triage_pending'),
            'approved' => translate('warranty_claim_meaning_approved'),
            'rma_issued' => translate('warranty_claim_meaning_rma_issued'),
            'received' => translate('warranty_claim_meaning_received'),
            'diagnosis_pending' => translate('warranty_claim_meaning_diagnosis_pending'),
            'repair_pending' => translate('warranty_claim_meaning_repair_pending'),
            'replacement_pending' => translate('warranty_claim_meaning_replacement_pending'),
            'qc_pending' => translate('warranty_claim_meaning_qc_pending'),
            'waiting_customer' => translate('warranty_claim_meaning_waiting_customer'),
            'waiting_parts' => translate('warranty_claim_meaning_waiting_parts'),
            'waiting_payment' => translate('warranty_claim_meaning_waiting_payment'),
            'shipped_ready' => translate('warranty_claim_meaning_shipped_ready'),
            'dispatched' => translate('warranty_claim_meaning_dispatched'),
            'resolved' => translate('warranty_claim_meaning_resolved'),
            'closed' => translate('warranty_claim_meaning_closed'),
            'rejected' => translate('warranty_claim_meaning_rejected'),
            default => translate('warranty_claim_meaning_updated'),
        };
    }

    private function translateTimelineDescription(WarrantyTimelineEvent $event): string
    {
        $description = trim((string) $event->description);

        return match ($event->event_type) {
            'claim_submitted' => $this->translateClaimSubmittedEvent($description),
            'item_received' => $this->translateItemReceivedEvent($description),
            'decision_made' => $this->translateDecisionEvent($description),
            'payment_handled' => $this->translatePaymentHandledEvent($description),
            'diagnosis_complete' => $this->translateDiagnosisEvent($description),
            'repair_complete' => $this->translateRepairCompletedEvent($description),
            'qc_passed' => translate('warranty_timeline_qc_passed'),
            'dispatched' => $this->translateDispatchedEvent($description),
            'rma_issued' => $this->translateRmaIssuedEvent($description),
            'claim_resumed' => $this->translateClaimResumedEvent($description),
            'replacement_committed' => $this->translateReplacementCommittedEvent($description),
            'closed' => $this->translateClosedEvent($description),
            'resolved' => $this->translateResolvedEvent($description),
            default => $description,
        };
    }

    private function translateClaimSubmittedEvent(string $description): string
    {
        if (preg_match('/Serial(?: Number)?:\s*(.+)$/i', $description, $matches)) {
            return translate('warranty_timeline_claim_submitted') . ' | ' .
                translate('serial_number') . ': ' . trim($matches[1]);
        }

        return translate('warranty_timeline_claim_submitted');
    }

    private function translateItemReceivedEvent(string $description): string
    {
        if (preg_match('/Item received \| Serial:\s*(.*?)\s*\| Branch:\s*(.*?)\s*\| Notes:\s*(.*)$/i', $description, $matches)) {
            return translate('warranty_timeline_item_received') . ' | ' .
                translate('serial_number') . ': ' . trim($matches[1]) . ' | ' .
                translate('branch') . ': ' . trim($matches[2]) . ' | ' .
                translate('notes') . ': ' . trim($matches[3]);
        }

        return translate('warranty_timeline_item_received');
    }

    private function translateDecisionEvent(string $description): string
    {
        if (preg_match('/Decision:\s*(.*?)\s*\| Code:\s*(.*?)\s*\| Message:\s*(.*)$/i', $description, $matches)) {
            return translate('warranty_timeline_decision') . ': ' .
                $this->translateDecisionValue(trim($matches[1])) . ' | ' .
                translate('code') . ': ' . trim($matches[2]) . ' | ' .
                translate('message') . ': ' . trim($matches[3]);
        }

        return $description;
    }

    private function translatePaymentHandledEvent(string $description): string
    {
        $segments = array_values(array_filter(array_map('trim', explode('|', $description))));

        return implode(' | ', array_map(fn(string $segment) => $this->translatePaymentSegment($segment), $segments));
    }

    private function translatePaymentSegment(string $segment): string
    {
        if (str_starts_with($segment, 'Payment handling:')) {
            return translate('warranty_timeline_payment_handling') . ': ' .
                $this->translatePaymentAction(trim(substr($segment, strlen('Payment handling:'))));
        }
        if (str_starts_with($segment, 'Notes:')) {
            return translate('notes') . ': ' . trim(substr($segment, strlen('Notes:')));
        }
        if (str_starts_with($segment, 'COD payment collected:')) {
            return translate('warranty_timeline_cod_payment_collected') . ': ' .
                $this->translateChargeList(trim(substr($segment, strlen('COD payment collected:'))));
        }
        if (str_starts_with($segment, 'COD approved:')) {
            return translate('warranty_timeline_cod_approved') . ': ' .
                $this->translateChargeList(trim(substr($segment, strlen('COD approved:'))));
        }
        if (str_starts_with($segment, 'Resumed to')) {
            return translate('warranty_timeline_resumed_to') . ' ' .
                $this->claimStatusLabel(trim(substr($segment, strlen('Resumed to'))));
        }
        if (str_starts_with($segment, 'Online payment received')) {
            return translate('warranty_timeline_online_payment_received');
        }
        if (str_starts_with($segment, 'Amount:')) {
            return translate('amount') . ': ' . trim(substr($segment, strlen('Amount:')));
        }
        if (str_starts_with($segment, 'Payment ID:')) {
            return translate('payment_id') . ': ' . trim(substr($segment, strlen('Payment ID:')));
        }
        if (str_starts_with($segment, 'Gateway TX:')) {
            return translate('warranty_gateway_transaction') . ': ' . trim(substr($segment, strlen('Gateway TX:')));
        }

        return $segment;
    }

    private function translateDiagnosisEvent(string $description): string
    {
        if (preg_match('/Diagnosis:\s*(.*?)\s*\| REJECTED \| Tamper:\s*(Yes|No)$/i', $description, $matches)) {
            return translate('warranty_timeline_diagnosis') . ': ' . trim($matches[1]) . ' | ' .
                translate('warranty_decision_rejected') . ' | ' .
                translate('warranty_tamper') . ': ' . $this->translateYesNo(trim($matches[2]));
        }

        if (preg_match('/Diagnosis:\s*(.*?)\s*\| Action:\s*(.*?)\s*\| Tamper:\s*(Yes|No)(?:\s*\| Charges:\s*(.*))?$/i', $description, $matches)) {
            $translated = translate('warranty_timeline_diagnosis') . ': ' . trim($matches[1]) . ' | ' .
                translate('action') . ': ' . $this->translateClaimAction(trim($matches[2])) . ' | ' .
                translate('warranty_tamper') . ': ' . $this->translateYesNo(trim($matches[3]));

            if (!empty($matches[4])) {
                $translated .= ' | ' . translate('charges') . ': ' . $this->translateChargeList(trim($matches[4]), '=');
            }

            return $translated;
        }

        return $description;
    }

    private function translateRepairCompletedEvent(string $description): string
    {
        if (preg_match('/Repair completed\. Parts:\s*(.*?)\s*\| Notes:\s*(.*)$/i', $description, $matches)) {
            return translate('warranty_timeline_repair_completed') . ' | ' .
                translate('warranty_parts_used') . ': ' . trim($matches[1]) . ' | ' .
                translate('notes') . ': ' . trim($matches[2]);
        }

        return translate('warranty_timeline_repair_completed');
    }

    private function translateDispatchedEvent(string $description): string
    {
        if (preg_match('/Dispatched via\s*(.*?)(?:\s*\| Tracking:\s*(.*))?$/i', $description, $matches)) {
            $translated = translate('warranty_timeline_dispatched_via') . ' ' . trim($matches[1]);
            if (!empty($matches[2])) {
                $translated .= ' | ' . translate('tracking_number') . ': ' . trim($matches[2]);
            }
            return $translated;
        }

        return translate('warranty_timeline_dispatched');
    }

    private function translateRmaIssuedEvent(string $description): string
    {
        if (preg_match('/RMA\s*(.*?)\s*issued\s*\| Branch:\s*(.*?)\s*\| Deadline:\s*(.*?)\s*\| Instructions:\s*(.*)$/i', $description, $matches)) {
            return translate('warranty_timeline_rma_issued') . ': ' . trim($matches[1]) . ' | ' .
                translate('branch') . ': ' . trim($matches[2]) . ' | ' .
                translate('deadline') . ': ' . trim($matches[3]) . ' | ' .
                translate('instructions') . ': ' . trim($matches[4]);
        }

        return translate('warranty_timeline_rma_issued');
    }

    private function translateClaimResumedEvent(string $description): string
    {
        if (preg_match('/Resumed from\s*(.*?)\s*→\s*(.*?)\.\s*Notes:\s*(.*)$/u', $description, $matches)) {
            return translate('warranty_timeline_claim_resumed') . ' | ' .
                translate('from') . ': ' . $this->claimStatusLabel(trim($matches[1])) . ' | ' .
                translate('to') . ': ' . $this->claimStatusLabel(trim($matches[2])) . ' | ' .
                translate('notes') . ': ' . trim($matches[3]);
        }

        return translate('warranty_timeline_claim_resumed');
    }

    private function translateReplacementCommittedEvent(string $description): string
    {
        if (preg_match('/Replacement committed:\s*(.*?)\s*\| Mode:\s*(.*?)\s*\| Warranty:\s*(.*?)\s*\| Notes:\s*(.*)$/i', $description, $matches)) {
            return translate('warranty_timeline_replacement_committed') . ': ' . trim($matches[1]) . ' | ' .
                translate('mode') . ': ' . trim($matches[2]) . ' | ' .
                translate('warranty') . ': ' . trim($matches[3]) . ' | ' .
                translate('notes') . ': ' . trim($matches[4]);
        }

        return translate('warranty_timeline_replacement_committed');
    }

    private function translateClosedEvent(string $description): string
    {
        return str_replace('Claim closed', translate('warranty_timeline_claim_closed'), $description);
    }

    private function translateResolvedEvent(string $description): string
    {
        return str_replace('Claim resolved on delivery/collection.', translate('warranty_timeline_claim_resolved'), $description);
    }

    private function translatePaymentAction(string $action): string
    {
        return match ($action) {
            'remind' => translate('warranty_payment_action_remind'),
            'pos' => translate('warranty_payment_action_pos'),
            'cod' => translate('warranty_payment_action_cod'),
            'online_link' => translate('warranty_payment_action_online_link'),
            'cod_collect' => translate('warranty_payment_action_cod_collect'),
            'waive' => translate('warranty_payment_action_waive'),
            'client_reject_payment' => translate('warranty_payment_action_client_reject'),
            default => $this->humanizeStatus($action),
        };
    }

    private function translateDecisionValue(string $decision): string
    {
        return match ($decision) {
            'approve' => translate('warranty_decision_approved'),
            'reject' => translate('warranty_decision_rejected'),
            'waiting_customer' => translate('warranty_claim_status_waiting_customer'),
            default => $this->humanizeStatus($decision),
        };
    }

    private function translateClaimAction(string $action): string
    {
        return match ($action) {
            'repair' => translate('warranty_action_repair'),
            'replace' => translate('warranty_action_replace'),
            'reject' => translate('warranty_decision_rejected'),
            default => str_contains($action, 'replace')
                ? str_replace('replace', translate('warranty_action_replace'), $action)
                : $this->humanizeStatus($action),
        };
    }

    private function translateChargeList(string $value, string $separator = ':'): string
    {
        $items = array_values(array_filter(array_map('trim', explode(',', $value))));

        return implode(', ', array_map(function (string $item) use ($separator) {
            if (!str_contains($item, $separator)) {
                return $item;
            }

            [$chargeType, $amount] = array_map('trim', explode($separator, $item, 2));
            return $this->translateChargeType($chargeType) . ': ' . $amount;
        }, $items));
    }

    private function translateChargeType(string $chargeType): string
    {
        return match ($chargeType) {
            'repair_fee' => translate('warranty_charge_repair_fee'),
            'replacement_fee' => translate('warranty_charge_replacement_fee'),
            'inspection_fee' => translate('warranty_charge_inspection_fee'),
            default => $this->humanizeStatus($chargeType),
        };
    }

    private function translateYesNo(string $value): string
    {
        return strtolower($value) === 'yes' ? translate('yes') : translate('no');
    }

    private function humanizeStatus(string $value): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $value));
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
        bool $isWarrantyEnabled,
        bool $isDeliveredItem,
        int $remainingCount,
    ): string {
        return WarrantyOrderSupport::supportMessage(
            $isWarrantyEnabled,
            $isDeliveredItem,
            $remainingCount
        );
    }

    private function resolvePublishedPolicyVersion(): ?string
    {
        return (new WarrantyPolicyVersionResolver())->resolvePublishedVersion();
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
