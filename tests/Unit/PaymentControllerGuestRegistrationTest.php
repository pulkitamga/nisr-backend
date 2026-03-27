<?php

namespace Tests\Unit;

use App\Http\Controllers\Customer\PaymentController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PaymentControllerGuestRegistrationTest extends TestCase
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
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('shipping_addresses');

        parent::tearDown();
    }

    public function test_get_register_new_customer_api_process_reports_phone_conflict(): void
    {
        DB::table('users')->insert([
            'id' => 1,
            'name' => 'Existing Phone User',
            'f_name' => 'Existing Phone User',
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
            'id' => 15,
            'customer_id' => 77,
            'is_guest' => 1,
            'contact_person_name' => 'Guest Phone Conflict',
            'phone' => '+201111111111',
            'email' => 'new@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $controller = new PaymentController;
        $result = $controller->getRegisterNewCustomerAPIProcess(Request::create('/api/v1/digital-payment', 'POST', [
            'guest_id' => 77,
            'address_id' => 15,
            'password' => 'secret123',
        ]));

        $this->assertSame(0, $result['status']);
        $this->assertSame(translate('Phone_already_exists'), $result['message']);
    }

    public function test_get_register_new_customer_api_process_reports_email_conflict_for_billing_only_checkout(): void
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
            'id' => 16,
            'customer_id' => 88,
            'is_guest' => 1,
            'contact_person_name' => 'Guest Email Conflict',
            'phone' => '+201333333333',
            'email' => 'existing-email@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $controller = new PaymentController;
        $result = $controller->getRegisterNewCustomerAPIProcess(Request::create('/api/v1/digital-payment', 'POST', [
            'guest_id' => 88,
            'billing_address_id' => 16,
            'password' => 'secret123',
        ]));

        $this->assertSame(0, $result['status']);
        $this->assertSame(translate('Email_already_exists'), $result['message']);
    }
}
