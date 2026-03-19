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

        Schema::dropIfExists('contact_us');

        Schema::create('contact_us', function (Blueprint $table): void {
            $table->id();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('location')->nullable();
            $table->boolean('is_active')->default(0);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
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
}
