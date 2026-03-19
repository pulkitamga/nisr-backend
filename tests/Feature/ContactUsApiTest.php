<?php

namespace Tests\Feature;

use App\Http\Controllers\RestAPI\v1\GeneralController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContactUsApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('branches');
        Schema::dropIfExists('contact_us');

        Schema::create('contact_us', function (Blueprint $table): void {
            $table->id();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('location')->nullable();
            $table->boolean('is_active')->default(0);
            $table->timestamps();
        });

        Schema::create('branches', function (Blueprint $table): void {
            $table->id();
            $table->string('branch_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('status')->nullable();
            $table->text('branch_address')->nullable();
            $table->decimal('branch_latitude', 12, 8)->nullable();
            $table->decimal('branch_longitude', 12, 8)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('branches');
        Schema::dropIfExists('contact_us');

        parent::tearDown();
    }

    public function test_contacts_endpoint_returns_latest_active_contact_record(): void
    {
        \DB::table('contact_us')->insert([
            [
                'phone' => '+201111111111',
                'email' => 'inactive@example.com',
                'location' => 'Inactive address',
                'is_active' => 0,
                'created_at' => now()->subMinute(),
                'updated_at' => now()->subMinute(),
            ],
            [
                'phone' => '+201234567890',
                'email' => 'support@example.com',
                'location' => '307 Avenue, Berthelot',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = (new GeneralController())->contacts();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'success' => true,
            'data' => [
                'phone' => '+201234567890',
                'email' => 'support@example.com',
                'address' => '307 Avenue, Berthelot',
                'latitude' => null,
                'longitude' => null,
                'title' => null,
                'description' => null,
                'image' => null,
                'title_ar' => null,
                'title_en' => null,
                'description_ar' => null,
                'description_en' => null,
                'address_ar' => null,
                'address_en' => null,
            ],
        ], $response->getData(true));
    }

    public function test_contacts_endpoint_returns_null_data_when_no_contact_exists(): void
    {
        $response = (new GeneralController())->contacts();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'success' => true,
            'data' => null,
        ], $response->getData(true));
    }

    public function test_contacts_endpoint_falls_back_to_active_branch_data_when_contact_cms_is_empty(): void
    {
        \DB::table('branches')->insert([
            'id' => 11,
            'branch_name' => 'Alexandria',
            'phone' => '16870',
            'email' => 'info@elnisr.com',
            'status' => 'active',
            'branch_address' => '6XWP+2QG, As Soyouf Qebli, Montaza 1, Alexandria Governorate 5516007, Egypt',
            'branch_latitude' => 31.23352,
            'branch_longitude' => 29.95288,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = (new GeneralController())->contacts();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'success' => true,
            'data' => [
                'phone' => '16870',
                'email' => 'info@elnisr.com',
                'address' => '6XWP+2QG, As Soyouf Qebli, Montaza 1, Alexandria Governorate 5516007, Egypt',
                'latitude' => 31.23352,
                'longitude' => 29.95288,
                'title' => null,
                'description' => null,
                'image' => null,
                'title_ar' => null,
                'title_en' => null,
                'description_ar' => null,
                'description_en' => null,
                'address_ar' => null,
                'address_en' => null,
            ],
        ], $response->getData(true));
    }
}
