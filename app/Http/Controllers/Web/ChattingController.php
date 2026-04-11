<?php

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\ChattingRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\DeliveryManRepositoryInterface;
use App\Contracts\Repositories\ShopRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Enums\ViewPaths\Web\Chatting;
use App\Events\ChattingEvent;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Web\ChattingRequest;
use App\Services\ChattingService;
use App\Traits\PushNotificationTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ChattingController extends BaseController
{
    use PushNotificationTrait;

    /**
     * @param ChattingRepositoryInterface $chattingRepo
     * @param ShopRepositoryInterface $shopRepo
     * @param ChattingService $chattingService
     * @param DeliveryManRepositoryInterface $deliveryManRepo
     * @param CustomerRepositoryInterface $customerRepo
     * @param VendorRepositoryInterface $vendorRepo
     */
    public function __construct(
        private readonly ChattingRepositoryInterface    $chattingRepo,
        private readonly ShopRepositoryInterface        $shopRepo,
        private readonly ChattingService                $chattingService,
        private readonly DeliveryManRepositoryInterface $deliveryManRepo,
        private readonly CustomerRepositoryInterface    $customerRepo,
        private readonly VendorRepositoryInterface      $vendorRepo,
    )
    {
    }

    /**
     * @param Request|null $request
     * @param string|array|null $type
     * @return View|Collection|LengthAwarePaginator|callable|RedirectResponse|null
     */
    public function index(?Request $request, string|array $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->getListView(type: $type);
    }

    /**
     * @param string|array $type
     * @return View
     */
    public function getListView(string|array $type): View
    {
        return match ($type) {
            'delivery-man' => $this->getChatList(relation: ['deliveryMan'], columnName: 'delivery_man_id', type: $type),
            'vendor' => $this->getChatList(relation: ['shop', 'seller.shop'], columnName: 'seller_id', type: $type),
        };
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getMessageByUser(Request $request): JsonResponse
    {
        if ($request->has(key: 'delivery_man_id')) {
            $getUser = $this->deliveryManRepo->getFirstWhere(params: ['id' => $request['delivery_man_id']]);
            $requestColumn = 'delivery_man_id';
            $requestId = $request['delivery_man_id'];
            $whereNotNull = ['user_id', 'delivery_man_id'];
            $relation = ['deliveryMan'];
            $type = 'delivery-man';
        } elseif ($request->has(key: 'vendor_id') && $request['vendor_id'] == 0) {
            $getUser = 'admin';
            $requestColumn = 'admin_id';
            $requestId = 0;
            $whereNotNull = ['user_id', 'admin_id'];
            $relation = ['admin'];
            $type = 'admin';
        } else {
            $vendorData = $this->vendorRepo->getFirstWhere(params: ['id' => $request['vendor_id']], relations: ['shop']);
            $getUser = $vendorData->shop;
            $requestColumn = 'seller_id';
            $requestId = $request['vendor_id'];
            $whereNotNull = ['user_id', 'seller_id', 'shop_id'];
            $relation = ['shop', 'seller.shop'];
            $type = 'vendor';
        }
        $this->updateAllUnseenMessageStatus(requestColumn: $requestColumn, requestId: $requestId);
        $chattingMessages = $this->getMessage(requestColumn: $requestColumn, requestId: $requestId, whereNotNull: $whereNotNull, relation: $relation);
        $data = self::getRenderMessagesView(user: $getUser, message: $chattingMessages, type: $type);
        return response()->json($data);
    }

    /**
     * @param ChattingRequest $request
     * @return JsonResponse
     */
    public function addMessage(ChattingRequest $request): JsonResponse
    {
        $customerId = auth('customer')->id();
        $customer = $this->customerRepo->getFirstWhere(params: ['id' => $customerId]);
        if ($request->has(key: 'delivery_man_id')) {
            $this->chattingRepo->add(
                data: $this->chattingService->addChattingDataForWeb(
                    request: $request,
                    userId: $customerId,
                    type: 'deliveryman',
                    deliveryManId: $request['delivery_man_id']
                )
            );
            $getUser = $this->deliveryManRepo->getFirstWhere(params: ['id' => $request['delivery_man_id']]);
            $requestColumn = 'delivery_man_id';
            $requestId = $request['delivery_man_id'];
            $whereNotNull = ['user_id', 'delivery_man_id'];
            $relation = ['deliveryMan'];
            $type = 'delivery-man';
            event(new ChattingEvent(key: 'message_from_customer', type: 'delivery_man', userData: $getUser, messageForm: $customer));
        } elseif ($request->has(key: 'vendor_id') && $request['vendor_id'] == 0) {
            $this->chattingRepo->add(
                data: $this->chattingService->addChattingDataForWeb(
                    request: $request,
                    userId: $customerId,
                    type: 'admin',
                    adminId: $request['vendor_id']
                )
            );
            $getUser = 'admin';
            $requestColumn = 'admin_id';
            $requestId = 0;
            $whereNotNull = ['user_id', 'admin_id'];
            $relation = ['admin'];
            $type = 'admin';
        } else {
            $vendorData = $this->vendorRepo->getFirstWhere(params: ['id' => $request['vendor_id']], relations: ['shop']);

            $this->chattingRepo->add(
                data: $this->chattingService->addChattingDataForWeb(
                    request: $request,
                    userId: $customerId,
                    type: 'seller',
                    shopId: $vendorData?->shop?->id,
                    vendorId: $vendorData['id'])
            );

            event(new ChattingEvent(key: 'message_from_customer', type: 'seller', userData: $vendorData, messageForm: $customer));
            $getUser = $vendorData->shop;
            $requestColumn = 'seller_id';
            $requestId = $vendorData['id'];
            $whereNotNull = ['user_id', 'seller_id', 'shop_id'];
            $relation = ['shop', 'seller.shop'];
            $type = 'vendor';
        }
        $chattingMessages = $this->getMessage(requestColumn: $requestColumn, requestId: $requestId, whereNotNull: $whereNotNull, relation: $relation);
        $data = self::getRenderMessagesView(user: $getUser, message: $chattingMessages, type: $type);
        return response()->json($data);
    }

    /**
     * @param array $relation
     * @param string $columnName
     * @param string $type
     * @return View
     */
    private function getChatList(array $relation, string $columnName, string $type): View
    {
        $customerId = auth('customer')->id();
        $allChattingUsers = $this->chattingRepo->getListWhereNotNull(
            orderBy: ['created_at' => 'DESC'],
            filters: ['user_id' => $customerId],
            whereNotNull: [$columnName],
            relations: $relation,
            dataLimit: 'all'
        )->unique($columnName);

        if ($type == 'vendor') {
            $inHouseInfo = $this->chattingRepo->getListWhereNotNull(
                orderBy: ['created_at' => 'DESC'],
                filters: ['user_id' => $customerId],
                whereNotNull: ['admin_id'],
                relations: ['admin'],
                dataLimit: 'all'
            )->unique('admin_id');
            $allChattingUsers = $inHouseInfo->count() > 0 ? ($allChattingUsers->merge($inHouseInfo))->sortByDesc('created_at') : $allChattingUsers;
        }

        $allChattingUsers = $allChattingUsers
            ->filter(fn($chatting) => !is_null($this->resolveChatParticipantContext($chatting)))
            ->values();

        $allChattingUsers->map(function ($chatting) use ($customerId) {
            $chatParticipantContext = $this->resolveChatParticipantContext($chatting);
            if (is_null($chatParticipantContext)) {
                return;
            }

            $filter = [
                'user_id' => $customerId,
                $chatParticipantContext['column'] => $chatParticipantContext['id'],
                'sent_by_customer' => 0,
                'seen_by_customer' => 0,
            ];
            $unseenMessageCount = $this->chattingRepo->getListWhere(
                filters: $filter, dataLimit: 'all'
            )->count();
            $chatting['unseen_message_count'] = $unseenMessageCount;
        });

        $lastChatUser = null;
        $lastChatContext = null;
        foreach ($allChattingUsers as $key => $value) {
            $lastChatContext = $this->resolveChatParticipantContext($value);
            if (is_null($lastChatContext)) {
                continue;
            }

            $lastChatUser = $lastChatContext['user'];
            $columnName = $lastChatContext['column'];
            $type = $lastChatContext['type'];
            $relation = $lastChatContext['relation'];
            break;
        }

        if ($lastChatUser) {
            $this->updateAllUnseenMessageStatus(requestColumn: $columnName, requestId: $lastChatContext['id']);
            $chattingMessages = $this->getMessage(requestColumn: $columnName, requestId: $lastChatContext['id'], whereNotNull: ['user_id', $columnName], relation: $relation);
        } else {
            $chattingMessages = [];
        }
        return view(VIEW_FILE_NAMES['user_inbox'], [
            'userType' => $type,
            'userData' => $lastChatUser ? $this->getUserData(type: $type, user: ($lastChatUser['id'] == 0 ? 'admin' : $lastChatUser)) : '',
            'allChattingUsers' => $allChattingUsers,
            'lastChatUser' => $lastChatUser,
            'chattingMessages' => $chattingMessages,
        ]);
    }

    /**
     * @param object|string $user
     * @param object $message
     * @param string $type
     * @return array
     */
    protected function getRenderMessagesView(object|string $user, object $message, string $type): array
    {
        return [
            'userData' => $this->getUserData(type: $type, user: $user),
            'chattingMessages' => view(VIEW_FILE_NAMES['user_inbox_message'], [
                'lastChatUser' => $user,
                'userType' => $type,
                'chattingMessages' => $message
            ])->render(),
        ];
    }

    private function getUserData($type, $user): array
    {
        if ($type == 'vendor') {
            $userData = ['name' => $user['name'], 'phone' => $user['contact']];
            $userData['image'] = getStorageImages(path: $user->image_full_url, type: 'shop');
            $userData['temporary-close-status'] = (int)$user->temporary_close;
        } elseif ($type == 'delivery-man') {
            $userData = ['name' => $user['f_name'] . ' ' . $user['l_name'], 'phone' => $user['country_code'] . $user['phone']];
            $userData['image'] = getStorageImages(path: $user->image_full_url, type: 'avatar');
            $userData['temporary-close-status'] = '';

        } else {
            $userData = ['name' => getWebConfig('company_name'), 'phone' => ''];
            $userData['image'] = getStorageImages(path: getWebConfig('company_fav_icon'), type: 'shop');
            $userData['temporary-close-status'] = (int)getWebConfig(name: 'temporary_close')['status'];
        }
        return $userData;
    }

    private function getMessage($requestColumn, $requestId, $whereNotNull, $relation): Collection
    {
        $customerId = auth('customer')->id();
        $orderBy = theme_root_path() == 'default' ? ['created_at' => 'DESC'] : ['created_at' => 'ASC'];
        return $this->chattingRepo->getListWhereNotNull(
            orderBy: $orderBy,
            filters: ['user_id' => $customerId, $requestColumn => $requestId],
            whereNotNull: $whereNotNull,
            relations: $relation,
            dataLimit: 'all'
        );
    }

    private function updateAllUnseenMessageStatus($requestColumn, $requestId): void
    {
        $customerId = auth('customer')->id();
        $this->chattingRepo->updateAllWhere(
            params: ['user_id' => $customerId, $requestColumn => $requestId],
            data: ['sent_by_customer' => 1]
        );
    }

    private function resolveChatParticipantContext(object $chatting): ?array
    {
        if (!is_null($chatting->admin_id)) {
            return [
                'column' => 'admin_id',
                'id' => $chatting->admin_id,
                'type' => 'admin',
                'relation' => ['admin'],
                'user' => ['id' => 0],
            ];
        }

        if (!is_null($chatting->seller_id)) {
            $shop = $chatting->shop ?? $chatting?->seller?->shop;
            if (is_null($shop)) {
                return null;
            }

            return [
                'column' => 'seller_id',
                'id' => $chatting->seller_id,
                'type' => 'vendor',
                'relation' => ['shop', 'seller.shop'],
                'user' => $shop,
            ];
        }

        if (!is_null($chatting->delivery_man_id) && !is_null($chatting->deliveryMan)) {
            return [
                'column' => 'delivery_man_id',
                'id' => $chatting->delivery_man_id,
                'type' => 'delivery-man',
                'relation' => ['deliveryMan'],
                'user' => $chatting->deliveryMan,
            ];
        }

        return null;
    }
}
