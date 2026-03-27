<?php

namespace Tests\Feature;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Http\Controllers\RestAPI\v1\WarrantyActivationApiController;
use App\Http\Controllers\Web\WarrantyActivationController;
use App\Models\BusinessSetting;
use App\Models\Policy;
use App\Models\Warranty;
use App\Services\FirebaseService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class WarrantyActivationCommitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        Schema::dropIfExists('activation_reviews');
        Schema::dropIfExists('warranty_timeline_events');
        Schema::dropIfExists('policies');
        Schema::dropIfExists('warranties');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('products');
        Schema::dropIfExists('translations');

        parent::tearDown();
    }

    public function test_web_activation_commit_is_idempotent_for_pending_review_serials(): void
    {
        $controller = new WarrantyActivationController(
            $this->makeBusinessSettingRepo(),
            Mockery::mock(FirebaseService::class),
        );

        $warranty = $this->createWarranty('WEB-SERIAL-001');
        $request = new Request([
            'purchase_date' => now()->subDay()->toDateString(),
            'retailer_name' => 'Outside Retailer',
            'name' => 'Web Customer',
            'phone' => '201002010173',
            'email' => 'web@example.com',
            'activation_ip' => '127.0.0.1',
        ]);

        $this->invokePrivateMethod($controller, 'commitActivation', [$warranty, $request, true, true, ['Missing proof of purchase']]);
        $this->invokePrivateMethod($controller, 'commitActivation', [$warranty, $request, true, true, ['Missing proof of purchase']]);

        $this->assertDatabaseHas('warranties', [
            'serial_number' => 'WEB-SERIAL-001',
            'status' => 'pending_review',
            'activation_method' => 'user_public_form',
            'policy_version' => '2.0',
        ]);
        $this->assertSame(1, DB::table('warranty_timeline_events')->where('warranty_id', $warranty->id)->count());
        $this->assertSame(1, DB::table('activation_reviews')->where('warranty_id', $warranty->id)->where('status', 'pending')->count());
    }

    public function test_api_activation_commit_is_idempotent_for_pending_review_serials(): void
    {
        $controller = new WarrantyActivationApiController(
            $this->makeBusinessSettingRepo(),
            Mockery::mock(FirebaseService::class),
        );

        $warranty = $this->createWarranty('API-SERIAL-001');
        $request = new Request([
            'purchase_date' => now()->subDays(2)->toDateString(),
            'retailer_name' => 'Outside Retailer',
            'name' => 'Api Customer',
            'phone' => '201002010174',
            'email' => 'api@example.com',
            'receipt_path' => 'warranty/receipts/test.pdf',
        ]);

        $this->invokePrivateMethod($controller, 'commitActivation', [$warranty, $request, true, true, ['Distributor purchase without receipt']]);
        $this->invokePrivateMethod($controller, 'commitActivation', [$warranty, $request, true, true, ['Distributor purchase without receipt']]);

        $this->assertDatabaseHas('warranties', [
            'serial_number' => 'API-SERIAL-001',
            'status' => 'pending_review',
            'activation_method' => 'mobile_app',
            'receipt_path' => 'warranty/receipts/test.pdf',
            'policy_version' => '2.0',
        ]);
        $this->assertSame(1, DB::table('warranty_timeline_events')->where('warranty_id', $warranty->id)->count());
        $this->assertSame(1, DB::table('activation_reviews')->where('warranty_id', $warranty->id)->where('status', 'pending')->count());
    }

    private function createTables(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->unsignedInteger('warranty_duration')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('translationable_type');
            $table->unsignedBigInteger('translationable_id');
            $table->string('locale')->nullable();
            $table->string('key')->nullable();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('warranties', function (Blueprint $table): void {
            $table->id();
            $table->string('serial_number')->unique();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('status')->default('preactivated');
            $table->timestamp('activation_date')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamp('purchase_date')->nullable();
            $table->unsignedBigInteger('retailer_branch_id')->nullable();
            $table->string('retailer_name')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('activated_ip')->nullable();
            $table->string('activation_method')->nullable();
            $table->string('policy_version')->nullable();
            $table->boolean('consent_checked')->default(false);
            $table->timestamp('consent_timestamp')->nullable();
            $table->string('consent_ip')->nullable();
            $table->string('activated_by_name')->nullable();
            $table->string('activated_by_phone')->nullable();
            $table->string('activated_by_email')->nullable();
            $table->string('receipt_path')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('delivery_man_id')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        Schema::create('policies', function (Blueprint $table): void {
            $table->id();
            $table->string('version')->nullable();
            $table->date('effective_date')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('warranty_timeline_events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('warranty_id')->nullable();
            $table->unsignedBigInteger('warranty_claim_id')->nullable();
            $table->string('event_type');
            $table->text('description')->nullable();
            $table->timestamp('timestamp')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('activation_reviews', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('warranty_id');
            $table->string('status')->nullable();
            $table->text('review_notes')->nullable();
            $table->text('flagged_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('first_response_due')->nullable();
            $table->timestamp('decision_due')->nullable();
            $table->timestamps();
        });

        Policy::query()->create([
            'version' => '2.0',
            'effective_date' => now()->toDateString(),
            'published_at' => now(),
        ]);
    }

    private function createWarranty(string $serialNumber): Warranty
    {
        $productId = DB::table('products')->insertGetId([
            'name' => 'Warranty Product',
            'warranty_duration' => 12,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $warrantyId = DB::table('warranties')->insertGetId([
            'serial_number' => $serialNumber,
            'product_id' => $productId,
            'status' => 'preactivated',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Warranty::query()->findOrFail($warrantyId);
    }

    private function makeBusinessSettingRepo(): BusinessSettingRepositoryInterface
    {
        $repo = Mockery::mock(BusinessSettingRepositoryInterface::class);
        $repo->shouldReceive('getFirstWhere')
            ->andReturnUsing(function (array $params): BusinessSetting {
                return match ($params['type'] ?? null) {
                    'warranty_months' => new BusinessSetting(['value' => '12']),
                    'warranty_auto_approve_off_platform' => new BusinessSetting(['value' => '0']),
                    default => new BusinessSetting(['value' => null]),
                };
            });

        return $repo;
    }

    private function invokePrivateMethod(object $instance, string $methodName, array $arguments): mixed
    {
        $method = new ReflectionMethod($instance, $methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($instance, $arguments);
    }
}
