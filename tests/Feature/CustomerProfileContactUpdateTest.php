<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CustomerProfileContactUpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        $database = (string)($_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: '');
        if ($database === '' || $database === ':memory:') {
            $database = basename(getcwd());
        }

        putenv('DB_CONNECTION=mysql');
        putenv("DB_DATABASE={$database}");
        $_SERVER['DB_CONNECTION'] = 'mysql';
        $_ENV['DB_CONNECTION'] = 'mysql';
        $_SERVER['DB_DATABASE'] = $database;
        $_ENV['DB_DATABASE'] = $database;

        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => $database,
        ]);
    }

    protected function connectionsToTransact(): array
    {
        return ['mysql'];
    }

    public function test_web_profile_update_allows_verified_phone_change_and_resets_verification_flags(): void
    {
        $customer = $this->createCustomer([
            'f_name' => 'Ahmed',
            'l_name' => 'Old',
            'email' => 'old-contact-' . uniqid() . '@example.com',
            'phone' => '2010' . random_int(1000000, 9999999),
            'is_phone_verified' => 1,
            'is_email_verified' => 1,
            'email_verified_at' => now(),
        ]);

        $newPhone = '2012' . random_int(1000000, 9999999);
        $newEmail = 'new-contact-' . uniqid() . '@example.com';

        $response = $this->actingAs($customer, 'customer')->post(route('user-update'), [
            'f_name' => 'Ahmed',
            'l_name' => 'Updated',
            'email' => $newEmail,
            'phone' => $newPhone,
            'password' => '',
            'confirm_password' => '',
        ]);

        $response->assertRedirect();

        $customer->refresh();

        $this->assertSame($newPhone, $customer->phone);
        $this->assertSame($newEmail, $customer->email);
        $this->assertSame(0, (int)$customer->is_phone_verified);
        $this->assertSame(0, (int)$customer->is_email_verified);
        $this->assertNull($customer->email_verified_at);
    }

    public function test_api_profile_verification_updates_authenticated_user_for_unused_contacts(): void
    {
        $customer = $this->createCustomer([
            'f_name' => 'Mobile',
            'l_name' => 'Tester',
            'email' => 'mobile-old-' . uniqid() . '@example.com',
            'phone' => '2011' . random_int(1000000, 9999999),
            'is_phone_verified' => 0,
            'is_email_verified' => 0,
            'email_verified_at' => null,
        ]);

        $newPhone = '2015' . random_int(1000000, 9999999);
        $newEmail = 'mobile-new-' . uniqid() . '@example.com';

        DB::table('phone_or_email_verifications')->insert([
            'phone_or_email' => $newPhone,
            'token' => '1234',
            'otp_hit_count' => 0,
            'is_temp_blocked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $phoneResponse = $this->actingAs($customer, 'api')
            ->withHeader('Authorization', 'Bearer testing-token')
            ->postJson('/api/v1/auth/verify-profile-info', [
                'type' => 'phone',
                'email_or_phone' => $newPhone,
                'token' => '1234',
            ]);

        $phoneResponse->assertOk()
            ->assertJson([
                'message' => translate('Phone_number_is_successfully_verified'),
            ]);

        $customer->refresh();

        $this->assertSame($newPhone, $customer->phone);
        $this->assertTrue((bool)$customer->is_phone_verified);

        DB::table('phone_or_email_verifications')->insert([
            'phone_or_email' => $newEmail,
            'token' => '4321',
            'otp_hit_count' => 0,
            'is_temp_blocked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $emailResponse = $this->actingAs($customer, 'api')
            ->withHeader('Authorization', 'Bearer testing-token')
            ->postJson('/api/v1/auth/verify-profile-info', [
                'type' => 'email',
                'email_or_phone' => $newEmail,
                'token' => '4321',
            ]);

        $emailResponse->assertOk()
            ->assertJson([
                'message' => translate('Email_is_successfully_verified'),
            ]);

        $customer->refresh();

        $this->assertSame($newEmail, $customer->email);
        $this->assertTrue((bool)$customer->is_email_verified);
        $this->assertNotNull($customer->email_verified_at);
    }

    private function createCustomer(array $attributes = []): User
    {
        $now = now();

        $id = DB::table('users')->insertGetId(array_merge([
            'name' => 'Customer Tester',
            'f_name' => 'Customer',
            'l_name' => 'Tester',
            'email' => 'customer-' . uniqid() . '@example.com',
            'phone' => '2010' . random_int(1000000, 9999999),
            'password' => bcrypt('password'),
            'is_active' => 1,
            'is_phone_verified' => 0,
            'is_email_verified' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ], $attributes));

        return User::query()->findOrFail($id);
    }
}
