<?php

namespace Tests\Feature;

use App\Http\Controllers\RestAPI\v1\GeneralController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContactUsApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        Schema::dropIfExists('branches');
        Schema::dropIfExists('business_settings');
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
        Cache::flush();

        Schema::dropIfExists('branches');
        Schema::dropIfExists('translations');
        Schema::dropIfExists('business_settings');

        parent::tearDown();
    }

    public function test_contacts_endpoint_returns_business_settings_contact_data_and_branches(): void
    {
        \DB::table('business_settings')->insert([
            [
                'type' => 'company_phone',
                'value' => '16870',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'company_email',
                'value' => 'info@elnisr.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'shop_address',
                'value' => '6XWP+2QG, As Soyouf Qebli, Montaza 1, Alexandria Governorate 5516007, Egypt',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        \DB::table('branches')->insert([
            [
                'id' => 1,
                'branch_name' => 'System',
                'phone' => '0000',
                'email' => 'system@example.com',
                'status' => 'active',
                'branch_address' => 'System Address',
                'branch_latitude' => null,
                'branch_longitude' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 11,
                'branch_name' => 'Alexandria',
                'phone' => '0111122223',
                'email' => 'alex@elnisr.online',
                'status' => 'active',
                'branch_address' => '10 Bastour, Bab Sharqi WA Wabour Al Meyah, Bab Shar\', Alexandria Governorate 5422010, Egypt',
                'branch_latitude' => 31.17102183,
                'branch_longitude' => 29.89180945,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = (new GeneralController())->contacts();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'success' => true,
            'data' => [
                'phone' => '16870',
                'email' => 'info@elnisr.com',
                'address' => '6XWP+2QG, As Soyouf Qebli, Montaza 1, Alexandria Governorate 5516007, Egypt',
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
                'branches' => [
                    [
                        'id' => 11,
                        'branch_name' => 'Alexandria',
                        'address' => '10 Bastour, Bab Sharqi WA Wabour Al Meyah, Bab Shar\', Alexandria Governorate 5422010, Egypt',
                        'phone' => '0111122223',
                        'email' => 'alex@elnisr.online',
                        'latitude' => 31.17102183,
                        'longitude' => 29.89180945,
                    ],
                ],
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

    public function test_contacts_endpoint_returns_branch_data_when_only_branches_exist(): void
    {
        \DB::table('branches')->insert([
            'id' => 11,
            'branch_name' => 'Alexandria',
            'phone' => '0111122223',
            'email' => 'alex@elnisr.online',
            'status' => 'active',
            'branch_address' => '10 Bastour, Bab Sharqi WA Wabour Al Meyah, Bab Shar\', Alexandria Governorate 5422010, Egypt',
            'branch_latitude' => 31.17102183,
            'branch_longitude' => 29.89180945,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = (new GeneralController())->contacts();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'success' => true,
            'data' => [
                'phone' => null,
                'email' => null,
                'address' => null,
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
                'branches' => [
                    [
                        'id' => 11,
                        'branch_name' => 'Alexandria',
                        'address' => '10 Bastour, Bab Sharqi WA Wabour Al Meyah, Bab Shar\', Alexandria Governorate 5422010, Egypt',
                        'phone' => '0111122223',
                        'email' => 'alex@elnisr.online',
                        'latitude' => 31.17102183,
                        'longitude' => 29.89180945,
                    ],
                ],
            ],
        ], $response->getData(true));
    }
}
