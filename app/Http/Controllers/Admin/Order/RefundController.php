<?php

namespace App\Http\Controllers\Admin\Order;

use App\Contracts\Repositories\AdminWalletRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\LoyaltyPointTransactionRepositoryInterface;
use App\Contracts\Repositories\OrderDetailRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\RefundRequestRepositoryInterface;
use App\Contracts\Repositories\RefundStatusRepositoryInterface;
use App\Contracts\Repositories\RefundTransactionRepositoryInterface;
use App\Contracts\Repositories\VendorWalletRepositoryInterface;
use App\Enums\StockReason;
use App\Enums\ViewPaths\Admin\RefundRequest;
use App\Enums\ExportFileNames\Admin\RefundRequest as RefundRequestExportFile;
use App\Events\RefundEvent;
use App\Exports\RefundRequestExport;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\RefundStatusRequest;
use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\RefundRequest as RefundRequestModel;
use App\Models\RefundStatus;
use App\Models\SupportTicket;
use App\Services\InventoryMutationService;
use App\Services\RefundStatusService;
use App\Services\RefundTransactionService;
use App\Traits\CustomerTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RefundController extends BaseController
{
    use CustomerTrait;

    public function __construct(
        private readonly RefundRequestRepositoryInterface           $refundRequestRepo,
        private readonly CustomerRepositoryInterface                $customerRepo,
        private readonly OrderRepositoryInterface                   $orderRepo,
        private readonly OrderDetailRepositoryInterface             $orderDetailRepo,
        private readonly AdminWalletRepositoryInterface             $adminWalletRepo,
        private readonly VendorWalletRepositoryInterface            $vendorWalletRepo,
        private readonly RefundStatusRepositoryInterface            $refundStatusRepos,
        private readonly RefundTransactionRepositoryInterface       $refundTransactionRepo,
        private readonly LoyaltyPointTransactionRepositoryInterface $loyaltyPointTransactionRepo
    ) {}

    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->getListView(request: $request, status: $type);
    }

    public function getListView(Request $request, $status): View
    {
        $refundList = $this->refundRequestRepo->getListWhereHas(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            filters: ['status' => $status],
            whereHas: 'order',
            whereHasFilters: ['seller_is' => $request['type']],
            relations: ['order', 'order.seller', 'order.deliveryMan', 'product'],
            dataLimit: getWebConfig('pagination_limit'),
        );
        return view(RefundRequest::LIST[VIEW], compact('refundList', 'status'));
    }

    public function getDetailsView($id): View
    {
        $refund = $this->refundRequestRepo->getFirstWhere(params: ['id' => $id], relations: ['order.details'],);
        $order = $refund->order;
        $totalProductPrice = 0;
        foreach ($order->details as $key => $or_d) {
            $totalProductPrice += ($or_d->qty * $or_d->price) + $or_d->tax - $or_d->discount;
        }
        $subtotal = $refund->orderDetails->price * $refund->orderDetails->qty - $refund->orderDetails->discount + $refund->orderDetails->tax;
        $couponDiscount = ($order->discount_amount * $subtotal) / $totalProductPrice;
        $refundAmount = $subtotal - $couponDiscount;

        $walletStatus = getWebConfig(name: 'wallet_status');
        $walletAddRefund = getWebConfig(name: 'wallet_add_refund');
        $branches = Branch::query()
            ->where('status', 'active')
            ->orderBy('branch_name')
            ->get(['id', 'branch_name']);
        $defaultRestockBranchId = (int)($order->transfer_from_branch ?? 0);
        if ($defaultRestockBranchId <= 0) {
            $defaultRestockBranchId = (int)($order->pickup_from_branch ?? 0);
        }
        if ($defaultRestockBranchId <= 0) {
            $defaultRestockBranchId = 1;
        }

        return view(
            RefundRequest::DETAILS[VIEW],
            compact(
                'refund',
                'order',
                'totalProductPrice',
                'subtotal',
                'couponDiscount',
                'refundAmount',
                'walletStatus',
                'walletAddRefund',
                'branches',
                'defaultRestockBranchId'
            )
        );
    }

    public function updateRefundStatus(RefundStatusRequest $request, RefundStatusService $refundStatusService, RefundTransactionService $refundTransactionService): JsonResponse
    {
        $currentRefund = $this->refundRequestRepo->getFirstWhere(params: ['id' => $request['id']]);
        if (!$currentRefund) {
            return response()->json(['error' => 'Refund request not found.'], 404);
        }

        if ($currentRefund['status'] == 'refunded') {
            return response()->json(['error' => translate('when_refund_status_refunded') . ',' . translate('then_you_can`t_change_refund_status') . '.'], 409);
        }
        $user = $this->customerRepo->getFirstWhere(params: ['id' => $currentRefund['customer_id']]);

        if (!isset($user)) {
            return response()->json(['error' => translate('this_account_has_been_deleted_you_can_not_modify_the_status') . '.'], 404);
        }

        $loyaltyPoint = $this->countLoyaltyPointForAmount(id: $currentRefund['order_details_id']);

        $eventPayload = [];
        try {
            DB::transaction(function () use ($request, $refundStatusService, $refundTransactionService, $loyaltyPoint, &$eventPayload) {
                $refund = RefundRequestModel::query()->lockForUpdate()->find($request['id']);
                if (!$refund) {
                    throw new \RuntimeException('Refund request not found.', 404);
                }

                if ($refund['status'] == 'refunded') {
                    throw new \RuntimeException(translate('refunded_status_can_not_be_changed') . '.', 409);
                }

                $order = Order::query()->with('details')->lockForUpdate()->find($refund['order_id']);
                $orderDetails = OrderDetail::query()->lockForUpdate()->find($refund['order_details_id']);
                if (!$order || !$orderDetails) {
                    throw new \RuntimeException('Order or order detail not found for refund request.', 404);
                }

                if ($request['refund_status'] == 'refunded') {
                    $refund['amount'] = $this->calculateRefundAmount(order: $order, orderDetails: $orderDetails);
                    $refund->save();

                    $existingSettlement = $this->refundTransactionRepo->getFirstWhere(params: ['refund_id' => $refund['id']]);
                    if (!$existingSettlement) {
                        if ($order['seller_is'] == 'admin') {
                            $adminWallet = $this->adminWalletRepo->getFirstWhere(params: ['admin_id' => $order['seller_id']]);
                            $this->adminWalletRepo->updateWhere(params: ['admin_id' => $order['seller_id']], data: ['inhouse_earning' => $adminWallet['inhouse_earning'] - $refund['amount']]);
                        } else {
                            $sellerWallet = $this->vendorWalletRepo->getFirstWhere(params: ['seller_id' => $order['seller_id']]);
                            $this->vendorWalletRepo->updateWhere(params: ['seller_id' => $order['seller_id']], data: ['total_earning' => $sellerWallet['total_earning'] - $refund['amount']]);
                        }
                        $this->refundTransactionRepo->add(data: $refundTransactionService->getData(request: $request, refund: $refund, order: $order));
                    }

                    if ($this->shouldRestockOnRefund($request)) {
                        $stockReconcile = $this->reconcileStockOnRefund(
                            order: $order,
                            orderDetails: $orderDetails,
                            refundId: (int)$refund['id'],
                            branchIdOverride: (int)$request->input('restock_branch_id', 0)
                        );
                        if (!($stockReconcile['status'] ?? false)) {
                            throw new \RuntimeException($stockReconcile['message'] ?? 'Stock reconciliation failed', 409);
                        }
                    }
                }

                $dataArray = $refundStatusService->getRefundStatusProcessData(request: $request, orderDetails: $orderDetails, refund: $refund, loyaltyPoint: $loyaltyPoint);

                if ($request['refund_status'] == 'refunded' && $loyaltyPoint > 0 && getWebConfig(name: 'loyalty_point_status') == 1) {
                    $this->loyaltyPointTransactionRepo->addLoyaltyPointTransaction(userId: $refund['customer_id'], reference: $refund['order_id'], amount: $loyaltyPoint, transactionType: 'refund_order');
                }

                $this->orderDetailRepo->update(id: $refund['order_details_id'], data: ['refund_request' => $dataArray['orderDetails']['refund_request']]);
                $this->refundRequestRepo->update(id: $request['id'], data: $dataArray['refund']);
                $this->refundStatusRepos->add(data: $dataArray['refundStatus']);
                $ticket = SupportTicket::where('customer_id', $refund['customer_id'])
                    ->where('type', 'retail')
                    ->where('sub_type', 'refund')
                    ->where('source_id', $refund['id']) // source_id = refund_request ID
                    ->first();

                if ($ticket) {
                    $ticket->status = match ($request['refund_status']) {
                        'approved' => 'Refund Approved',
                        'rejected' => 'Refund Rejected',
                        'refunded' => 'Refund Posted',
                        default => $ticket->status
                    };
                    $ticket->save();
                }

                $eventPayload = [
                    'status' => $request['refund_status'],
                    'order' => $order,
                    'refund' => $refund,
                    'orderDetails' => $orderDetails,
                ];
            });
        } catch (\RuntimeException $exception) {
            $statusCode = (int)$exception->getCode();
            $statusCode = ($statusCode >= 100 && $statusCode <= 599) ? $statusCode : 409;
            return response()->json(['error' => $exception->getMessage()], $statusCode);
        } catch (\Throwable $exception) {
            return response()->json(['error' => 'Refund status update failed.'], 500);
        }

        event(new RefundEvent(
            status: $eventPayload['status'],
            order: $eventPayload['order'],
            refund: $eventPayload['refund'],
            orderDetails: $eventPayload['orderDetails']
        ));

        return response()->json(['message' => translate('refund_status_updated') . '.']);
    }

    private function shouldRestockOnRefund(RefundStatusRequest $request): bool
    {
        return $request->input('inventory_action', 'restock') !== 'no_restock';
    }

    private function calculateRefundAmount(Order $order, OrderDetail $orderDetails): float
    {
        $totalProductPrice = 0;
        foreach ($order->details as $detail) {
            $totalProductPrice += ($detail->qty * $detail->price) + $detail->tax - $detail->discount;
        }

        if ($totalProductPrice <= 0) {
            return 0;
        }

        $subtotal = ($orderDetails->price * $orderDetails->qty) - $orderDetails->discount + $orderDetails->tax;
        $couponDiscount = (($order->discount_amount ?? 0) * $subtotal) / $totalProductPrice;
        return max(0, (float)($subtotal - $couponDiscount));
    }

    private function reconcileStockOnRefund(Order $order, OrderDetail $orderDetails, int $refundId, int $branchIdOverride = 0): array
    {
        if ((int)$orderDetails->qty <= 0 || (int)$orderDetails->is_stock_decreased === 0) {
            return ['status' => true];
        }

        $product = Product::query()->find($orderDetails->product_id);
        if (!$product || $product->product_type !== 'physical') {
            return ['status' => true];
        }

        $branchId = $branchIdOverride > 0 ? $branchIdOverride : (int)($order->transfer_from_branch ?? 0);
        if ($branchId <= 0) {
            $branchId = (int)($order->pickup_from_branch ?? 0);
        }
        if ($branchId <= 0) {
            $branchId = 1;
        }

        $stockResponse = app(InventoryMutationService::class)->manualAdjust(
            productId: (int)$orderDetails->product_id,
            branchId: $branchId,
            variant: $orderDetails->variant,
            delta: (int)$orderDetails->qty,
            note: "Refund settlement #{$refundId}",
            stockReason: StockReason::RETURN,
            referenceId: $refundId,
            context: 'Refund Settlement'
        );

        if (!($stockResponse['status'] ?? false)) {
            return ['status' => false, 'message' => $stockResponse['message'] ?? 'Stock update failed'];
        }

        $orderDetails->is_stock_decreased = 0;
        $orderDetails->delivery_status = 'returned';
        $orderDetails->save();

        return ['status' => true];
    }

    public function exportList(Request $request, $status): BinaryFileResponse
    {
        $refundList = $this->refundRequestRepo->getListWhereHas(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            filters: ['status' => $status],
            whereHas: 'order',
            whereHasFilters: ['seller_is' => $request['type']],
            relations: ['order', 'order.seller', 'order.deliveryMan', 'product'],
            dataLimit: 'all',
        );
        return Excel::download(new RefundRequestExport([
            'data-from' => 'admin',
            'refundList' => $refundList,
            'search' => $request['searchValue'],
            'status' => $status,
            'filter_By' => $request->get('type', 'all'),
        ]), RefundRequestExportFile::EXPORT_XLSX);
    }
}
