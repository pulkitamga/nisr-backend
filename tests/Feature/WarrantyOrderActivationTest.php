<?php

namespace Tests\Feature;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Http\Controllers\Admin\WarrantyController as AdminWarrantyController;
use App\Http\Controllers\RestAPI\v1\WarrantyCustomerController;
use App\Models\ActivationReview;
use App\Models\BusinessSetting;
use App\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class WarrantyOrderActivationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
        $this->seedBusinessSettings();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_api_order_activation_accepts_unassigned_serial_inventory(): void
    {
        $customer = $this->makeCustomer(77);
        $productId = $this->createProduct(isTraceable: true);
        $orderId = $this->createOrder(customerId: $customer->id);
        $orderDetailId = $this->createOrderDetail(orderId: $orderId, productId: $productId, quantity: 1);

        DB::table('warranties')->insert([
            'serial_number' => 'SERIAL-UNASSIGNED',
            'product_id' => null,
            'status' => 'preactivated',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $controller = $this->makeController();
        $request = $this->makeRequest([
            'serial_numbers' => ['SERIAL-UNASSIGNED'],
            'agree_terms' => '1',
        ], $customer);

        $response = $controller->activateOrderWarranty($request, $orderDetailId);
        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($payload['success']);
        $this->assertSame(['SERIAL-UNASSIGNED'], $payload['activated_serials']);

        $this->assertDatabaseHas('warranties', [
            'serial_number' => 'SERIAL-UNASSIGNED',
            'product_id' => $productId,
            'status' => 'active',
            'final_user_id' => $customer->id,
            'invoice_number' => (string)$orderId,
            'activation_method' => 'order_activation',
        ]);

        $this->assertDatabaseHas('order_details', [
            'id' => $orderDetailId,
            'warranty_status' => 1,
        ]);
    }

    public function test_api_order_activation_still_rejects_serial_bound_to_another_product(): void
    {
        $customer = $this->makeCustomer(88);
        $productId = $this->createProduct(isTraceable: true);
        $otherProductId = $this->createProduct(isTraceable: true);
        $orderId = $this->createOrder(customerId: $customer->id);
        $orderDetailId = $this->createOrderDetail(orderId: $orderId, productId: $productId, quantity: 1);

        DB::table('warranties')->insert([
            'serial_number' => 'SERIAL-MISMATCHED',
            'product_id' => $otherProductId,
            'status' => 'preactivated',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $controller = $this->makeController();
        $request = $this->makeRequest([
            'serial_numbers' => ['SERIAL-MISMATCHED'],
            'agree_terms' => '1',
        ], $customer);

        $response = $controller->activateOrderWarranty($request, $orderDetailId);
        $payload = $response->getData(true);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertFalse($payload['success']);
        $this->assertSame([], $payload['activated_serials']);
        $this->assertSame(['SERIAL-MISMATCHED'], $payload['failed_serials']);

        $this->assertDatabaseHas('warranties', [
            'serial_number' => 'SERIAL-MISMATCHED',
            'product_id' => $otherProductId,
            'status' => 'preactivated',
            'final_user_id' => null,
            'activation_method' => null,
        ]);
    }

    public function test_api_order_activation_accepts_delivered_registered_pos_orders(): void
    {
        $customer = $this->makeCustomer(99);
        $productId = $this->createProduct(isTraceable: true);
        $orderId = $this->createOrder(customerId: $customer->id, orderType: 'POS');
        $orderDetailId = $this->createOrderDetail(
            orderId: $orderId,
            productId: $productId,
            quantity: 1,
            deliveryStatus: null
        );

        DB::table('warranties')->insert([
            'serial_number' => 'SERIAL-POS-DELIVERED',
            'product_id' => $productId,
            'status' => 'preactivated',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $controller = $this->makeController();
        $request = $this->makeRequest([
            'serial_numbers' => ['SERIAL-POS-DELIVERED'],
            'agree_terms' => '1',
        ], $customer);

        $response = $controller->activateOrderWarranty($request, $orderDetailId);
        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($payload['success']);
        $this->assertSame(['SERIAL-POS-DELIVERED'], $payload['activated_serials']);

        $this->assertDatabaseHas('warranties', [
            'serial_number' => 'SERIAL-POS-DELIVERED',
            'status' => 'active',
            'final_user_id' => $customer->id,
            'invoice_number' => (string)$orderId,
            'activation_method' => 'order_activation',
        ]);
    }

    public function test_api_order_activation_does_not_expire_and_uses_order_purchase_date(): void
    {
        $customer = $this->makeCustomer(109);
        $productId = $this->createProduct(isTraceable: true);
        $orderId = $this->createOrder(customerId: $customer->id);
        $orderDetailId = $this->createOrderDetail(orderId: $orderId, productId: $productId, quantity: 1);
        $purchaseDate = now()->subDays(45)->startOfSecond();

        DB::table('orders')
            ->where('id', $orderId)
            ->update([
                'created_at' => $purchaseDate,
                'updated_at' => $purchaseDate,
            ]);

        DB::table('warranties')->insert([
            'serial_number' => 'SERIAL-OLD-ORDER',
            'product_id' => $productId,
            'status' => 'preactivated',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $controller = $this->makeController();
        $request = $this->makeRequest([
            'serial_numbers' => ['SERIAL-OLD-ORDER'],
            'agree_terms' => '1',
        ], $customer);

        $response = $controller->activateOrderWarranty($request, $orderDetailId);
        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($payload['success']);
        $this->assertSame(['SERIAL-OLD-ORDER'], $payload['activated_serials']);

        $warranty = DB::table('warranties')
            ->where('serial_number', 'SERIAL-OLD-ORDER')
            ->first();

        $this->assertNotNull($warranty);
        $this->assertSame($purchaseDate->format('Y-m-d H:i:s'), $warranty->activation_date);
        $this->assertSame($purchaseDate->format('Y-m-d H:i:s'), $warranty->start_date);
        $this->assertSame($purchaseDate->format('Y-m-d H:i:s'), $warranty->purchase_date);
    }

    public function test_api_order_activation_rejects_traceable_product_without_warranty_flag(): void
    {
        $customer = $this->makeCustomer(119);
        $productId = $this->createProduct(isTraceable: true, isWarranty: false);
        $orderId = $this->createOrder(customerId: $customer->id);
        $orderDetailId = $this->createOrderDetail(orderId: $orderId, productId: $productId, quantity: 1);

        DB::table('warranties')->insert([
            'serial_number' => 'SERIAL-NO-WARRANTY-FLAG',
            'product_id' => $productId,
            'status' => 'preactivated',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $controller = $this->makeController();
        $request = $this->makeRequest([
            'serial_numbers' => ['SERIAL-NO-WARRANTY-FLAG'],
            'agree_terms' => '1',
        ], $customer);

        $response = $controller->activateOrderWarranty($request, $orderDetailId);
        $payload = $response->getData(true);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertFalse($payload['success']);

        $this->assertDatabaseHas('warranties', [
            'serial_number' => 'SERIAL-NO-WARRANTY-FLAG',
            'status' => 'preactivated',
            'final_user_id' => null,
            'activation_method' => null,
        ]);
    }

    public function test_admin_manual_activation_rejects_already_active_serial(): void
    {
        $productId = $this->createProduct(isTraceable: true);
        $purchaseDate = now()->subDays(2)->startOfSecond();
        $endDate = now()->addMonths(12)->startOfSecond();

        DB::table('warranties')->insert([
            'serial_number' => 'SERIAL-ACTIVE-MANUAL-BLOCK',
            'product_id' => $productId,
            'status' => 'active',
            'activation_date' => $purchaseDate,
            'start_date' => $purchaseDate,
            'end_date' => $endDate,
            'purchase_date' => $purchaseDate,
            'activation_method' => 'user_public_form',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $controller = new AdminWarrantyController();
        $request = Request::create('/admin/warranty/activation/manual', 'POST', [
            'serial_number' => 'SERIAL-ACTIVE-MANUAL-BLOCK',
            'purchase_date' => now()->toDateString(),
            'reason' => 'Customer requested duplicate activation',
        ]);

        $response = $controller->manualActivate($request);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertDatabaseHas('warranties', [
            'serial_number' => 'SERIAL-ACTIVE-MANUAL-BLOCK',
            'status' => 'active',
            'activation_method' => 'user_public_form',
            'purchase_date' => $purchaseDate->format('Y-m-d H:i:s'),
        ]);
        $this->assertDatabaseMissing('warranty_timeline_events', [
            'warranty_id' => DB::table('warranties')
                ->where('serial_number', 'SERIAL-ACTIVE-MANUAL-BLOCK')
                ->value('id'),
            'event_type' => 'manual_activated',
        ]);
    }

    public function test_admin_activation_approval_rejects_already_active_serial(): void
    {
        $productId = $this->createProduct(isTraceable: true);
        $purchaseDate = now()->subDays(3)->startOfSecond();
        $endDate = now()->addMonths(12)->startOfSecond();
        $warrantyId = DB::table('warranties')->insertGetId([
            'serial_number' => 'SERIAL-ACTIVE-APPROVAL-BLOCK',
            'product_id' => $productId,
            'status' => 'active',
            'activation_date' => $purchaseDate,
            'start_date' => $purchaseDate,
            'end_date' => $endDate,
            'purchase_date' => $purchaseDate,
            'activation_method' => 'user_public_form',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $review = ActivationReview::query()->create([
            'warranty_id' => $warrantyId,
            'status' => 'pending',
        ]);

        $controller = new AdminWarrantyController();
        $request = Request::create('/admin/warranty/review/approve', 'POST', [
            'review_notes' => 'Attempting duplicate approval',
        ]);

        $response = $controller->approveActivation($review, $request);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertDatabaseHas('warranties', [
            'id' => $warrantyId,
            'status' => 'active',
            'activation_method' => 'user_public_form',
        ]);
        $this->assertDatabaseHas('activation_reviews', [
            'id' => $review->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseMissing('warranty_timeline_events', [
            'warranty_id' => $warrantyId,
            'event_type' => 'activation_approved',
        ]);
    }

    private function createTables(): void
    {
        if (!Schema::hasTable('translations')) {
            Schema::create('translations', function (Blueprint $table) {
                $table->id();
                $table->string('translationable_type');
                $table->unsignedBigInteger('translationable_id');
                $table->string('locale')->nullable();
                $table->string('key')->nullable();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('business_settings')) {
            Schema::create('business_settings', function (Blueprint $table) {
                $table->id();
                $table->string('type')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('storages')) {
            Schema::create('storages', function (Blueprint $table) {
                $table->id();
                $table->string('data_type');
                $table->unsignedBigInteger('data_id');
                $table->string('key');
                $table->string('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->boolean('status')->default(1);
                $table->boolean('is_traceable')->default(0);
                $table->boolean('is_warranty')->default(0);
                $table->softDeletes();
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('products', 'is_warranty')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('is_warranty')->default(0);
            });
        }

        if (!Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('delivery_man_id')->nullable();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->text('comment')->nullable();
                $table->text('attachment')->nullable();
                $table->integer('rating')->default(0);
                $table->integer('status')->default(1);
                $table->boolean('is_saved')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id');
                $table->boolean('is_guest')->default(0);
                $table->string('order_type')->nullable();
                $table->string('order_status')->nullable();
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('orders', 'order_type')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('order_type')->nullable();
            });
        }

        if (!Schema::hasTable('order_details')) {
            Schema::create('order_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->unsignedBigInteger('product_id')->nullable();
                $table->integer('qty')->default(1);
                $table->string('delivery_status')->nullable();
                $table->boolean('warranty_status')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('warranties')) {
            Schema::create('warranties', function (Blueprint $table) {
                $table->id();
                $table->string('serial_number')->unique();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('status')->default('preactivated');
                $table->timestamp('activation_date')->nullable();
                $table->timestamp('start_date')->nullable();
                $table->timestamp('end_date')->nullable();
                $table->timestamp('purchase_date')->nullable();
                $table->unsignedBigInteger('final_user_id')->nullable();
                $table->string('invoice_number')->nullable();
                $table->string('activation_method')->nullable();
                $table->boolean('consent_checked')->default(false);
                $table->timestamp('consent_timestamp')->nullable();
                $table->string('consent_ip')->nullable();
                $table->string('policy_version')->nullable();
                $table->string('warranty_public_id')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('blacklists')) {
            Schema::create('blacklists', function (Blueprint $table) {
                $table->id();
                $table->string('serial_number')->nullable()->unique();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('policies')) {
            Schema::create('policies', function (Blueprint $table) {
                $table->id();
                $table->string('version')->nullable();
                $table->timestamp('effective_date')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('warranty_timeline_events')) {
            Schema::create('warranty_timeline_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('warranty_id');
                $table->string('event_type');
                $table->text('description')->nullable();
                $table->timestamp('timestamp')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('activation_reviews')) {
            Schema::create('activation_reviews', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('warranty_id');
                $table->string('status')->nullable();
                $table->text('review_notes')->nullable();
                $table->unsignedBigInteger('agent_id')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    private function seedBusinessSettings(): void
    {
        DB::table('business_settings')->updateOrInsert(
            ['type' => 'warranty_activation_days'],
            ['value' => '7', 'created_at' => now(), 'updated_at' => now()]
        );
    }

    private function makeController(): WarrantyCustomerController
    {
        $businessSettingRepo = Mockery::mock(BusinessSettingRepositoryInterface::class);
        $businessSettingRepo->shouldReceive('getFirstWhere')
            ->with(['type' => 'warranty_months'])
            ->andReturn(new BusinessSetting(['value' => '12']));

        return new WarrantyCustomerController($businessSettingRepo);
    }

    private function makeRequest(array $payload, User $customer): Request
    {
        $request = Request::create('/api/test/warranties', 'POST', $payload);
        $request->setUserResolver(fn() => $customer);

        return $request;
    }

    private function makeCustomer(int $id): User
    {
        $customer = new User();
        $customer->id = $id;
        $customer->exists = true;

        return $customer;
    }

    private function createProduct(bool $isTraceable, bool $isWarranty = true): int
    {
        return DB::table('products')->insertGetId([
            'name' => 'Traceable Product',
            'status' => 1,
            'is_traceable' => $isTraceable ? 1 : 0,
            'is_warranty' => $isWarranty ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createOrder(int $customerId, string $orderType = 'default_type'): int
    {
        return DB::table('orders')->insertGetId([
            'customer_id' => $customerId,
            'is_guest' => 0,
            'order_type' => $orderType,
            'order_status' => 'delivered',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);
    }

    private function createOrderDetail(
        int $orderId,
        int $productId,
        int $quantity,
        ?string $deliveryStatus = 'delivered',
    ): int
    {
        return DB::table('order_details')->insertGetId([
            'order_id' => $orderId,
            'product_id' => $productId,
            'qty' => $quantity,
            'delivery_status' => $deliveryStatus,
            'warranty_status' => 0,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);
    }
}
