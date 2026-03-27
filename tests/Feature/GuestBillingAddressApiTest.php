<?php

namespace Tests\Feature;

use App\Http\Controllers\RestAPI\v1\CustomerController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GuestBillingAddressApiTest extends TestCase
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

        Schema::dropIfExists('business_settings');
        Schema::dropIfExists('shipping_addresses');
        Schema::dropIfExists('translations');

        Schema::create('business_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('type')->unique();
            $table->longText('value')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('translationable_type');
            $table->unsignedBigInteger('translationable_id');
            $table->string('locale');
            $table->string('key')->nullable();
            $table->longText('value')->nullable();
            $table->timestamps();
        });

        Schema::create('shipping_addresses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->boolean('is_guest')->default(false);
            $table->string('contact_person_name');
            $table->string('address_type');
            $table->text('address');
            $table->string('state');
            $table->string('city');
            $table->string('area');
            $table->string('zip')->nullable();
            $table->string('country');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('latitude');
            $table->string('longitude');
            $table->boolean('is_billing')->default(false);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('shipping_addresses');
        Schema::dropIfExists('business_settings');
        Schema::dropIfExists('translations');

        parent::tearDown();
    }

    public function test_guest_billing_address_is_stored_in_shipping_addresses_and_returned_in_address_list(): void
    {
        DB::table('business_settings')->insert([
            [
                'type' => 'delivery_zip_code_area_restriction',
                'value' => '0',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'delivery_country_restriction',
                'value' => '0',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $controller = new CustomerController;

        $storeRequest = Request::create('/api/v1/customer/address/add', 'POST', [
            'contact_person_name' => 'Guest Billing',
            'address_type' => 'home',
            'address' => '90 Billing Street',
            'state' => 'Alexandria',
            'city' => 'Montaza',
            'area' => 'Sidi Bishr',
            'zip' => '21500',
            'country' => 'Egypt',
            'phone' => '+201000000000',
            'email' => 'guest@example.com',
            'latitude' => '31.2500',
            'longitude' => '29.9600',
            'is_billing' => 1,
            'guest_id' => 99,
            'payment_request_from' => 'app',
            'is_guest' => 1,
        ]);

        $storeResponse = $controller->add_new_address($storeRequest);

        $this->assertSame(200, $storeResponse->getStatusCode());
        $this->assertDatabaseHas('shipping_addresses', [
            'customer_id' => 99,
            'is_guest' => 1,
            'is_billing' => 1,
            'email' => 'guest@example.com',
        ]);

        $listRequest = Request::create('/api/v1/customer/address/list', 'GET', [
            'guest_id' => 99,
            'payment_request_from' => 'app',
            'is_guest' => 1,
        ]);

        $listResponse = $controller->address_list($listRequest);
        $payload = $listResponse->getData(true);

        $this->assertSame(200, $listResponse->getStatusCode());
        $this->assertCount(1, $payload);
        $this->assertTrue($payload[0]['is_billing']);
        $this->assertSame('guest@example.com', $payload[0]['email']);
    }
}
