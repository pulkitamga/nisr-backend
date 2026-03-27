<?php

namespace Tests\Unit;

use App\Http\Controllers\RestAPI\v1\OrderController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class OrderControllerGuestRegistrationAddressTest extends TestCase
{
    protected function setUp(): void
    {
        if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('pdo_sqlite extension is not available in this environment.');
        }

        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => false,
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::dropIfExists('shipping_addresses');

        Schema::create('shipping_addresses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->boolean('is_guest')->default(false);
            $table->string('contact_person_name')->nullable();
            $table->string('address_type')->nullable();
            $table->text('address')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('area')->nullable();
            $table->string('zip')->nullable();
            $table->string('country')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->boolean('is_billing')->default(false);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('shipping_addresses');

        parent::tearDown();
    }

    public function test_resolve_guest_registration_address_uses_billing_address_for_pickup_checkout(): void
    {
        DB::table('shipping_addresses')->insert([
            'id' => 15,
            'customer_id' => 77,
            'is_guest' => 1,
            'contact_person_name' => 'Pickup Guest',
            'address_type' => 'home',
            'address' => 'Pickup Billing Address',
            'state' => 'Alexandria',
            'city' => 'Montaza',
            'area' => 'Sidi Bishr',
            'zip' => '21500',
            'country' => 'Egypt',
            'phone' => '+201111111111',
            'email' => 'pickup@example.com',
            'latitude' => '31.2500',
            'longitude' => '29.9600',
            'is_billing' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = Request::create('/api/v1/customer/order/place', 'GET', [
            'guest_id' => 77,
            'billing_address_id' => 15,
            'payment_request_from' => 'app',
            'is_guest' => 1,
        ]);

        $controller = new OrderController;
        $method = new ReflectionMethod(OrderController::class, 'resolveGuestRegistrationAddress');
        $method->setAccessible(true);

        $address = $method->invoke($controller, $request);

        $this->assertInstanceOf(\App\Models\ShippingAddress::class, $address);
        $this->assertSame(15, $address->id);
        $this->assertSame('pickup@example.com', $address->email);
    }
}
