<?php

namespace Tests\Feature;

use App\Jobs\TriageClaimJob;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class WarrantyClaimTriageJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('blacklists');
        Schema::dropIfExists('warranty_claims');

        Schema::create('warranty_claims', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('warranty_id')->nullable();
            $table->string('serial_number');
            $table->string('claim_number');
            $table->string('status')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('response_due')->nullable();
            $table->timestamp('resolution_due')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('rma_number')->nullable();
            $table->timestamp('rma_deadline')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('blacklists', function (Blueprint $table): void {
            $table->id();
            $table->string('serial_number')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Mockery::close();

        Schema::dropIfExists('blacklists');
        Schema::dropIfExists('warranty_claims');

        parent::tearDown();
    }

    public function test_triage_job_keeps_claim_saved_when_rma_side_effect_fails(): void
    {
        $claim = WarrantyClaim::query()->create([
            'warranty_id' => 1,
            'serial_number' => 'SERIAL507',
            'claim_number' => 'CLM-TEST01',
            'status' => 'new',
            'description' => 'Battery charging problem reported by customer.',
            'submitted_at' => now(),
            'response_due' => now()->addDay(),
            'resolution_due' => now()->addDays(3),
        ]);

        $claim->setRelation('warranty', new Warranty([
            'status' => 'active',
            'end_date' => now()->addYear(),
            'branch_id' => 1,
        ]));

        Mockery::mock('alias:App\Services\RMAService')
            ->shouldReceive('issueRMA')
            ->once()
            ->with(Mockery::type(WarrantyClaim::class))
            ->andThrow(new RuntimeException('550 5.7.1 Relaying denied'));

        (new TriageClaimJob($claim))->handle();

        $this->assertSame('approved', $claim->fresh()->status);
    }
}
