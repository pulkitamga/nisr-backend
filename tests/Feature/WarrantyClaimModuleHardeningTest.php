<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use App\Http\Controllers\Admin\WarrantyClaimController as AdminWarrantyClaimController;
use App\Http\Requests\Warranty\Admin\ReceiveRequest;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class WarrantyClaimModuleHardeningTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(PermissionMiddleware::class);

        Schema::dropIfExists('warranty_timeline_events');
        Schema::dropIfExists('warranty_claims');
        Schema::dropIfExists('warranties');
        Schema::dropIfExists('branches');

        Schema::create('warranties', function (Blueprint $table): void {
            $table->id();
            $table->string('serial_number')->unique();
            $table->string('status')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('warranty_public_id')->nullable();
            $table->unsignedBigInteger('final_user_id')->nullable();
            $table->string('activated_by_name')->nullable();
            $table->string('activation_method')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('warranty_claims', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('warranty_id')->nullable();
            $table->string('serial_number');
            $table->string('claim_number')->nullable();
            $table->string('status')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('response_due')->nullable();
            $table->timestamp('resolution_due')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->text('diagnosis_notes')->nullable();
            $table->string('repair_or_replace')->nullable();
            $table->boolean('tamper_detected')->default(false);
            $table->timestamp('received_at')->nullable();
            $table->string('rma_number')->nullable();
            $table->timestamp('rma_deadline')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('branches', function (Blueprint $table): void {
            $table->id();
            $table->string('branch_name')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('warranty_timeline_events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('warranty_id')->nullable();
            $table->unsignedBigInteger('warranty_claim_id')->nullable();
            $table->string('event_type')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('timestamp')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('warranty_timeline_events');
        Schema::dropIfExists('warranty_claims');
        Schema::dropIfExists('warranties');
        Schema::dropIfExists('branches');

        parent::tearDown();
    }

    public function test_branch_assigned_admin_cannot_view_claim_from_another_branch(): void
    {
        $claim = WarrantyClaim::query()->create([
            'serial_number' => 'CLAIM-BRANCH-1',
            'claim_number' => 'CLAIM-BRANCH-1',
            'status' => 'new',
            'branch_id' => 2,
        ]);

        $this->actingAs(new Admin(['id' => 7, 'branch_id' => 1]), 'admin');

        $controller = $this->app->make(AdminWarrantyClaimController::class);

        try {
            $controller->view($claim);
            $this->fail('Expected branch access to be denied.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_diagnose_rejects_excessive_replacement_fee(): void
    {
        $claim = WarrantyClaim::query()->create([
            'serial_number' => 'CLAIM-DIAG-1',
            'claim_number' => 'CLAIM-DIAG-1',
            'status' => 'received',
            'branch_id' => 1,
        ]);

        $this->actingAs(new Admin(['id' => 8, 'branch_id' => 1]), 'admin');

        $response = $this->from('/__back')->post(route('admin.warranty.claim.diagnose', $claim->id), [
            'diagnosis_notes' => 'Battery cell failure confirmed.',
            'repair_or_replace' => 'replace',
            'replacement_mode' => 'full',
            'replacement_fee_option' => 'fee_required',
            'replacement_fee' => 100001,
        ]);

        $response->assertRedirect('/__back');
        $response->assertSessionHasErrors(['replacement_fee']);
    }

    public function test_admin_submit_rejects_non_image_attachment(): void
    {
        Warranty::query()->create([
            'serial_number' => 'SAFE-SERIAL-1',
            'status' => 'active',
            'end_date' => now()->addDays(30),
            'branch_id' => 1,
        ]);

        $this->actingAs(new Admin(['id' => 9, 'branch_id' => 1]), 'admin');

        $response = $this->from('/__back')->post(route('admin.warranty.claim.submit'), [
            'serial_number' => 'SAFE-SERIAL-1',
            'description' => 'Customer brought the battery for inspection.',
            'attachments' => [
                UploadedFile::fake()->create('payload.php', 12, 'application/x-httpd-php'),
            ],
        ]);

        $response->assertRedirect('/__back');
        $response->assertSessionHasErrors(['attachments.0']);
    }

    public function test_receive_accepts_matching_branch_when_request_branch_id_is_numeric_string(): void
    {
        DB::table('branches')->insert([
            'id' => 2,
            'branch_name' => 'Branch Two',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $claim = WarrantyClaim::query()->create([
            'serial_number' => 'CLAIM-RECEIVE-OK',
            'claim_number' => 'CLAIM-RECEIVE-OK',
            'status' => 'rma_issued',
            'branch_id' => 2,
            'rma_deadline' => now()->addDay(),
        ]);

        $this->actingAs(new Admin(['id' => 10, 'branch_id' => 2]), 'admin');

        $controller = $this->app->make(AdminWarrantyClaimController::class);
        $request = ReceiveRequest::create('/admin/warranty/claim/' . $claim->id . '/receive', 'POST', [
            'serial_number' => 'CLAIM-RECEIVE-OK',
            'branch_id' => '2',
            'received_notes' => 'Item received at assigned branch.',
        ], server: ['HTTP_ACCEPT' => 'application/json']);

        $response = $controller->receive($request, $claim);
        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($payload['success']);
        $this->assertDatabaseHas('warranty_claims', [
            'id' => $claim->id,
            'status' => 'received',
        ]);
    }

    public function test_receive_rejects_mismatched_branch_with_expected_branch_name(): void
    {
        DB::table('branches')->insert([
            ['id' => 2, 'branch_name' => 'Branch Two', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'branch_name' => 'Branch Three', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $claim = WarrantyClaim::query()->create([
            'serial_number' => 'CLAIM-RECEIVE-BAD',
            'claim_number' => 'CLAIM-RECEIVE-BAD',
            'status' => 'rma_issued',
            'branch_id' => 2,
            'rma_deadline' => now()->addDay(),
        ]);

        $this->actingAs(new Admin(['id' => 11, 'branch_id' => 2]), 'admin');

        $controller = $this->app->make(AdminWarrantyClaimController::class);
        $request = ReceiveRequest::create('/admin/warranty/claim/' . $claim->id . '/receive', 'POST', [
            'serial_number' => 'CLAIM-RECEIVE-BAD',
            'branch_id' => '3',
        ], server: ['HTTP_ACCEPT' => 'application/json']);

        $response = $controller->receive($request, $claim);
        $payload = $response->getData(true);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertFalse($payload['success']);
        $this->assertSame('Invalid branch. Item must be returned to: Branch Two', $payload['message']);
        $this->assertDatabaseHas('warranty_claims', [
            'id' => $claim->id,
            'status' => 'rma_issued',
        ]);
    }
}
