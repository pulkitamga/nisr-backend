<?php

namespace Tests\Feature;

use App\Contracts\Repositories\RobotsMetaContentRepositoryInterface;
use App\Http\Controllers\Web\CareerController;
use App\Models\CareerJob;
use App\Models\InboxMessage;
use App\Models\careerApplies;
use App\Services\SlaService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CareerApplicationWebFlowTest extends TestCase
{
    protected function setUp(): void
    {
        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('pdo_sqlite extension is not available in this environment.');
        }

        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => false,
            'session.driver' => 'array',
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createTestSchema();

        app()->instance(RobotsMetaContentRepositoryInterface::class, $this->createMock(RobotsMetaContentRepositoryInterface::class));
        app()->instance(SlaService::class, $this->createMock(SlaService::class));

        Route::middleware('web')->post('/career/store-test', [CareerController::class, 'careerStore'])
            ->name('career.store.test');
        app('router')->getRoutes()->refreshNameLookups();
    }

    public function test_career_application_is_saved_when_form_submits_supported_gender_values(): void
    {
        Storage::fake('local');

        $job = CareerJob::query()->create([
            'title' => 'Sales Executive',
            'is_active' => true,
        ]);

        $response = $this->from('/career/job/' . $job->id)
            ->post(route('career.store.test'), [
                'job_id' => $job->id,
                'first_name' => 'Ahmed',
                'last_name' => 'Ali',
                'email' => 'ahmed@example.com',
                'phone' => '01223344556',
                'gender' => 'Male',
                'country' => 'Egypt',
                'state' => 'Alexandria',
                'city' => 'Alexandria',
                'area' => 'Smouha',
                'notice_period' => '2 weeks',
                'last_ctc' => '5000',
                'resume' => UploadedFile::fake()->create('resume.pdf', 200, 'application/pdf'),
            ]);

        $response->assertRedirect('/career/job/' . $job->id);
        $response->assertSessionHasNoErrors();

        $application = careerApplies::query()->firstOrFail();
        $this->assertSame('Male', $application->gender);
        $this->assertSame((string) $job->id, (string) $application->job_id);
        $this->assertNotEmpty($application->resume);
        Storage::disk('local')->assertExists($application->resume);

        $inboxMessage = InboxMessage::query()->firstOrFail();
        $this->assertSame('career', $inboxMessage->message_type);
        $this->assertSame('medium', $inboxMessage->priority);
        $this->assertSame('Male', $inboxMessage->details['gender'] ?? null);
    }

    private function createTestSchema(): void
    {
        Schema::create('business_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('type')->nullable();
            $table->longText('value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('translationable_type')->nullable();
            $table->unsignedBigInteger('translationable_id')->nullable();
            $table->string('locale')->nullable();
            $table->string('key')->nullable();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        Schema::create('career_jobs', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('career_applies', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('job_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->enum('gender', ['Male', 'Female'])->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('area')->nullable();
            $table->string('notice_period')->nullable();
            $table->string('last_ctc')->nullable();
            $table->string('resume')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inbox_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('subject')->nullable();
            $table->longText('body')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->string('sender_name')->nullable();
            $table->string('sender_email')->nullable();
            $table->string('sender_phone')->nullable();
            $table->enum('pipeline', ['email', 'form', 'chat', 'social', 'phone']);
            $table->enum('message_type', ['support', 'service', 'career', 'warranty', 'contact'])->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('related_lead_id')->nullable();
            $table->unsignedBigInteger('related_ticket_id')->nullable();
            $table->unsignedBigInteger('related_warranty_id')->nullable();
            $table->json('details')->nullable();
            $table->enum('status', ['new', 'processing', 'converted', 'ignored', 'spam'])->default('new');
            $table->enum('escalation_level', ['none', 'l1', 'l2'])->default('none');
            $table->timestamp('escalated_at')->nullable();
            $table->unsignedBigInteger('escalated_by')->nullable();
            $table->double('spam_score', 8, 2)->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->nullable();
            $table->longText('attachment')->nullable();
            $table->longText('reply')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->longText('message')->nullable();
            $table->string('convert_type')->nullable();
            $table->string('convert_sub_type')->nullable();
            $table->timestamp('response_due')->nullable();
            $table->timestamp('resolution_due')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->integer('reopen_count')->default(0);
            $table->timestamp('sla_paused_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
