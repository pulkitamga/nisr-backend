<?php

namespace Tests\Feature;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Http\Controllers\Web\WarrantyViewController;
use App\Services\FirebaseService;
use App\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Mockery;
use Tests\TestCase;

class WarrantyViewControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('VIEW_FILE_NAMES')) {
            define('VIEW_FILE_NAMES', require base_path('resources/themes/default/file_names.php'));
        }

        View::addLocation(base_path('resources/themes/default'));
        $this->createTables();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_owner_view_prefers_customer_profile_details_and_loads_product_name_when_available(): void
    {
        $customer = User::query()->create([
            'f_name' => 'Profile',
            'l_name' => 'Owner',
            'email' => 'profile-owner@example.com',
            'phone' => '201234567890',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);

        $productId = \DB::table('products')->insertGetId([
            'name' => 'Visible Product',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('warranties')->insert([
            'serial_number' => 'SERIAL-VIEW-001',
            'product_id' => $productId,
            'status' => 'active',
            'start_date' => now()->subDays(2),
            'end_date' => now()->addYear(),
            'final_user_id' => $customer->id,
            'activated_by_name' => 'Old Snapshot Name',
            'activated_by_email' => 'old-snapshot@example.com',
            'activated_by_phone' => '200000000000',
            'warranty_public_id' => 'public-warranty-001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($customer, 'customer');

        $controller = new WarrantyViewController(
            Mockery::mock(FirebaseService::class),
            Mockery::mock(BusinessSettingRepositoryInterface::class)
        );

        $view = $controller->view(Request::create('/warranty/view/public-warranty-001', 'GET'), 'public-warranty-001');
        $data = $view->getData();

        $this->assertTrue($data['isOwner']);
        $this->assertSame('Profile Owner', $data['warranty']->activated_by_name);
        $this->assertSame('profile-owner@example.com', $data['warranty']->activated_by_email);
        $this->assertSame('201234567890', $data['warranty']->activated_by_phone);
        $this->assertSame('Visible Product', $data['warranty']->product_name);
    }

    private function createTables(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('f_name')->nullable();
                $table->string('l_name')->nullable();
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('password')->nullable();
                $table->integer('is_active')->default(1);
                $table->rememberToken();
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
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('warranties')) {
            Schema::create('warranties', function (Blueprint $table) {
                $table->id();
                $table->string('serial_number')->unique();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('status')->default('preactivated');
                $table->timestamp('start_date')->nullable();
                $table->timestamp('end_date')->nullable();
                $table->unsignedBigInteger('final_user_id')->nullable();
                $table->string('activated_by_name')->nullable();
                $table->string('activated_by_email')->nullable();
                $table->string('activated_by_phone')->nullable();
                $table->string('warranty_public_id')->nullable();
                $table->softDeletes();
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
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('warranty_claims')) {
            Schema::create('warranty_claims', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('warranty_id');
                $table->string('status')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('view_tokens')) {
            Schema::create('view_tokens', function (Blueprint $table) {
                $table->id();
                $table->string('jti')->nullable();
                $table->string('warranty_public_id');
                $table->string('recipient_hash')->nullable();
                $table->string('scope')->nullable();
                $table->timestamp('issued_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('used_at')->nullable();
                $table->string('ip')->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
            });
        }
    }
}
