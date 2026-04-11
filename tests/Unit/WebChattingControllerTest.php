<?php

namespace Tests\Unit;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\DeliveryManRepositoryInterface;
use App\Contracts\Repositories\ShopRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Http\Controllers\Web\ChattingController;
use App\Models\Chatting as ChattingModel;
use App\Models\Seller;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\ChattingRepository;
use App\Services\ChattingService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class WebChattingControllerTest extends TestCase
{
    public function test_vendor_chat_list_skips_orphaned_vendor_rows_and_uses_seller_id_for_filters(): void
    {
        View::addLocation(base_path('resources/themes/default'));
        if (!defined('VIEW_FILE_NAMES')) {
            define('VIEW_FILE_NAMES', require base_path('resources/themes/default/file_names.php'));
        }

        auth('customer')->setUser(new User(['id' => 7]));

        $chattingRepo = $this->getMockBuilder(ChattingRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getListWhereNotNull', 'getListWhere', 'updateAllWhere'])
            ->getMock();

        $orphanedVendorChat = new ChattingModel([
            'id' => 10,
            'user_id' => 7,
            'seller_id' => 99,
            'message' => 'orphaned vendor message',
            'sent_by_seller' => true,
            'created_at' => Carbon::parse('2026-04-10 10:00:00'),
        ]);
        $orphanedVendorChat->setRelation('seller', new Seller(['id' => 99]));

        $vendorShop = new Shop([
            'seller_id' => 44,
            'name' => 'Vendor Shop',
            'contact' => '01000000000',
            'temporary_close' => false,
            'image' => 'shop.png',
        ]);
        $vendorShop->id = 501;

        $validVendorChat = new ChattingModel([
            'id' => 11,
            'user_id' => 7,
            'seller_id' => 44,
            'shop_id' => 501,
            'message' => 'valid vendor message',
            'sent_by_seller' => true,
            'created_at' => Carbon::parse('2026-04-10 11:00:00'),
        ]);
        $validVendorChat->setRelation('shop', $vendorShop);
        $validVendorChat->setRelation('seller', new Seller(['id' => 44]));

        $chattingRepo->expects($this->exactly(3))
            ->method('getListWhereNotNull')
            ->willReturnCallback(function (
                array $orderBy = [],
                ?string $searchValue = null,
                array $filters = [],
                array $whereNotNull = [],
                array $relations = [],
                int|string $dataLimit = DEFAULT_DATA_LIMIT,
                ?int $offset = null
            ) use ($orphanedVendorChat, $validVendorChat) {
                $this->assertNull($searchValue);
                $this->assertSame('all', $dataLimit);
                $this->assertNull($offset);

                if ($whereNotNull === ['seller_id']) {
                    $this->assertSame(['created_at' => 'DESC'], $orderBy);
                    $this->assertSame(['user_id' => 7], $filters);
                    $this->assertSame(['shop', 'seller.shop'], $relations);

                    return new Collection([$orphanedVendorChat, $validVendorChat]);
                }

                if ($whereNotNull === ['admin_id']) {
                    $this->assertSame(['created_at' => 'DESC'], $orderBy);
                    $this->assertSame(['user_id' => 7], $filters);
                    $this->assertSame(['admin'], $relations);

                    return new Collection();
                }

                $this->assertSame(['created_at' => 'DESC'], $orderBy);
                $this->assertSame(['user_id' => 7, 'seller_id' => 44], $filters);
                $this->assertSame(['user_id', 'seller_id'], $whereNotNull);
                $this->assertSame(['shop', 'seller.shop'], $relations);

                return new Collection([$validVendorChat]);
            });

        $chattingRepo->expects($this->once())
            ->method('getListWhere')
            ->with([], null, [
                'user_id' => 7,
                'seller_id' => 44,
                'sent_by_customer' => 0,
                'seen_by_customer' => 0,
            ], [], 'all', null)
            ->willReturn(new Collection([$validVendorChat]));

        $chattingRepo->expects($this->once())
            ->method('updateAllWhere')
            ->with(
                ['user_id' => 7, 'seller_id' => 44],
                ['sent_by_customer' => 1]
            )
            ->willReturn(true);

        $controller = new ChattingController(
            $chattingRepo,
            $this->createMock(ShopRepositoryInterface::class),
            $this->createMock(ChattingService::class),
            $this->createMock(DeliveryManRepositoryInterface::class),
            $this->createMock(CustomerRepositoryInterface::class),
            $this->createMock(VendorRepositoryInterface::class),
        );

        $view = $controller->getListView('vendor');
        $data = $view->getData();

        $this->assertSame('vendor', $data['userType']);
        $this->assertCount(1, $data['allChattingUsers']);
        $this->assertSame(44, $data['allChattingUsers']->first()->seller_id);
        $this->assertSame('Vendor Shop', $data['lastChatUser']->name);
        $this->assertCount(1, $data['chattingMessages']);
    }
}
