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

        Schema::dropIfExists('users');
        Schema::dropIfExists('shipping_addresses');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('f_name')->nullable();
            $table->string('l_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('referral_code')->nullable();
            $table->timestamps();
        });

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
        Schema::dropIfExists('users');
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

    public function test_guest_registration_conflict_message_uses_existing_phone_match(): void
    {
        DB::table('users')->insert([
            'id' => 1,
            'name' => 'Existing User',
            'f_name' => 'Existing User',
            'l_name' => '',
            'email' => 'existing@example.com',
            'phone' => '+201111111111',
            'password' => bcrypt('secret123'),
            'is_active' => 1,
            'referral_code' => 'REF001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('shipping_addresses')->insert([
            'id' => 16,
            'customer_id' => 78,
            'is_guest' => 1,
            'contact_person_name' => 'Phone Conflict Guest',
            'address_type' => 'home',
            'address' => 'Phone Conflict Address',
            'state' => 'Alexandria',
            'city' => 'Montaza',
            'area' => 'Sidi Bishr',
            'zip' => '21500',
            'country' => 'Egypt',
            'phone' => '+201111111111',
            'email' => 'new@example.com',
            'latitude' => '31.2500',
            'longitude' => '29.9600',
            'is_billing' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $controller = new OrderController;
        $method = new ReflectionMethod(OrderController::class, 'getGuestCustomerConflictMessage');
        $method->setAccessible(true);

        $address = \App\Models\ShippingAddress::query()->findOrFail(16);
        $message = $method->invoke($controller, $address);

        $this->assertSame(translate('Phone_already_exists'), $message);
    }

    public function test_guest_registration_conflict_message_uses_existing_email_match(): void
    {
        DB::table('users')->insert([
            'id' => 2,
            'name' => 'Existing Email User',
            'f_name' => 'Existing Email User',
            'l_name' => '',
            'email' => 'existing-email@example.com',
            'phone' => '+201222222222',
            'password' => bcrypt('secret123'),
            'is_active' => 1,
            'referral_code' => 'REF002',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('shipping_addresses')->insert([
            'id' => 17,
            'customer_id' => 79,
            'is_guest' => 1,
            'contact_person_name' => 'Email Conflict Guest',
            'address_type' => 'home',
            'address' => 'Email Conflict Address',
            'state' => 'Alexandria',
            'city' => 'Montaza',
            'area' => 'Sidi Bishr',
            'zip' => '21500',
            'country' => 'Egypt',
            'phone' => '+201333333333',
            'email' => 'existing-email@example.com',
            'latitude' => '31.2500',
            'longitude' => '29.9600',
            'is_billing' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $controller = new OrderController;
        $method = new ReflectionMethod(OrderController::class, 'getGuestCustomerConflictMessage');
        $method->setAccessible(true);

        $address = \App\Models\ShippingAddress::query()->findOrFail(17);
        $message = $method->invoke($controller, $address);

        $this->assertSame(translate('Email_already_exists'), $message);
    }
}
